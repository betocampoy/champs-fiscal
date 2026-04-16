<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Normalizer;

use BetoCampoy\Champs\Fiscal\Brazil\UfCodeMap;
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
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTranspRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTransportRequest;

/**
 * Normaliza a request de autorização da DC-e antes da validação.
 *
 * Responsabilidades:
 * - aplicar defaults internos seguros
 * - aplicar defaults externos opcionais
 * - fazer trim de strings
 * - remover máscaras de campos documentais
 * - padronizar UF e país
 * - não sobrescrever valores já informados
 */
final class DceAuthorizationRequestNormalizer
{
    /**
     * Normaliza a request.
     *
     * Prioridade:
     * input informado > default externo > default interno.
     *
     * @param array<string, string> $defaults
     */
    public function normalize(DceAuthorizationRequest $request, array $defaults = []): DceAuthorizationRequest
    {
        $defaults = array_replace($this->getInternalDefaults(), $defaults);

        $this->normalizeIde($request->getIde(), $defaults);
        $this->normalizeEmit($request->getEmit(), $defaults);
        $this->normalizeTransp($request->getTransp());
        $this->normalizeDest($request->getDest(), $defaults);
        $this->normalizeAuthorizedXmlViewers($request->getAuthorizedXmlViewers());
        $this->normalizeDetails($request->getDetails());
        $this->normalizeTotal($request->getTotal());
        $this->normalizeTransport($request->getTransport(), $defaults);
        $this->normalizeAdditionalInfo($request->getAdditionalInfo());

        $ide = $request->getIde();

        if ($ide !== null) {
            if (!$ide->getUfCode() && $ide->getState()) {
                $ide->setUfCode(UfCodeMap::codeFromState($ide->getState()));
            }
        }

        // ✅ NOVO: gerar cDC automaticamente se não informado
        if (!$ide->getRandomCode()) {
            $ide->setRandomCode(
                $this->generateRandomCode($ide->getNumber())
            );
        }

        return $request;
    }

    /**
     * @return array<string, string>
     */
    private function getInternalDefaults(): array
    {
        return [
            'ide.model' => '99',
            'ide.authorizerSiteNumber' => '0',

            'emit.address.countryCode' => '1058',
            'emit.address.countryName' => 'Brasil',

            'dest.address.countryCode' => '1058',
            'dest.address.countryName' => 'Brasil',
        ];
    }

    /**
     * @param array<string, string> $defaults
     */
    private function normalizeIde(?DceIdeRequest $ide, array $defaults): void
    {
        if ($ide === null) {
            return;
        }

        $ide->setState($this->upperOrNull($ide->getState()));
        $ide->setRandomCode($this->digitsOnly($ide->getRandomCode()));
        $ide->setModel($this->withDefault($ide->getModel(), $defaults['ide.model'] ?? null));
        $ide->setSeries($this->digitsOnly($ide->getSeries()));
        $ide->setNumber($this->digitsOnly($ide->getNumber()));
        $ide->setEmissionType($this->trimOrNull($ide->getEmissionType()));
        $ide->setIssuerType($this->trimOrNull($ide->getIssuerType()));
        $ide->setAuthorizerSiteNumber(
            $this->withDefault($ide->getAuthorizerSiteNumber(), $defaults['ide.authorizerSiteNumber'] ?? null)
        );
        $ide->setCheckDigit($this->digitsOnly($ide->getCheckDigit()));
        $ide->setEnvironment($this->withDefault($ide->getEnvironment(), $defaults['ide.environment'] ?? null));
        $ide->setProcessVersion($this->withDefault($ide->getProcessVersion(), $defaults['ide.processVersion'] ?? null));
    }

    /**
     * @param array<string, string> $defaults
     */
    private function normalizeEmit(?DceEmitRequest $emit, array $defaults): void
    {
        if ($emit === null) {
            return;
        }

        $emit->setCnpj($this->digitsOnly($emit->getCnpj()));
        $emit->setCpf($this->digitsOnly($emit->getCpf()));
        $emit->setOther($this->trimOrNull($emit->getOther()));
        $emit->setName($this->trimOrNull($emit->getName()));

        $this->normalizeEmitAddress($emit->getAddress(), $defaults);
    }

    /**
     * @param array<string, string> $defaults
     */
    private function normalizeEmitAddress(?DceEmitAddressRequest $address, array $defaults): void
    {
        if ($address === null) {
            return;
        }

        $address->setStreet($this->trimOrNull($address->getStreet()));
        $address->setNumber($this->trimOrNull($address->getNumber()));
        $address->setComplement($this->trimOrNull($address->getComplement()));
        $address->setNeighborhood($this->trimOrNull($address->getNeighborhood()));
        $address->setCityCode($this->digitsOnly($address->getCityCode()));
        $address->setCityName($this->trimOrNull($address->getCityName()));
        $address->setState($this->upperOrNull($address->getState()));
        $address->setZipCode($this->digitsOnly($address->getZipCode()));
        $address->setCountryCode(
            $this->digitsOnly($this->withDefault($address->getCountryCode(), $defaults['emit.address.countryCode'] ?? null))
        );
        $address->setCountryName(
            $this->countryNameOrNull(
                $this->withDefault($address->getCountryName(), $defaults['emit.address.countryName'] ?? null)
            )
        );
        $address->setPhone($this->digitsOnly($address->getPhone()));
    }

