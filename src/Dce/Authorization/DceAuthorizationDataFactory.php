<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Authorization;

use BetoCampoy\Champs\Fiscal\Dce\Enum\DceIssuerType;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Builder\DceAuthorizationPayload;
use DateTimeImmutable;
use InvalidArgumentException;

final class DceAuthorizationDataFactory
{
    public function __construct(
        private readonly DceAccessKeyGenerator $accessKeyGenerator,
        private readonly DceAuthorizationQrCodeBuilder $qrCodeBuilder,
    ) {
    }

    public function create(DceAuthorizationPayload $payload): DceAuthorizationData
    {
        $data = $payload->all();

        $ide = $payload->getIde();
        $emit = $payload->getEmit();
        $dest = $payload->getDest();
        $det = $payload->getDet();
        $transp = $payload->getTransp();

        $fisco = $payload->getFisco();
        $marketplace = $payload->getMarketplace();
        $empEmisProp = $payload->getEmpEmisProp();
        $transportadora = $payload->getTransportadora();

        $autXml = $payload->getAutXml();
        $infAdic = $payload->getInfAdic();

        $infDec = $data['infDec'] ?? [];
        $infSolicDCe = $data['infSolicDCe'] ?? [];

        $tpEmit = (int) ($ide['tpEmit'] ?? 0);
        $tpEmis = (int) ($ide['tpEmis'] ?? 0);
        $tpAmb = (int) ($ide['tpAmb'] ?? 0);
        $dhEmi = new DateTimeImmutable((string) ($ide['dhEmi'] ?? ''));

        $actorCnpj = $this->resolveActorCnpj(
            tpEmit: $tpEmit,
            emit: $emit,
            fisco: $fisco !== [] ? $fisco : null,
            marketplace: $marketplace !== [] ? $marketplace : null,
            transportadora: $transportadora !== [] ? $transportadora : null,
        );

        $accessKey = $this->accessKeyGenerator->generate(
            cUF: (string) ($ide['cUF'] ?? ''),
            dhEmi: $dhEmi,
            cnpjAutor: $actorCnpj,
            mod: (string) ($ide['mod'] ?? '99'),
            serie: (string) ($ide['serie'] ?? ''),
            nDC: (string) ($ide['nDC'] ?? ''),
            tpEmis: $tpEmis,
            tpEmit: $tpEmit,
            nSiteAutoriz: (int) ($ide['nSiteAutoriz'] ?? 0),
            cDC: (string) ($ide['cDC'] ?? ''),
        );

        $queryUrl = $this->resolveUrlChave($data, $tpAmb);

        $qrCode = $tpEmis === 9
            ? $this->buildOfflineQrCode($queryUrl, $accessKey, $tpAmb, $emit, $data)
            : $this->qrCodeBuilder->buildNormal($queryUrl, $accessKey, $tpAmb);

        $items = [];
        $total = 0.0;

        foreach ($det as $detail) {
            $prod = $detail['prod'] ?? [];

            $qCom = $this->toFloat($prod['qCom'] ?? null);
            $vUnCom = $this->toFloat($prod['vUnCom'] ?? null);
            $vProd = $this->toFloat($prod['vProd'] ?? null);

            $items[] = new DceAuthorizationItem(
                xProd: trim((string) ($prod['xProd'] ?? '')),
                ncm: isset($prod['NCM']) ? $this->digitsOrNull((string) $prod['NCM']) : null,
                qCom: $qCom,
                vUnCom: $vUnCom,
                vProd: $vProd,
                infAdProd: isset($detail['infAdProd']) ? $this->stringOrNull($detail['infAdProd']) : null,
            );

            $total += $vProd;
        }

        return new DceAuthorizationData(
            versao: '1.00',
            cUF: (int) $this->digits((string) ($ide['cUF'] ?? '')),
            cDC: $this->digits((string) ($ide['cDC'] ?? '')),
            mod: (int) $this->digits((string) ($ide['mod'] ?? '99')),
            serie: (int) $this->digits((string) ($ide['serie'] ?? '')),
            nDC: (int) $this->digits((string) ($ide['nDC'] ?? '')),
            dhEmi: $dhEmi,
            tpEmis: $tpEmis,
            tpEmit: $tpEmit,
            nSiteAutoriz: (int) ($ide['nSiteAutoriz'] ?? 0),
            tpAmb: $tpAmb,
            verProc: trim((string) ($ide['verProc'] ?? '')),

            emitCnpj: $this->digitsOrNull($emit['CNPJ'] ?? null),
            emitCpf: $this->digitsOrNull($emit['CPF'] ?? null),
            emitIdOutros: $this->stringOrNull($emit['idOutros'] ?? null),
            emitNome: trim((string) ($emit['xNome'] ?? '')),
            emitEndereco: $this->normalizeAddress($emit['enderEmit'] ?? []),

            fisco: $this->normalizeFisco($fisco !== [] ? $fisco : null),
            marketplace: $this->normalizeMarketplace($marketplace !== [] ? $marketplace : null),
            transportadoraEmissora: $this->normalizeTransportadoraEmissora($transportadora !== [] ? $transportadora : null),
            emissaoPropria: $this->normalizeEmpEmisProp($empEmisProp !== [] ? $empEmisProp : null),

            destCnpj: $this->digitsOrNull($dest['CNPJ'] ?? null),
            destCpf: $this->digitsOrNull($dest['CPF'] ?? null),
            destIdOutros: $this->stringOrNull($dest['idOutros'] ?? null),
            destNome: $this->stringOrNull($dest['xNome'] ?? null),
            destEndereco: $this->normalizeAddress($dest['enderDest'] ?? []),
            destEmail: $this->stringOrNull(
                ($dest['enderDest']['email'] ?? null) ?? ($dest['email'] ?? null)
            ),

            autXml: $this->normalizeAutXml($autXml),
            items: $items,
            vDC: round($total, 2),

            modTrans: (int) ($transp['modFrete'] ?? 0),
            cnpjTransp: $this->digitsOrNull(
                $transp['CNPJTransp'] ?? $transportadora['CNPJ'] ?? null
            ),

            infAdFisco: $this->stringOrNull($infAdic['infAdFisco'] ?? null),
            infCpl: $this->stringOrNull($infAdic['infCpl'] ?? null),
            infAdMarketplace: $this->stringOrNull($infAdic['infAdMarketplace'] ?? null),
            infAdTransp: $this->stringOrNull($infAdic['infAdTransp'] ?? null),

            xObs1: $this->defaultObs1($infDec['xObs1'] ?? null),
            xObs2: $this->defaultObs2($infDec['xObs2'] ?? null),

            xSolic: $this->stringOrNull($infSolicDCe['xSolic'] ?? null),

            qrCode: $qrCode,
            urlChave: $queryUrl,
            accessKey: $accessKey,
        );
    }

