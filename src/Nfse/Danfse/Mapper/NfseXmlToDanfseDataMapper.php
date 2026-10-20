<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Danfse\Mapper;

use BetoCampoy\Champs\Fiscal\Dce\Dace\Contract\DaceQrCodeGeneratorInterface;
use BetoCampoy\Champs\Fiscal\Dce\Dace\Service\EndroidDaceQrCodeGenerator;
use BetoCampoy\Champs\Fiscal\Nfse\Danfse\Dto\DanfseData;
use BetoCampoy\Champs\Fiscal\Nfse\Danfse\Dto\DanfsePartyData;
use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use Throwable;

/**
 * Converte o XML autorizado da NFS-e Nacional (<NFSe><infNFSe>…<DPS>…) em DanfseData,
 * permitindo gerar o DANFSe localmente, sem depender do serviço /danfse do ADN.
 */
final class NfseXmlToDanfseDataMapper
{
    private const NS = 'http://www.sped.fazenda.gov.br/nfse';

    public const DEFAULT_QR_CODE_BASE_URL = 'https://www.nfse.gov.br/ConsultaPublica/?tpc=1&chave=';

    private const SIMPLES_NACIONAL = [
        '1' => 'Não Optante',
        '2' => 'Optante - Microempreendedor Individual (MEI)',
        '3' => 'Optante - Microempresa ou Empresa de Pequeno Porte (ME/EPP)',
    ];

    private const SIMPLES_NACIONAL_REGIME = [
        '1' => 'Regime de apuração dos tributos federais e municipal pelo SN',
        '2' => 'Regime de apuração dos tributos federais pelo SN e o ISSQN pela NFS-e conforme respectiva legislação municipal do tributo',
        '3' => 'Regime de apuração dos tributos federais e municipal pela NFS-e conforme respectivas legislações federal e municipal de cada tributo',
    ];

    private const ISSQN_TAXATION = [
        '1' => 'Operação Tributável',
        '2' => 'Imunidade',
        '3' => 'Exportação de Serviço',
        '4' => 'Não Incidência',
    ];

    private const SPECIAL_REGIME = [
        '0' => 'Nenhum',
        '1' => 'Ato Cooperado (Cooperativa)',
        '2' => 'Estimativa',
        '3' => 'Microempresa Municipal',
        '4' => 'Notário ou Registrador',
        '5' => 'Profissional Autônomo',
        '6' => 'Sociedade de Profissionais',
    ];

    private const IMMUNITY_TYPE = [
        '0' => 'Imunidade (tipo não informado)',
        '1' => 'Patrimônio, renda ou serviços, uns dos outros (CF/88, art. 150, VI, a)',
        '2' => 'Templos de qualquer culto (CF/88, art. 150, VI, b)',
        '3' => 'Patrimônio, renda ou serviços dos partidos políticos, entidades sindicais dos trabalhadores, instituições de educação e de assistência social (CF/88, art. 150, VI, c)',
        '4' => 'Livros, jornais, periódicos e o papel destinado a sua impressão (CF/88, art. 150, VI, d)',
        '5' => 'Fonogramas e videofonogramas musicais produzidos no Brasil (CF/88, art. 150, VI, e)',
    ];

    private const SUSPENSION = [
        '1' => 'Exigibilidade suspensa por decisão judicial',
        '2' => 'Exigibilidade suspensa por processo administrativo',
    ];

    private const ISSQN_WITHHOLDING = [
        '1' => 'Não Retido',
        '2' => 'Retido pelo Tomador',
        '3' => 'Retido pelo Intermediário',
    ];

    private const PIS_COFINS_WITHHOLDING = [
        '1' => 'Retido',
        '2' => 'Não Retido',
    ];

    private DOMXPath $xpath;

    /** @var array<string, string> código IBGE => nome do município (os que o próprio XML traz) */
    private array $cityNames = [];

    public function __construct(
        private readonly ?DaceQrCodeGeneratorInterface $qrCodeGenerator = new EndroidDaceQrCodeGenerator(size: 200, margin: 4),
        private readonly string $qrCodeBaseUrl = self::DEFAULT_QR_CODE_BASE_URL,
    ) {
    }

    public function map(string $xml): DanfseData
    {
        if (trim($xml) === '') {
            throw new RuntimeException('XML da NFS-e vazio. Não é possível gerar o DANFSe.');
        }

        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            throw new RuntimeException('XML da NFS-e inválido. Não é possível gerar o DANFSe.');
        }

