<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Mapper;

use BetoCampoy\Champs\Fiscal\Dce\Dace\Contract\DaceBarcodeGeneratorInterface;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Contract\DaceQrCodeGeneratorInterface;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Contract\DaceQrCodeUrlBuilderInterface;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Contract\DcePrintableDocumentInterface;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Dto\DaceAdditionalData;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Dto\DaceData;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Dto\DaceDocumentData;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Dto\DaceItemData;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Dto\DacePartyData;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Dto\DaceVisualAssetsData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use RuntimeException;

final class FiscalDocumentToDaceDataMapper
{
    private const DCE_NS = 'http://www.portalfiscal.inf.br/dce';

    public function __construct(
        private readonly DaceQrCodeUrlBuilderInterface $qrCodeUrlBuilder,
        private readonly DaceQrCodeGeneratorInterface $qrCodeGenerator,
        private readonly DaceBarcodeGeneratorInterface $barcodeGenerator,
        private readonly string $qrCodeBaseUrl,
    ) {
    }

    public function map(DcePrintableDocumentInterface $document): DaceData
    {
        $xml = $document->getDceAuthorizedXml();

        if ($xml === null || trim($xml) === '') {
            throw new RuntimeException(
                'Não foi possível gerar a DACE porque o documento ainda não possui XML autorizado disponível.'
            );
        }

        [$dom, $xpath] = $this->loadAndValidateXml($xml);

        $infDce = $this->getRequiredElement($xpath, '//dce:infDCe', 'infDCe não encontrado no XML da DC-e.');
        $ide = $this->getRequiredElement($xpath, 'dce:ide', 'ide não encontrado no XML da DC-e.', $infDce);

        $issuerNode = $this->getRequiredElement($xpath, 'dce:emit', 'emit não encontrado no XML da DC-e.', $infDce);
        $recipientNode = $this->getRequiredElement($xpath, 'dce:dest', 'dest não encontrado no XML da DC-e.', $infDce);

        $documentData = $this->mapDocument($xpath, $infDce, $ide);
        $issuer = $this->mapParty($xpath, $issuerNode);
        $recipient = $this->mapParty($xpath, $recipientNode);
        $responsibleParty = $this->mapResponsibleParty($xpath, $infDce);
        $items = $this->mapItems($xpath, $infDce);
        $additional = $this->mapAdditional($xpath, $infDce);
        $assets = $this->mapAssets($documentData, $issuer);

        return new DaceData(
            document: $documentData,
            issuer: $issuer,
            recipient: $recipient,
            responsibleParty: $responsibleParty,
            items: $items,
            additional: $additional,
            assets: $assets,
        );
    }

    /**
     * @return array{0: DOMDocument, 1: DOMXPath}
     */
    private function loadAndValidateXml(string $xml): array
    {
        if (trim($xml) === '') {
            throw new RuntimeException('XML da DC-e está vazio.');
        }

        $previousUseInternalErrors = libxml_use_internal_errors(true);

        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $loaded = $dom->loadXML($xml, LIBXML_NOBLANKS);

            if (!$loaded) {
                $errors = libxml_get_errors();
                $message = 'XML inválido ou mal formatado.';

                if ($errors !== []) {
                    $first = $errors[0];
                    $message .= sprintf(' [%s:%d:%d] %s', 'line', $first->line, $first->column, trim($first->message));
                }

                throw new RuntimeException($message);
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('dce', self::DCE_NS);

            $this->assertIsValidDceXml($xpath);

            return [$dom, $xpath];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }
    }

    private function assertIsValidDceXml(DOMXPath $xpath): void
    {
        $hasInfDce = $this->firstNode($xpath, '//dce:infDCe') !== null;
        $hasIde = $this->firstNode($xpath, '//dce:infDCe/dce:ide') !== null;
        $hasEmit = $this->firstNode($xpath, '//dce:infDCe/dce:emit') !== null;
        $hasDest = $this->firstNode($xpath, '//dce:infDCe/dce:dest') !== null;

        if (!$hasInfDce || !$hasIde || !$hasEmit || !$hasDest) {
            throw new RuntimeException(
                'XML não parece ser uma DC-e válida para impressão da DACE. Estrutura mínima esperada não encontrada.'
            );
        }
    }