    /**
     * @param array<string, mixed>|null $fisco
     * @param array<string, mixed>|null $marketplace
     * @param array<string, mixed>|null $transportadora
     * @param array<string, mixed>|null $empEmisProp
     */
    private function resolveActorCnpj(
        int $tpEmit,
        array $emit,
        ?array $fisco,
        ?array $marketplace,
        ?array $transportadora,
    ): string {
        return match (DceIssuerType::from($tpEmit)) {
            DceIssuerType::FISCO =>
            $this->requiredDigits($fisco['CNPJ'] ?? null, 'Fisco.CNPJ'),

            DceIssuerType::MARKETPLACE =>
            $this->requiredDigits($marketplace['CNPJ'] ?? null, 'Marketplace.CNPJ'),

            DceIssuerType::OWN =>
            $this->requiredDigits($emit['CNPJ'] ?? null, 'emit.CNPJ'),

            DceIssuerType::CARRIER =>
            $this->requiredDigits($transportadora['CNPJ'] ?? null, 'Transportadora.CNPJ'),
        };
    }

    /**
     * @param array<string, mixed> $emit
     * @param array<string, mixed> $input
     */
    private function buildOfflineQrCode(
        string $baseUrl,
        string $accessKey,
        int $tpAmb,
        array $emit,
        array $input,
    ): string {
        if (!empty($emit['CNPJ'])) {
            return $this->qrCodeBuilder->buildOffline(
                $baseUrl,
                $accessKey,
                $tpAmb,
                'CNPJ',
                $this->digits((string) $emit['CNPJ']),
                (string) ($input['qrSign'] ?? ''),
            );
        }

        if (!empty($emit['CPF'])) {
            return $this->qrCodeBuilder->buildOffline(
                $baseUrl,
                $accessKey,
                $tpAmb,
                'CPF',
                $this->digits((string) $emit['CPF']),
                (string) ($input['qrSign'] ?? ''),
            );
        }

        return $this->qrCodeBuilder->buildOffline(
            $baseUrl,
            $accessKey,
            $tpAmb,
            'idOutros',
            trim((string) ($emit['idOutros'] ?? '')),
            (string) ($input['qrSign'] ?? ''),
        );
    }