        $this->xpath = new DOMXPath($dom);
        $this->xpath->registerNamespace('n', self::NS);

        $inf = $this->element('//n:infNFSe');
        $dps = $inf ? $this->element('n:DPS/n:infDPS', $inf) : null;

        if (!$inf || !$dps) {
            throw new RuntimeException('Estrutura da NFS-e não reconhecida (infNFSe/infDPS ausentes).');
        }

        $this->collectCityNames($inf, $dps);

        $accessKey = preg_replace('/^NFS/', '', (string) $inf->getAttribute('Id'));
        $qrCodeUrl = $accessKey !== '' ? $this->qrCodeBaseUrl . $accessKey : null;

        $tribMun = $this->element('n:valores/n:trib/n:tribMun', $dps);
        $tribFed = $this->element('n:valores/n:trib/n:tribFed', $dps);
        $totTrib = $this->element('n:valores/n:trib/n:totTrib', $dps);

        $issqnWithholdingCode = $this->text('n:tpRetISSQN', $tribMun);
        $pisCofinsWithholdingCode = $this->text('n:piscofins/n:tpRetPisCofins', $tribFed);

        $irrf   = $this->number('n:vRetIRRF', $tribFed);
        $cp     = $this->number('n:vRetCP', $tribFed);
        $csll   = $this->number('n:vRetCSLL', $tribFed);
        $pis    = $this->number('n:piscofins/n:vPis', $tribFed);
        $cofins = $this->number('n:piscofins/n:vCofins', $tribFed);

        $totalFederalWithheld = $this->number('n:valores/n:vTotalRet', $inf);
        if ($totalFederalWithheld === null && ($irrf !== null || $cp !== null || $csll !== null)) {
            $totalFederalWithheld = (float) $irrf + (float) $cp + (float) $csll
                + ($pisCofinsWithholdingCode === '1' ? (float) $pis + (float) $cofins : 0.0);
        }

        $nationalTaxCode = $this->text('n:serv/n:cServ/n:cTribNac', $dps);
        $nationalTaxDescription = $this->text('n:xTribNac', $inf);

        $municipalTaxCode = $this->text('n:serv/n:cServ/n:cTribMun', $dps);
        $municipalTaxDescription = $this->text('n:xTribMun', $inf);

