<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Builder;

use BetoCampoy\Champs\Fiscal\Dce\Enum\DceIssuerType;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAdditionalInfoRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAuthorizationRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAutXmlRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDestAddressRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDestRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDetRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmitAddressRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmitRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmpEmisPropRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceIdeRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceMarketplaceRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceProdRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTotalRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTranspRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTransportRequest;

final class DceAuthorizationPayloadBuilder
{
    public function build(DceAuthorizationRequest $request): DceAuthorizationPayload
    {
        $payload = [
            'ide' => $this->buildIde($request->getIde()),
            'emit' => $this->buildEmit($request->getEmit()),
            'dest' => $this->buildDest($request->getDest()),
            'det' => array_map(
                fn (DceDetRequest $detail) => $this->buildDetail($detail),
                $request->getDetails()
            ),
            'total' => $this->buildTotal($request->getTotal()),
            'transp' => $this->buildTransport($request->getTransport()),
        ];

        $this->appendIssuerSpecificGroup($payload, $request);

        if ($request->getAuthorizedXmlViewers() !== []) {
            $payload['autXML'] = array_map(
                fn (DceAutXmlRequest $viewer) => $this->buildAutXml($viewer),
                $request->getAuthorizedXmlViewers()
            );
        }

        if ($request->getAdditionalInfo() !== null) {
            $payload['infAdic'] = $this->buildAdditionalInfo($request->getAdditionalInfo());
        }

        return new DceAuthorizationPayload($this->filterNulls($payload));
    }

    private function appendIssuerSpecificGroup(array &$payload, DceAuthorizationRequest $request): void
    {
        $rawIssuerType = $request->getIde()?->getIssuerType();

        if ($rawIssuerType === null || $rawIssuerType === '') {
            return;
        }

        try {
            $issuerType = DceIssuerType::from((int) $rawIssuerType);
        } catch (\ValueError) {
            return;
        }

        match ($issuerType) {
            DceIssuerType::FISCO => null,

            DceIssuerType::MARKETPLACE =>
            $payload['Marketplace'] = $this->buildMarketplace($request->getMarketplace()),

            DceIssuerType::OWN => null,

            DceIssuerType::CARRIER =>
            $payload['Transportadora'] = $this->buildTransportadora($request->getTransp()),
        };
    }

    private function buildIde(?DceIdeRequest $ide): array
    {
        if ($ide === null) {
            return [];
        }

        return $this->filterNulls([
            'cUF' => $ide->getUfCode(),
            'cDC' => $ide->getRandomCode(),
            'mod' => $ide->getModel(),
            'serie' => $ide->getSeries(),
            'nDC' => $ide->getNumber(),
            'dhEmi' => $ide->getIssuedAt()?->format('Y-m-d\TH:i:sP'),
            'tpEmis' => $ide->getEmissionType(),
            'tpEmit' => $ide->getIssuerType(),
            'nSiteAutoriz' => $ide->getAuthorizerSiteNumber(),
            'cDV' => $ide->getCheckDigit(),
            'tpAmb' => $ide->getEnvironment(),
            'verProc' => $ide->getProcessVersion(),
        ]);
    }

    private function buildEmit(?DceEmitRequest $emit): array
    {
        if ($emit === null) {
            return [];
        }

        $payload = [
            'xNome' => $emit->getName(),
            'enderEmit' => $this->buildEmitAddress($emit->getAddress()),
        ];

        if ($this->hasText($emit->getCnpj())) {
            $payload['CNPJ'] = $emit->getCnpj();
        } elseif ($this->hasText($emit->getCpf())) {
            $payload['CPF'] = $emit->getCpf();
        } elseif ($this->hasText($emit->getOther())) {
            $payload['idOutros'] = $emit->getOther();
        }

        return $this->filterNulls($payload);
    }

    private function buildEmitAddress(?DceEmitAddressRequest $address): array
    {
        if ($address === null) {
            return [];
        }

        return $this->filterNulls([
            'xLgr' => $address->getStreet(),
            'nro' => $address->getNumber(),
            'xCpl' => $address->getComplement(),
            'xBairro' => $address->getNeighborhood(),
            'cMun' => $address->getCityCode(),
            'xMun' => $address->getCityName(),
            'UF' => $address->getState(),
            'CEP' => $address->getZipCode(),
            'cPais' => $address->getCountryCode(),
            'xPais' => $address->getCountryName(),
            'fone' => $address->getPhone(),
        ]);
    }