    /**
     * @param array<string, mixed> $address
     * @return array<string, string|null>
     */
    private function normalizeAddress(array $address): array
    {
        return [
            'xLgr' => trim((string) ($address['xLgr'] ?? '')),
            'nro' => trim((string) ($address['nro'] ?? '')),
            'xCpl' => $this->stringOrNull($address['xCpl'] ?? null),
            'xBairro' => trim((string) ($address['xBairro'] ?? '')),
            'cMun' => $this->digits((string) ($address['cMun'] ?? '')),
            'xMun' => trim((string) ($address['xMun'] ?? '')),
            'UF' => trim((string) ($address['UF'] ?? '')),
            'CEP' => $this->digitsOrNull($address['CEP'] ?? null),
            'cPais' => $this->digits((string) ($address['cPais'] ?? '')),
            'xPais' => trim((string) ($address['xPais'] ?? '')),
            'fone' => $this->digitsOrNull($address['fone'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function normalizeFisco(?array $data): ?array
    {
        if (!$data) {
            return null;
        }

        return [
            'cnpj' => $this->digits((string) ($data['CNPJ'] ?? '')),
            'xOrgao' => trim((string) ($data['xOrgao'] ?? '')),
            'uf' => trim((string) ($data['UF'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function normalizeMarketplace(?array $data): ?array
    {
        if (!$data) {
            return null;
        }

        return [
            'cnpj' => $this->digits((string) ($data['CNPJ'] ?? '')),
            'xNome' => trim((string) ($data['xNome'] ?? '')),
            'site' => trim((string) ($data['Site'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function normalizeTransportadoraEmissora(?array $data): ?array
    {
        if (!$data) {
            return null;
        }

        return [
            'cnpj' => $this->digitsOrNull($data['CNPJ'] ?? null),
            'xNome' => trim((string) ($data['xNome'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function normalizeEmpEmisProp(?array $data): ?array
    {
        if (!$data) {
            return null;
        }

        return [
            'cnpj' => $this->digits((string) ($data['CNPJ'] ?? '')),
            'xNome' => trim((string) ($data['xNome'] ?? '')),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $list
     * @return array<int, array{document:string,type:string}>
     */
    private function normalizeAutXml(array $list): array
    {
        $normalized = [];

        foreach ($list as $item) {
            if (!empty($item['CNPJ'])) {
                $normalized[] = [
                    'type' => 'CNPJ',
                    'document' => $this->digits((string) $item['CNPJ']),
                ];
                continue;
            }

            if (!empty($item['CPF'])) {
                $normalized[] = [
                    'type' => 'CPF',
                    'document' => $this->digits((string) $item['CPF']),
                ];
            }
        }

        return $normalized;
    }

    private function defaultObs1(?string $value): string
    {
        return $this->stringOrNull($value)
            ?? 'É contribuinte de ICMS qualquer pessoa física ou jurídica, que realize, com habitualidade ou em volume que caracterize intuito comercial, operações de circulação de mercadoria ou prestações de serviços de transportes interestadual e intermunicipal e de comunicação, ainda que as operações e prestações de iniciem no exterior (Lei Complementar nº 87/96, Art. 4º)';
    }

    private function defaultObs2(?string $value): string
    {
        return $this->stringOrNull($value)
            ?? 'Constitui crime contra a ordem tributária suprimir ou reduzir tributo, ou contribuição social e qualquer acessório: quando negar ou deixar de fornecer, quando obrigatório, nota fiscal ou documento equivalente, relativa a venda de mercadoria ou prestação de serviço, efetivamente realizada ou fornece-la em desacordo com a legislação. Sob pena de reclusão de 2 (dois) e 5 (cinco) anos, e multa (Lei 8.137/90, Art 1ª, V).';
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function digitsOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $digits = $this->digits((string) $value);

        return $digits === '' ? null : $digits;
    }

    private function requiredDigits(mixed $value, string $field): string
    {
        $digits = $this->digitsOrNull($value);

        if ($digits === null) {
            throw new InvalidArgumentException(sprintf(
                'Campo obrigatório não informado para tpEmit: %s',
                $field
            ));
        }

        return $digits;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function toFloat(mixed $value): float
    {
        if ($value === null || trim((string) $value) === '') {
            throw new InvalidArgumentException('Valor decimal não informado.');
        }

        $normalized = str_replace(',', '.', trim((string) $value));

        if (!is_numeric($normalized)) {
            throw new InvalidArgumentException("Valor decimal inválido: {$normalized}");
        }

        return (float) $normalized;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveUrlChave(array $data, int $tpAmb): string
    {
        $url = trim((string) ($data['urlChave'] ?? ''));

        if ($url !== '') {
            return $url;
        }

        return match ($tpAmb) {
            1, 2 => 'https://www.fazenda.pr.gov.br/dce/qrcode',
            default => 'https://www.fazenda.pr.gov.br/dce/qrcode',
        };
    }
}