    private function mapDocument(DOMXPath $xpath, DOMElement $infDce, DOMElement $ide): DaceDocumentData
    {
        $accessKey = $this->extractAccessKey($infDce);
        $number = $this->firstValueFromQueries($xpath, [
            'dce:nDC',
            'dce:nDoc',
            'dce:nNF',
        ], $ide) ?? '';

        $series = $this->firstValueFromQueries($xpath, [
            'dce:serie',
        ], $ide) ?? '';

        $issueDateTimeRaw = $this->firstValueFromQueries($xpath, [
            'dce:dhEmi',
            'dce:dEmi',
        ], $ide);

        $authorizationProtocol = $this->firstValueFromQueries($xpath, [
            '//dce:retDCe/dce:nProt',
            '//dce:protDCe/dce:infProt/dce:nProt',
            '//dce:infProt/dce:nProt',
        ]) ?? '';

        $transportModeLabel = $this->buildTransportModeLabel($xpath, $ide);
        $totalAmount = $this->extractTotalAmount($xpath, $infDce);
        $isHomologation = $this->extractEnvironment($xpath, $ide) === 2;
        $isContingency = $this->extractEmissionType($xpath, $ide) !== 1;

        return new DaceDocumentData(
            number: $number,
            numberFormatted: $this->formatDocumentNumber($number),
            series: $series,
            issueDateTime: $this->formatIssueDateTime($issueDateTimeRaw),
            transportModeLabel: $transportModeLabel,
            authorizationProtocol: $authorizationProtocol,
            accessKey: $accessKey,
            accessKeyFormatted: $this->formatAccessKey($accessKey),
            totalAmountFormatted: $this->formatMoney($totalAmount),
            isHomologation: $isHomologation,
            isContingency: $isContingency,
            contingencyMessage: $isContingency ? 'EMITIDA EM CONTINGÊNCIA' : null,
            sheetLabel: 'FOLHA 01/01',
        );
    }

    private function mapParty(DOMXPath $xpath, DOMElement $partyNode): DacePartyData
    {
        $document = $this->firstValueFromQueries($xpath, [
            'dce:CNPJ',
            'dce:CPF',
            'dce:idOutros',
        ], $partyNode);

        $name = $this->firstValueFromQueries($xpath, [
            'dce:xNome',
            'dce:xNomeDest',
            'dce:xNomeEmit',
        ], $partyNode) ?? '';

        $addressNode = $this->findFirstElementByQueries($xpath, [
            'dce:enderEmit',
            'dce:enderDest',
            'dce:ender',
        ], $partyNode);

        return new DacePartyData(
            document: $this->formatCpfCnpjOrRaw($document),
            name: $name,
            cityUf: $this->buildCityUf(
                $addressNode ? $this->firstValueFromQueries($xpath, ['dce:xMun'], $addressNode) : null,
                $addressNode ? $this->firstValueFromQueries($xpath, ['dce:UF'], $addressNode) : null,
            ),
            fullAddress: $addressNode ? $this->buildFullAddressFromNode($xpath, $addressNode) : '',
        );
    }

    private function mapResponsibleParty(DOMXPath $xpath, DOMElement $infDce): ?DacePartyData
    {
        $transportadoraNode = $this->findFirstElementByQueries($xpath, [
            'dce:Transportadora',
        ], $infDce);

        if ($transportadoraNode instanceof DOMElement) {
            $document = $this->firstValueFromQueries($xpath, [
                'dce:CNPJ',
                'dce:CPF',
                'dce:idOutros',
            ], $transportadoraNode);

            $name = $this->firstValueFromQueries($xpath, [
                'dce:xNome',
            ], $transportadoraNode) ?? '';

            if (trim($name) === '' && trim((string) $document) === '') {
                return null;
            }

            return new DacePartyData(
                document: $this->formatCpfCnpjOrRaw($document),
                name: $name !== '' ? $name : '-',
                cityUf: '',
                fullAddress: '',
            );
        }

        $node = $this->findFirstElementByQueries($xpath, [
            'dce:transporta',
            'dce:transp',
            'dce:marketplace',
            'dce:fisco',
            'dce:responsavel',
        ], $infDce);

        if (!$node instanceof DOMElement) {
            return null;
        }

        $document = $this->firstValueFromQueries($xpath, [
            'dce:CNPJ',
            'dce:CPF',
            'dce:idOutros',
        ], $node);

        $name = $this->firstValueFromQueries($xpath, [
            'dce:xNome',
            'dce:xNomeResp',
        ], $node) ?? '';

        if (trim($name) === '' && trim((string) $document) === '') {
            return null;
        }

        $addressNode = $this->findFirstElementByQueries($xpath, [
            'dce:ender',
            'dce:enderResp',
            'dce:enderTransporta',
        ], $node);

        return new DacePartyData(
            document: $this->formatCpfCnpjOrRaw($document),
            name: $name !== '' ? $name : '-',
            cityUf: $addressNode ? $this->buildCityUf(
                $this->firstValueFromQueries($xpath, ['dce:xMun'], $addressNode),
                $this->firstValueFromQueries($xpath, ['dce:UF'], $addressNode),
            ) : '',
            fullAddress: $addressNode ? $this->buildFullAddressFromNode($xpath, $addressNode) : '',
        );
    }

