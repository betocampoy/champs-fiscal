<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Query\Validator;

use BetoCampoy\Champs\Fiscal\Dce\Request\Query\Input\DceQueryRequest;

final class DceQueryRequestValidator
{
    public function validate(DceQueryRequest $request): void
    {
        $this->validateAccessKey($request->getAccessKey());
        $this->validateEnvironment($request->getEnvironment());
        $this->validateVersion($request->getVersion());
        $this->validateService($request->getService());
    }

    private function validateAccessKey(?string $accessKey): void
    {
        if ($accessKey === null || $accessKey === '') {
            throw new \InvalidArgumentException('A chave de acesso é obrigatória.');
        }

        if (!preg_match('/^\d{44}$/', $accessKey)) {
            throw new \InvalidArgumentException('A chave de acesso deve conter 44 dígitos.');
        }
    }

    private function validateEnvironment(?string $environment): void
    {
        if ($environment === null || $environment === '') {
            throw new \InvalidArgumentException('O ambiente é obrigatório.');
        }

        if (!in_array($environment, ['1', '2'], true)) {
            throw new \InvalidArgumentException('O ambiente deve ser 1 (produção) ou 2 (homologação).');
        }
    }

    private function validateVersion(?string $version): void
    {
        if ($version === null || $version === '') {
            throw new \InvalidArgumentException('A versão é obrigatória.');
        }

        if ($version !== '1.00') {
            throw new \InvalidArgumentException('A versão da consulta deve ser 1.00.');
        }
    }

    private function validateService(?string $service): void
    {
        if ($service === null || $service === '') {
            throw new \InvalidArgumentException('O serviço é obrigatório.');
        }

        if ($service !== 'CONSULTAR') {
            throw new \InvalidArgumentException('O serviço da consulta deve ser CONSULTAR.');
        }
    }
}
