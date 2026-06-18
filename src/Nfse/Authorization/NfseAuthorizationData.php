<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Authorization;

use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseProviderData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseServiceData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseTakerData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseValuesData;
use DateTimeImmutable;
use InvalidArgumentException;

final class NfseAuthorizationData
{
    public function __construct(
        public readonly int $rpsNumber,
        public readonly string $rpsSeries,
        public readonly DateTimeImmutable $emissionDate,
        public readonly string $competenceDate,
        public readonly int $environment,
        public readonly NfseProviderData $provider,
        public readonly NfseTakerData $taker,
        public readonly NfseServiceData $service,
        public readonly NfseValuesData $values,
        public readonly ?string $additionalInfo = null,
        public readonly string $applicationVersion = '1.00',
    ) {
        if ($this->rpsNumber <= 0) {
            throw new InvalidArgumentException('Número do RPS deve ser maior que zero.');
        }
    }

    public function getDpsId(): string
    {
        $doc = preg_replace(
            '/\D/',
            '',
            (string) ($this->provider->cnpj ?? $this->provider->cpf ?? '')
        );

        if ($doc === '') {
            throw new InvalidArgumentException('Prestador deve ter CNPJ ou CPF para gerar o Id da DPS.');
        }

        $registrationType = strlen($doc) === 14 ? '2' : '1';
        $series = preg_replace('/\D/', '', $this->rpsSeries);
        $series = str_pad($series !== '' ? $series : '1', 5, '0', STR_PAD_LEFT);

        return sprintf(
            'DPS%s%s%s%s%s',
            preg_replace('/\D/', '', $this->provider->emitterIbgeCode),
            $registrationType,
            $doc,
            $series,
            str_pad((string) $this->rpsNumber, 15, '0', STR_PAD_LEFT)
        );
    }
}