    /**
     * @return DaceItemData[]
     */
    private function mapItems(DOMXPath $xpath, DOMElement $infDce): array
    {
        $items = [];
        $detNodes = $xpath->query('dce:det', $infDce);

        if (!$detNodes instanceof DOMNodeList || $detNodes->length === 0) {
            return [];
        }

        $index = 1;

        foreach ($detNodes as $detNode) {
            if (!$detNode instanceof DOMElement) {
                continue;
            }

            $description = $this->firstValueFromQueries($xpath, [
                'dce:prod/dce:xProd',
                'dce:xProd',
                'dce:desc',
                'dce:xItem',
            ], $detNode) ?? '';

            $quantity = $this->firstValueFromQueries($xpath, [
                'dce:prod/dce:qCom',
                'dce:qCom',
                'dce:qItem',
                'dce:qCarga',
            ], $detNode);

            $amount = $this->firstValueFromQueries($xpath, [
                'dce:prod/dce:vProd',
                'dce:vProd',
                'dce:vItem',
                'dce:vMerc',
            ], $detNode);

            $items[] = new DaceItemData(
                itemNumber: $index,
                description: $description,
                quantity: $this->formatQuantity($quantity),
                amountFormatted: $this->formatMoney($amount),
            );

            $index++;
        }

        return $items;
    }

    private function mapAdditional(DOMXPath $xpath, DOMElement $infDce): DaceAdditionalData
    {
        $complementaryInformation = $this->firstValueFromQueries($xpath, [
            'dce:infAdic/dce:infCpl',
            'dce:infCpl',
        ], $infDce);

        $taxInformation = $this->firstValueFromQueries($xpath, [
            'dce:infAdic/dce:infAdFisco',
            'dce:infAdFisco',
        ], $infDce);

        return new DaceAdditionalData(
            complementaryInformation: $this->nullIfBlank($complementaryInformation),
            taxInformation: $this->nullIfBlank($taxInformation),
            observations: null,
            legalText: $this->buildLegalText(),
        );
    }

    private function mapAssets(DaceDocumentData $document, DacePartyData $issuer): DaceVisualAssetsData
    {
        $environment = $document->isHomologation() ? 2 : 1;
        $accessKey = $document->getAccessKey();

        if ($document->isContingency()) {
            $qrCodeValue = $this->qrCodeUrlBuilder->buildOfflineContingency(
                baseUrl: $this->qrCodeBaseUrl,
                accessKey: $accessKey,
                environment: $environment,
                issuerDocument: $issuer->getDocument(),
                signature: 'ASSINATURA_OFFLINE_AQUI',
            );
        } else {
            $qrCodeValue = $this->qrCodeUrlBuilder->buildNormal(
                baseUrl: $this->qrCodeBaseUrl,
                accessKey: $accessKey,
                environment: $environment,
            );
        }

        return new DaceVisualAssetsData(
            barcodeValue: $accessKey,
            barcodeImageBase64: $this->barcodeGenerator->generateBase64($accessKey),
            qrCodeValue: $qrCodeValue,
            qrCodeImageBase64: $this->qrCodeGenerator->generateBase64($qrCodeValue),
        );
    }

    private function extractAccessKey(DOMElement $infDce): string
    {
        $id = trim($infDce->getAttribute('Id'));

        if ($id === '') {
            throw new RuntimeException('Atributo Id de infDCe não encontrado.');
        }

        $accessKey = preg_replace('/^DCe/i', '', $id) ?? '';

        if (strlen(preg_replace('/\D+/', '', $accessKey) ?? '') !== 44) {
            throw new RuntimeException('Chave de acesso inválida no atributo Id de infDCe.');
        }

        return $accessKey;
    }

