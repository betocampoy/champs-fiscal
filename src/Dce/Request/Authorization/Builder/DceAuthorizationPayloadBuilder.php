<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Builder;

use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAdditionalInfoRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAuthorizationRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAutXmlRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDestAddressRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDestRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDetRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmitAddressRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmitRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceIdeRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceProdRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTotalRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTransportRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTranspRequest;

/**
 * Constrói o payload técnico da autorização da DC-e
 * a partir da request semântica.
 */
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

        $issuerType = $request->getIde()?->getIssuerType();

        if ($issuerType === '3') {
            $payload['Transportadora'] = $this->buildTransportadora($request->getTransp());
        }

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

        if ($emit->getCnpj() !== null && $emit->getCnpj() !== '') {
            $payload['CNPJ'] = $emit->getCnpj();
        } elseif ($emit->getCpf() !== null && $emit->getCpf() !== '') {
            $payload['CPF'] = $emit->getCpf();
        } elseif ($emit->getOther() !== null && $emit->getOther() !== '') {
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

    private function buildTransportadora(?DceTranspRequest $transp): array
    {
        if ($transp === null) {
            return [];
        }

        $payload = [
            'xNome' => $transp->getName(),
        ];

        if ($transp->getCnpj() !== null && $transp->getCnpj() !== '') {
            $payload['CNPJ'] = $transp->getCnpj();
        } elseif ($transp->getCpf() !== null && $transp->getCpf() !== '') {
            $payload['CPF'] = $transp->getCpf();
        } elseif ($transp->getOtherId() !== null && $transp->getOtherId() !== '') {
            $payload['idOutros'] = $transp->getOtherId();
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

        if ($dest->getCnpj() !== null && $dest->getCnpj() !== '') {
            $payload['CNPJ'] = $dest->getCnpj();
        } elseif ($dest->getCpf() !== null && $dest->getCpf() !== '') {
            $payload['CPF'] = $dest->getCpf();
        } elseif ($dest->getOtherId() !== null && $dest->getOtherId() !== '') {
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

        if ($viewer->getCnpj() !== null && $viewer->getCnpj() !== '') {
            $payload['CNPJ'] = $viewer->getCnpj();
        } elseif ($viewer->getCpf() !== null && $viewer->getCpf() !== '') {
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

        if ($detail->getAdditionalInfo() !== null && $detail->getAdditionalInfo() !== '') {
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
}
