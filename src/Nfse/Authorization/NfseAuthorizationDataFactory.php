<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Authorization;

use BetoCampoy\Champs\Fiscal\Nfse\Request\Builder\NfseAuthorizationPayload;

final class NfseAuthorizationDataFactory
{
    public function create(NfseAuthorizationPayload $payload): NfseAuthorizationData
    {
        return new NfseAuthorizationData(
            rpsNumber:       $payload->getRpsNumber(),
            rpsSeries:       $payload->getRpsSeries(),
            emissionDate:    $payload->getEmissionDate(),
            competenceDate:  $payload->getCompetenceDate(),
            environment:     $payload->getEnvironment(),
            provider:        $payload->getProvider(),
            taker:           $payload->getTaker(),
            service:         $payload->getService(),
            values:          $payload->getValues(),
            additionalInfo:  $payload->getAdditionalInfo(),
        );
    }
}