    private function extractEnvironment(DOMXPath $xpath, DOMElement $ide): int
    {
        $value = $this->firstValueFromQueries($xpath, ['dce:tpAmb'], $ide);

        return (int) ($value ?: 1);
    }

    private function extractEmissionType(DOMXPath $xpath, DOMElement $ide): int
    {
        $value = $this->firstValueFromQueries($xpath, ['dce:tpEmis'], $ide);

        return (int) ($value ?: 1);
    }

    private function extractTotalAmount(DOMXPath $xpath, DOMElement $infDce): float
    {
        $directAmount = $this->firstValueFromQueries($xpath, [
            'dce:total/dce:vDCe',
            'dce:total/dce:vProd',
            'dce:vDCe',
            'dce:vProd',
            'dce:vCarga',
        ], $infDce);

        if ($directAmount !== null && is_numeric(str_replace(',', '.', $directAmount))) {
            return (float) str_replace(',', '.', $directAmount);
        }

        $sum = 0.0;
        $detNodes = $xpath->query('dce:det', $infDce);

        if ($detNodes instanceof DOMNodeList) {
            foreach ($detNodes as $detNode) {
                if (!$detNode instanceof DOMElement) {
                    continue;
                }

                $amount = $this->firstValueFromQueries($xpath, [
                    'dce:prod/dce:vProd',
                    'dce:vProd',
                    'dce:vItem',
                    'dce:vMerc',
                ], $detNode);

                if ($amount !== null && is_numeric(str_replace(',', '.', $amount))) {
                    $sum += (float) str_replace(',', '.', $amount);
                }
            }
        }

        return $sum;
    }

    private function buildTransportModeLabel(DOMXPath $xpath, DOMElement $ide): string
    {
        $code = $this->firstValueFromQueries($xpath, [
            'dce:modal',
            'dce:modTransp',
            'dce:tpTransp',
        ], $ide);

        $description = $this->firstValueFromQueries($xpath, [
            'dce:xModal',
            'dce:xModTransp',
            'dce:descModal',
        ], $ide);

        if ($code !== null && $description !== null) {
            return sprintf('%s-%s', $code, $description);
        }

        if ($code !== null) {
            return $code;
        }

        return 'NÃO INFORMADO';
    }

    private function buildFullAddressFromNode(DOMXPath $xpath, DOMElement $addressNode): string
    {
        return $this->buildFullAddress(
            street: $this->firstValueFromQueries($xpath, ['dce:xLgr'], $addressNode),
            number: $this->firstValueFromQueries($xpath, ['dce:nro'], $addressNode),
            complement: $this->firstValueFromQueries($xpath, ['dce:xCpl'], $addressNode),
            district: $this->firstValueFromQueries($xpath, ['dce:xBairro'], $addressNode),
            city: $this->firstValueFromQueries($xpath, ['dce:xMun'], $addressNode),
            uf: $this->firstValueFromQueries($xpath, ['dce:UF'], $addressNode),
            zipCode: $this->firstValueFromQueries($xpath, ['dce:CEP'], $addressNode),
        );
    }

    private function buildCityUf(?string $city, ?string $uf): string
    {
        $city = trim((string) $city);
        $uf = trim((string) $uf);

        if ($city !== '' && $uf !== '') {
            return sprintf('%s - %s', $city, strtoupper($uf));
        }

        return $city !== '' ? $city : $uf;
    }

    private function buildFullAddress(
        ?string $street,
        ?string $number,
        ?string $complement,
        ?string $district,
        ?string $city,
        ?string $uf,
        ?string $zipCode,
    ): string {
        $parts = [];

        $line1 = trim(implode(', ', array_filter([
            $this->normalizeText($street),
            $this->normalizeText($number),
        ])));

        if ($line1 !== '') {
            $parts[] = $line1;
        }

        $line2 = trim(implode(' - ', array_filter([
            $this->normalizeText($complement),
            $this->normalizeText($district),
        ])));

        if ($line2 !== '') {
            $parts[] = $line2;
        }

        $cityUf = $this->buildCityUf($city, $uf);
        if ($cityUf !== '') {
            $parts[] = $cityUf;
        }

        $zipCode = $this->formatZipCode($zipCode);
        if ($zipCode !== null) {
            $parts[] = 'CEP ' . $zipCode;
        }

        return implode(' - ', $parts);
    }

