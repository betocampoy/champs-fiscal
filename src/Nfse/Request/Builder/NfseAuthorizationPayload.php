<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Request\Builder;

use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseAddressData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseProviderData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseServiceData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseTakerData;
use BetoCampoy\Champs\Fiscal\Nfse\Common\NfseValuesData;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Input\NfseEmitRequest;
use DateTimeImmutable;

final class NfseAuthorizationPayload
{
    private NfseProviderData $provider;
    private NfseTakerData $taker;
    private NfseServiceData $service;
    private NfseValuesData $values;
    private int $rpsNumber;
    private string $rpsSeries;
    private DateTimeImmutable $emissionDate;
    private string $competenceDate;
    private int $environment;
    private ?string $additionalInfo;

    public function __construct(NfseEmitRequest $request, int $environment = 2)
    {
        $req = $request;

        $this->rpsNumber     = $req->rpsNumber;
        $this->rpsSeries     = $req->rpsSeries;
        $this->emissionDate  = $req->getEmissionDate();
        $this->competenceDate = $req->getCompetenceDate();
        $this->environment   = $environment;
        $this->additionalInfo = $req->additionalInfo;

        $this->provider = new NfseProviderData(
            cnpj: $req->provider->cnpj,
            cpf: $req->provider->cpf,
            municipalRegistration: $req->provider->municipalRegistration,
            emitterIbgeCode: $req->provider->emitterIbgeCode,
            simplesNacional: $req->provider->simplesNacional,
        );

        $address = null;
        if ($req->taker->address !== null) {
            $addr = $req->taker->address;
            $address = new NfseAddressData(
                ibgeCode: $addr->ibgeCode,
                zipCode: $addr->zipCode,
                street: $addr->street,
                number: $addr->number,
                complement: $addr->complement,
                neighborhood: $addr->neighborhood,
            );
        }

        $this->taker = new NfseTakerData(
            cnpj: $req->taker->cnpj,
            cpf: $req->taker->cpf,
            foreignId: $req->taker->foreignId,
            name: $req->taker->name,
            address: $address,
            email: $req->taker->email,
            phone: $req->taker->phone,
        );

        $this->service = new NfseServiceData(
            nationalServiceCode: $req->service->nationalServiceCode,
            municipalServiceCode: $req->service->municipalServiceCode,
            description: $req->service->description,
            serviceMunicipalityIbge: $req->service->serviceMunicipalityIbge,
            cnae: $req->service->cnae,
        );

        $this->values = new NfseValuesData(
            serviceValue: $req->values->serviceValue,
            issAliquot: $req->values->issAliquot,
            issIncidenceIbge: $req->service->serviceMunicipalityIbge,
            issRetained: $req->values->issRetained,
            unconditionalDiscount: $req->values->unconditionalDiscount,
            conditionalDiscount: $req->values->conditionalDiscount,
            pisValue: $req->values->pisValue,
            cofinsValue: $req->values->cofinsValue,
            inssValue: $req->values->inssValue,
            irValue: $req->values->irValue,
            csllValue: $req->values->csllValue,
        );
    }

    public function getRpsNumber(): int        { return $this->rpsNumber; }
    public function getRpsSeries(): string     { return $this->rpsSeries; }
    public function getEmissionDate(): DateTimeImmutable { return $this->emissionDate; }
    public function getCompetenceDate(): string { return $this->competenceDate; }
    public function getEnvironment(): int      { return $this->environment; }
    public function getProvider(): NfseProviderData { return $this->provider; }
    public function getTaker(): NfseTakerData  { return $this->taker; }
    public function getService(): NfseServiceData { return $this->service; }
    public function getValues(): NfseValuesData  { return $this->values; }
    public function getAdditionalInfo(): ?string { return $this->additionalInfo; }
}