    private function normalizeTransp(?DceTranspRequest $transp): void
    {
        if ($transp === null) {
            return;
        }

        $transp->setCnpj($this->digitsOnly($transp->getCnpj()));
        $transp->setCpf($this->digitsOnly($transp->getCpf()));
        $transp->setName($this->trimOrNull($transp->getName()));
    }

    /**
     * @param array<string, string> $defaults
     */
    private function normalizeDest(?DceDestRequest $dest, array $defaults): void
    {
        if ($dest === null) {
            return;
        }

        $dest->setCnpj($this->digitsOnly($dest->getCnpj()));
        $dest->setCpf($this->digitsOnly($dest->getCpf()));
        $dest->setOtherId($this->trimOrNull($dest->getOtherId()));
        $dest->setName($this->trimOrNull($dest->getName()));

        $this->normalizeDestAddress($dest->getAddress(), $defaults);
    }

    /**
     * @param array<string, string> $defaults
     */
    private function normalizeDestAddress(?DceDestAddressRequest $address, array $defaults): void
    {
        if ($address === null) {
            return;
        }

        $address->setStreet($this->trimOrNull($address->getStreet()));
        $address->setNumber($this->trimOrNull($address->getNumber()));
        $address->setComplement($this->trimOrNull($address->getComplement()));
        $address->setNeighborhood($this->trimOrNull($address->getNeighborhood()));
        $address->setCityCode($this->digitsOnly($address->getCityCode()));
        $address->setCityName($this->trimOrNull($address->getCityName()));
        $address->setState($this->upperOrNull($address->getState()));
        $address->setZipCode($this->digitsOnly($address->getZipCode()));
        $address->setCountryCode(
            $this->digitsOnly($this->withDefault($address->getCountryCode(), $defaults['dest.address.countryCode'] ?? null))
        );
        $address->setCountryName(
            $this->countryNameOrNull(
                $this->withDefault($address->getCountryName(), $defaults['dest.address.countryName'] ?? null)
            )
        );
        $address->setPhone($this->digitsOnly($address->getPhone()));
        $address->setEmail($this->trimOrNull($address->getEmail()));
    }

    /**
     * @param DceAutXmlRequest[] $authorizedXmlViewers
     */
    private function normalizeAuthorizedXmlViewers(array $authorizedXmlViewers): void
    {
        foreach ($authorizedXmlViewers as $viewer) {
            $viewer->setCnpj($this->digitsOnly($viewer->getCnpj()));
            $viewer->setCpf($this->digitsOnly($viewer->getCpf()));
        }
    }

    /**
     * @param DceDetRequest[] $details
     */
    private function normalizeDetails(array $details): void
    {
        foreach ($details as $detail) {
            $detail->setItemNumber($this->digitsOnly($detail->getItemNumber()));
            $detail->setAdditionalInfo($this->trimOrNull($detail->getAdditionalInfo()));

            $this->normalizeProd($detail->getProd());
        }
    }

    private function normalizeProd(?DceProdRequest $prod): void
    {
        if ($prod === null) {
            return;
        }

        $prod->setName($this->trimOrNull($prod->getName()));
        $prod->setNcm($this->digitsOnly($prod->getNcm()) ?? '00');
        $prod->setCommercialQuantity($this->trimOrNull($prod->getCommercialQuantity()));
        $prod->setUnitValue($this->trimOrNull($prod->getUnitValue()));
        $prod->setTotalValue($this->trimOrNull($prod->getTotalValue()));
    }

    private function normalizeTotal(?DceTotalRequest $total): void
    {
        if ($total === null) {
            return;
        }

        $total->setTotalValue($this->trimOrNull($total->getTotalValue()));
    }

    /**
     * @param array<string, string> $defaults
     */
    private function normalizeTransport(?DceTransportRequest $transport, array $defaults): void
    {
        if ($transport === null) {
            return;
        }

        $transport->setFreightMode($this->withDefault($transport->getFreightMode(), $defaults['transport.freightMode'] ?? null));
        $transport->setFreightValue($this->trimOrNull($transport->getFreightValue()));
    }

    private function normalizeAdditionalInfo(?DceAdditionalInfoRequest $additionalInfo): void
    {
        if ($additionalInfo === null) {
            return;
        }

        $additionalInfo->setComplementaryInfo($this->trimOrNull($additionalInfo->getComplementaryInfo()));
    }

    private function trimOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function digitsOnly(?string $value): ?string
    {
        $value = $this->trimOrNull($value);

        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return $digits === '' ? null : $digits;
    }

    private function upperOrNull(?string $value): ?string
    {
        $value = $this->trimOrNull($value);

        return $value !== null ? mb_strtoupper($value) : null;
    }

    private function countryNameOrNull(?string $value): ?string
    {
        $value = $this->trimOrNull($value);

        if ($value === null) {
            return null;
        }

        if (mb_strtoupper($value) === 'BRASIL') {
            return 'Brasil';
        }

        return $value;
    }

    private function withDefault(?string $value, ?string $default): ?string
    {
        $value = $this->trimOrNull($value);

        if ($value !== null) {
            return $value;
        }

        return $this->trimOrNull($default);
    }

    private function generateRandomCode(?string $number): string
    {
        $numberDigits = $this->onlyDigits((string) $number);

        do {
            $randomCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (
            $this->isRepeatedSequence($randomCode)
            || ($numberDigits !== '' && $randomCode === str_pad(substr($numberDigits, -6), 6, '0', STR_PAD_LEFT))
        );

        return $randomCode;
    }

    private function isRepeatedSequence(string $value): bool
    {
        return preg_match('/^(\d)\1{5}$/', $value) === 1;
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