        return new DanfseData(
            accessKey: $accessKey,
            production: $this->text('n:tpAmb', $dps) === '1',
            nfseNumber: $this->text('n:nNFSe', $inf),
            competence: $this->date($this->text('n:dCompet', $dps), 'd/m/Y'),
            issuedAt: $this->date($this->text('n:dhProc', $inf), 'd/m/Y H:i:s'),
            dpsNumber: $this->text('n:nDPS', $dps),
            dpsSeries: $this->text('n:serie', $dps),
            dpsIssuedAt: $this->date($this->text('n:dhEmi', $dps), 'd/m/Y H:i:s'),
            issuingCity: $this->text('n:xLocEmi', $inf),

            provider: $this->mapProvider($inf, $dps),
            taker: $this->mapParty($this->element('n:toma', $dps)),
            intermediary: $this->mapParty($this->element('n:interm', $dps)),

            nationalTaxCode: $this->joinCodeAndDescription($this->formatNationalTaxCode($nationalTaxCode), $nationalTaxDescription),
            municipalTaxCode: $this->joinCodeAndDescription($municipalTaxCode, $municipalTaxDescription),
            serviceLocation: $this->text('n:xLocPrestacao', $inf)
                ?? $this->cityName($this->text('n:serv/n:locPrest/n:cLocPrestacao', $dps)),
            serviceCountry: $this->text('n:serv/n:locPrest/n:cPaisPrestacao', $dps),
            serviceDescription: $this->text('n:serv/n:cServ/n:xDescServ', $dps),

            issqnTaxation: $this->label(self::ISSQN_TAXATION, $this->text('n:tribISSQN', $tribMun)),
            resultCountry: $this->text('n:cPaisResult', $tribMun),
            issqnIncidenceCity: $this->text('n:xLocIncid', $inf),
            specialRegime: $this->label(self::SPECIAL_REGIME, $this->text('n:prest/n:regTrib/n:regEspTrib', $dps)),
            immunityType: $this->label(self::IMMUNITY_TYPE, $this->text('n:tpImunidade', $tribMun)),
            suspension: $this->label(self::SUSPENSION, $this->text('n:exigSusp/n:tpSusp', $tribMun)),
            suspensionProcess: $this->text('n:exigSusp/n:nProcesso', $tribMun),
            municipalBenefit: $this->text('n:BM/n:nBM', $tribMun),
            serviceValue: $this->number('n:valores/n:vServPrest/n:vServ', $dps),
            unconditionalDiscount: $this->number('n:valores/n:vDescCondIncond/n:vDescIncond', $dps),
            conditionalDiscount: $this->number('n:valores/n:vDescCondIncond/n:vDescCond', $dps),
            deductions: $this->number('n:valores/n:vCalcDR', $inf),
            benefitCalculation: $this->number('n:valores/n:vCalcBM', $inf),
            issqnBase: $this->number('n:valores/n:vBC', $inf),
            issqnRate: $this->number('n:valores/n:pAliqAplic', $inf) ?? $this->number('n:pAliq', $tribMun),
            issqnWithholding: $this->label(self::ISSQN_WITHHOLDING, $issqnWithholdingCode),
            issqnWithheld: in_array($issqnWithholdingCode, ['2', '3'], true),
            issqnValue: $this->number('n:valores/n:vISSQN', $inf),

            irrf: $irrf,
            cp: $cp,
            csll: $csll,
            pis: $pis,
            cofins: $cofins,
            pisCofinsWithholding: $this->label(self::PIS_COFINS_WITHHOLDING, $pisCofinsWithholdingCode),
            totalFederalWithheld: $totalFederalWithheld,

            netValue: $this->number('n:valores/n:vLiq', $inf),
            approxFederalTaxes: $this->approxTax($totTrib, 'Fed'),
            approxStateTaxes: $this->approxTax($totTrib, 'Est'),
            approxMunicipalTaxes: $this->approxTax($totTrib, 'Mun'),
            approxSimplesNacional: $this->percent($this->number('n:pTotTribSN', $totTrib)),

            additionalInfo: $this->text('n:serv/n:infoCompl/n:xInfComp', $dps),

            qrCodeUrl: $qrCodeUrl,
            qrCodeBase64: $this->generateQrCode($qrCodeUrl),
        );
    }

    private function mapProvider(DOMElement $inf, DOMElement $dps): DanfsePartyData
    {
        $emit = $this->element('n:emit', $inf);
        $regTrib = $this->element('n:prest/n:regTrib', $dps);
        $party = $this->mapParty($emit, 'n:enderNac');

        return new DanfsePartyData(
            document: $party?->document,
            name: $party?->name,
            municipalRegistration: $party?->municipalRegistration,
            phone: $party?->phone,
            email: $party?->email,
            address: $party?->address,
            city: $party?->city,
            zipCode: $party?->zipCode,
            simplesNacional: $this->label(self::SIMPLES_NACIONAL, $this->text('n:opSimpNac', $regTrib)),
            simplesNacionalRegime: $this->label(self::SIMPLES_NACIONAL_REGIME, $this->text('n:regApTribSN', $regTrib)),
        );
    }

    /**
     * Prestador (emit/enderNac) e tomador/intermediário (end/endNac|endExt) têm
     * a mesma forma, só muda onde fica o endereço.
     */
    private function mapParty(?DOMElement $node, string $addressPath = 'n:end'): ?DanfsePartyData
    {
        if (!$node) {
            return null;
        }

        $document = $this->formatDocument($this->text('n:CNPJ', $node) ?? $this->text('n:CPF', $node))
            ?? $this->text('n:NIF', $node);

        $address = $this->element($addressPath, $node);
        $street = null;
        $city = null;
        $zipCode = null;

        if ($address) {
            $street = $this->joinNonEmpty([
                $this->joinNonEmpty([$this->text('n:xLgr', $address), $this->text('n:nro', $address)], ', '),
                $this->text('n:xCpl', $address),
                $this->text('n:xBairro', $address),
            ], ' - ');

            // emit/enderNac tem cMun/UF/CEP direto; toma/end tem endNac ou endExt
            $nac = $addressPath === 'n:enderNac' ? $address : $this->element('n:endNac', $address);
            $ext = $this->element('n:endExt', $address);

            if ($nac) {
                $city = $this->joinNonEmpty([$this->cityName($this->text('n:cMun', $nac)), $this->text('n:UF', $nac)], ' - ');
                $zipCode = $this->formatZipCode($this->text('n:CEP', $nac));
            } elseif ($ext) {
                $city = $this->joinNonEmpty([
                    $this->text('n:xCidade', $ext),
                    $this->text('n:xEstProvReg', $ext),
                    $this->text('n:cPais', $ext),
                ], ' - ');
                $zipCode = $this->text('n:cEndPost', $ext);
            }
        }

        return new DanfsePartyData(
            document: $document,
            name: $this->text('n:xNome', $node),
            municipalRegistration: $this->text('n:IM', $node),
            phone: $this->formatPhone($this->text('n:fone', $node)),
            email: $this->text('n:email', $node),
            address: $street,
            city: $city,
            zipCode: $zipCode,
        );
    }

    /**
     * O XML não traz o nome de todo município, só os de emissão, prestação e incidência.
     * Usamos esses pares código→nome para resolver os endereços; os demais ficam com o código IBGE.
     */
    private function collectCityNames(DOMElement $inf, DOMElement $dps): void
    {
        $pairs = [
            [$this->text('n:cLocEmi', $dps), $this->text('n:xLocEmi', $inf)],
            [$this->text('n:serv/n:locPrest/n:cLocPrestacao', $dps), $this->text('n:xLocPrestacao', $inf)],
            [$this->text('n:cLocIncid', $inf), $this->text('n:xLocIncid', $inf)],
        ];

        foreach ($pairs as [$code, $name]) {
            if ($code !== null && $name !== null) {
                $this->cityNames[$code] = $name;
            }
        }
    }

    private function cityName(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        return $this->cityNames[$code] ?? $code;
    }

    private function approxTax(?DOMElement $totTrib, string $suffix): ?string
    {
        $value = $this->number("n:vTotTrib/n:vTotTrib{$suffix}", $totTrib);
        if ($value !== null) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        }

        return $this->percent($this->number("n:pTotTrib/n:pTotTrib{$suffix}", $totTrib));
    }

    private function generateQrCode(?string $content): ?string
    {
        if ($content === null || $this->qrCodeGenerator === null) {
            return null;
        }

        try {
            return $this->qrCodeGenerator->generateBase64($content);
        } catch (Throwable) {
            return null; // QR Code é opcional — a chave de acesso continua impressa
        }
    }

    private function element(string $path, ?DOMElement $context = null): ?DOMElement
    {
        if ($context === null && !str_starts_with($path, '/')) {
            return null;
        }

        $node = $this->xpath->query($path, $context)?->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private function text(string $path, ?DOMElement $context): ?string
    {
        $node = $this->element($path, $context);
        $value = $node ? trim($node->textContent) : '';

        return $value !== '' ? $value : null;
    }

    private function number(string $path, ?DOMElement $context): ?float
    {
        $value = $this->text($path, $context);

        return is_numeric($value) ? (float) $value : null;
    }

    private function percent(?float $value): ?string
    {
        return $value !== null ? number_format($value, 2, ',', '.') . '%' : null;
    }

    private function date(?string $value, string $format): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format($format);
        } catch (Throwable) {
            return $value;
        }
    }

    /** @param array<string, string> $labels */
    private function label(array $labels, ?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        return $labels[$code] ?? $code;
    }

    private function formatNationalTaxCode(?string $code): ?string
    {
        if ($code === null || !preg_match('/^\d{6}$/', $code)) {
            return $code;
        }

        return substr($code, 0, 2) . '.' . substr($code, 2, 2) . '.' . substr($code, 4, 2);
    }

    private function joinCodeAndDescription(?string $code, ?string $description): ?string
    {
        return $this->joinNonEmpty([$code, $description], ' - ');
    }

    private function formatDocument(?string $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return match (strlen($digits)) {
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits),
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits),
            0 => null,
            default => $value,
        };
    }

    private function formatZipCode(?string $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return strlen($digits) === 8 ? substr($digits, 0, 5) . '-' . substr($digits, 5) : $value;
    }

    private function formatPhone(?string $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return match (strlen($digits)) {
            10 => preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits),
            11 => preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits),
            0 => null,
            default => $value,
        };
    }

    /** @param array<int, string|null> $parts */
    private function joinNonEmpty(array $parts, string $glue): ?string
    {
        $parts = array_filter($parts, static fn (?string $p) => $p !== null && $p !== '');

        return $parts !== [] ? implode($glue, $parts) : null;
    }
}