    private function buildMarketplace(?DceMarketplaceRequest $marketplace): array
    {
        if ($marketplace === null) {
            return [];
        }

        return $this->filterNulls([
            'CNPJ' => $marketplace->getCnpj(),
            'xNome' => $marketplace->getName(),
            'Site' => $marketplace->getSite(),
        ]);
    }

//    private function buildEmpEmisProp(
//        ?DceEmpEmisPropRequest $empEmisProp,
//        ?DceEmitRequest $emit
//    ): array {
//        return $this->filterNulls([
//            'CNPJ' => $empEmisProp?->getCnpj() ?: $emit?->getCnpj(),
//            'xNome' => $empEmisProp?->getName() ?: $emit?->getName(),
//        ]);
//    }

    private function buildTransportadora(?DceTranspRequest $transp): array
    {
        if ($transp === null) {
            return [];
        }

        $payload = [
            'xNome' => $transp->getName(),
        ];

        if ($this->hasText($transp->getCnpj())) {
            $payload['CNPJ'] = $transp->getCnpj();
        } elseif ($this->hasText($transp->getCpf())) {
            $payload['CPF'] = $transp->getCpf();
        }

        return $this->filterNulls($payload);
    }

    private function buildDest(?DceDestRequest $dest): array
    {
        if ($dest === null) {
            return [];
        }

        $payload = [
            'xNome' => $dest->getName(),
            'enderDest' => $this->buildDestAddress($dest->getAddress()),
        ];

        if ($this->hasText($dest->getCnpj())) {
            $payload['CNPJ'] = $dest->getCnpj();
        } elseif ($this->hasText($dest->getCpf())) {
            $payload['CPF'] = $dest->getCpf();
        } elseif ($this->hasText($dest->getOtherId())) {
            $payload['idOutros'] = $dest->getOtherId();
        }

        return $this->filterNulls($payload);
    }

    private function buildDestAddress(?DceDestAddressRequest $address): array
    {
        if ($address === null) {
            return [];
        }

        return $this->filterNulls([
            'xLgr' => $address->getStreet(),
            'nro' => $address->getNumber(),
            'xCpl' => $address->getComplement(),
            'xBairro' => $address->getNeighborhood(),
            'cMun' => $address->getCityCode(),
            'xMun' => $address->getCityName(),
            'UF' => $address->getState(),
            'CEP' => $address->getZipCode(),
            'cPais' => $address->getCountryCode(),
            'xPais' => $address->getCountryName(),
            'fone' => $address->getPhone(),
            'email' => $address->getEmail(),
        ]);
    }

    private function buildAutXml(DceAutXmlRequest $viewer): array
    {
        $payload = [];

        if ($this->hasText($viewer->getCnpj())) {
            $payload['CNPJ'] = $viewer->getCnpj();
        } elseif ($this->hasText($viewer->getCpf())) {
            $payload['CPF'] = $viewer->getCpf();
        }

        return $this->filterNulls($payload);
    }

    private function buildDetail(DceDetRequest $detail): array
    {
        $payload = [
            '@attributes' => [
                'nItem' => $detail->getItemNumber(),
            ],
            'prod' => $this->buildProd($detail->getProd()),
        ];

        if ($this->hasText($detail->getAdditionalInfo())) {
            $payload['infAdProd'] = $detail->getAdditionalInfo();
        }

        return $payload;
    }

    private function buildProd(?DceProdRequest $prod): array
    {
        if ($prod === null) {
            return [];
        }

        return $this->filterNulls([
            'xProd' => $prod->getName(),
            'NCM' => $prod->getNcm(),
            'qCom' => $prod->getCommercialQuantity(),
            'vUnCom' => $prod->getUnitValue(),
            'vProd' => $prod->getTotalValue(),
        ]);
    }

    private function buildTotal(?DceTotalRequest $total): array
    {
        if ($total === null) {
            return [];
        }

        return $this->filterNulls([
            'vDC' => $total->getTotalValue(),
        ]);
    }

    private function buildTransport(?DceTransportRequest $transport): array
    {
        if ($transport === null) {
            return [];
        }

        return $this->filterNulls([
            'modFrete' => $transport->getFreightMode(),
            'vFrete' => $transport->getFreightValue(),
            'CNPJTransp' => $transport->getCarrierCnpj(),
        ]);
    }

    private function buildAdditionalInfo(?DceAdditionalInfoRequest $additionalInfo): array
    {
        if ($additionalInfo === null) {
            return [];
        }

        return $this->filterNulls([
            'infCpl' => $additionalInfo->getComplementaryInfo(),
        ]);
    }

    private function filterNulls(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterNulls($value);
            }
        }

        return array_filter(
            $data,
            static fn ($value) => $value !== null && $value !== []
        );
    }

    private function hasText(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }
}
