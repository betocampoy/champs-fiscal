<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Request\Input;

use DateTimeImmutable;

final class NfseEmitRequest
{
    public function __construct(
        public readonly NfseProviderRequest $provider,
        public readonly NfseTakerRequest $taker,
        public readonly NfseServiceRequest $service,
        public readonly NfseValuesRequest $values,
        public readonly int $rpsNumber,
        public readonly string $rpsSeries = 'E',
        public readonly ?DateTimeImmutable $emissionDate = null,
        public readonly ?string $competenceDate = null,
        public readonly ?string $additionalInfo = null,
    ) {
    }

    public function getEmissionDate(): DateTimeImmutable
    {
        return $this->emissionDate ?? new DateTimeImmutable();
    }

    public function getCompetenceDate(): string
    {
        return $this->competenceDate ?? $this->getEmissionDate()->format('Y-m-d');
    }
}