    private function buildLegalText(): string
    {
        return 'É contribuinte de ICMS qualquer pessoa física ou jurídica, que realize, com habitualidade ou em volume que caracterize intuito comercial, operações de circulação de mercadoria ou prestações de serviços de transportes interestadual e intermunicipal e de comunicação, ainda que as operações e prestações se iniciem no exterior, conforme art. 4º da Lei Complementar nº 87/96.'
            . "\n\n"
            . 'Constitui crime contra a ordem tributária suprimir ou reduzir tributo, ou contribuição social e qualquer acessório: quando negar ou deixar de fornecer, quando obrigatório, nota fiscal ou documento equivalente, relativa a venda de mercadoria ou prestação de serviço, efetivamente realizada ou fornecê-la em desacordo com a legislação, sob pena de reclusão de dois a cinco anos, e multa, conforme inciso V do art. 1º da Lei nº 8.137/90.';
    }

    private function formatAccessKey(string $accessKey): string
    {
        $numbersOnly = preg_replace('/\D+/', '', $accessKey) ?? '';

        if (strlen($numbersOnly) !== 44) {
            throw new RuntimeException('A chave de acesso da DC-e deve conter 44 dígitos.');
        }

        return trim(implode(' ', str_split($numbersOnly, 4)));
    }

    private function formatDocumentNumber(string $number): string
    {
        $numbersOnly = preg_replace('/\D+/', '', $number) ?? '';

        if ($numbersOnly === '') {
            return $number;
        }

        return str_pad($numbersOnly, 9, '0', STR_PAD_LEFT);
    }

    private function formatIssueDateTime(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        try {
            return (new \DateTimeImmutable($value))->format('d-m-Y H:i:s');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function formatMoney(float|int|string|null $value): string
    {
        $number = (float) str_replace(',', '.', (string) $value);

        return 'R$ ' . number_format($number, 2, ',', '.');
    }

    private function formatQuantity(float|int|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $number = (float) str_replace(',', '.', (string) $value);

        if (fmod($number, 1.0) === 0.0) {
            return (string) (int) $number;
        }

        return number_format($number, 3, ',', '.');
    }

    private function formatCpfCnpjOrRaw(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $numbersOnly = preg_replace('/\D+/', '', $value) ?? '';

        if ($numbersOnly === '') {
            return null;
        }

        if (strlen($numbersOnly) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $numbersOnly);
        }

        if (strlen($numbersOnly) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $numbersOnly);
        }

        return $value;
    }

    private function formatZipCode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $numbersOnly = preg_replace('/\D+/', '', $value) ?? '';

        if ($numbersOnly === '') {
            return null;
        }

        if (strlen($numbersOnly) !== 8) {
            return $numbersOnly;
        }

        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $numbersOnly);
    }

    private function normalizeText(?string $value): string
    {
        return trim((string) $value);
    }

    private function nullIfBlank(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function getRequiredElement(
        DOMXPath $xpath,
        string $query,
        string $errorMessage,
        ?DOMNode $contextNode = null,
    ): DOMElement {
        $node = $this->firstNode($xpath, $query, $contextNode);

        if (!$node instanceof DOMElement) {
            throw new RuntimeException($errorMessage);
        }

        return $node;
    }

    private function findFirstElementByQueries(
        DOMXPath $xpath,
        array $queries,
        ?DOMNode $contextNode = null,
    ): ?DOMElement {
        foreach ($queries as $query) {
            $node = $this->firstNode($xpath, $query, $contextNode);

            if ($node instanceof DOMElement) {
                return $node;
            }
        }

        return null;
    }

    private function firstValueFromQueries(
        DOMXPath $xpath,
        array $queries,
        ?DOMNode $contextNode = null,
    ): ?string {
        foreach ($queries as $query) {
            $value = $this->firstValue($xpath, $query, $contextNode);

            if ($value !== null && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function firstValue(DOMXPath $xpath, string $query, ?DOMNode $contextNode = null): ?string
    {
        $node = $this->firstNode($xpath, $query, $contextNode);

        if ($node === null) {
            return null;
        }

        return trim($node->textContent);
    }

    private function firstNode(DOMXPath $xpath, string $query, ?DOMNode $contextNode = null): ?DOMNode
    {
        $nodes = $xpath->query($query, $contextNode);

        if (!$nodes instanceof DOMNodeList || $nodes->length === 0) {
            return null;
        }

        return $nodes->item(0);
    }
}
