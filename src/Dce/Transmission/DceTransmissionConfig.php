<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Transmission;

final class DceTransmissionConfig
{
    public function __construct(
        private readonly string $environment, // 1=prod | 2=homolog
    ) {}

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isProduction(): bool
    {

        return (int)$this->environment === (int)1;
    }

    public function getAuthorizationWsdl(): string
    {
        $env = $this->isProduction() ? 'prod' : 'homolog';
        $local = __DIR__ . "/../../resources/wsdl/dce/{$env}/DCeAutorizacao.wsdl";

        if (file_exists($local)) {
            return $local;
        }

        return $this->isProduction()
            ? 'https://dce.fazenda.pr.gov.br/dce/DCeAutorizacao?wsdl'
            : 'https://homologacao.dce.fazenda.pr.gov.br/dce/DCeAutorizacao?wsdl';
    }

    public function getAuthorizationOperation(): string
    {
        return 'dceAutorizacao';
    }

    public function getAuthorizationZipOperation(): string
    {
        return 'dceAutorizacaoZip';
    }

    public function getAuthorizationBodyKey(): string
    {
        return 'dceDadosMsg';
    }

    public function getAuthorizationZipBodyKey(): string
    {
        return 'dceDadosMsgZip';
    }

    public function getQueryWsdl(): string
    {
        return $this->isProduction()
            ? 'https://dce.fazenda.pr.gov.br/dce/DCeConsulta?wsdl'
            : 'https://homologacao.dce.fazenda.pr.gov.br/dce/DCeConsulta?wsdl';
    }

    public function getQueryOperation(): string
    {
        return 'dceConsulta';
    }

    public function getQueryBodyKey(): string
    {
        return 'dceDadosMsg';
    }

    public function getEventWsdl(): string
    {
        return $this->isProduction()
            ? 'https://dce.fazenda.pr.gov.br/dce/DCeRecepcaoEvento?wsdl'
            : 'https://homologacao.dce.fazenda.pr.gov.br/dce/DCeRecepcaoEvento?wsdl';
    }

    public function getEventOperation(): string
    {
        return 'dceRecepcaoEvento';
    }

    public function getEventBodyKey(): string
    {
        return 'dceDadosMsg';
    }
}
