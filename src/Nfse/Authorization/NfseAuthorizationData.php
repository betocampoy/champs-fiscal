<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Authorization;

use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseProviderData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseServiceData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseTakerData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseValuesData;
use DateTimeImmutable;

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
            throw new \InvalidArgumentException('Número do RPS deve ser maior que zero.');
        }
    }

    public function getDpsId(): string
    {
        $doc = $this->provider->cnpj ?? $this->provider->cpf ?? '00000000000000';
        return 'DPS' . preg_replace('/\D/', '', $doc) . str_pad((string) $this->rpsNumber, 9, '0', STR_PAD_LEFT);
    }
}
