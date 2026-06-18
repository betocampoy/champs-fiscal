<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Authorization;

use InvalidArgumentException;

final class NfseAuthorizationDataValidator
{
    public function validate(NfseAuthorizationData $data): void
    {
        $errors = [];

        $providerDoc = $this->onlyDigits($data->provider->cnpj ?? $data->provider->cpf ?? null);

        if ($providerDoc === '') {
            $errors[] = 'Prestador: informe CNPJ ou CPF.';
        }

        if ($data->provider->cnpj !== null && strlen($this->onlyDigits($data->provider->cnpj)) !== 14) {
            $errors[] = 'Prestador: CNPJ deve conter 14 dígitos.';
        }

        if ($data->provider->cpf !== null && strlen($this->onlyDigits($data->provider->cpf)) !== 11) {
            $errors[] = 'Prestador: CPF deve conter 11 dígitos.';
        }

        if ($this->onlyDigits($data->provider->emitterIbgeCode) === '') {
            $errors[] = 'Prestador: informe o código IBGE do município emissor.';
        }

        if ($data->provider->municipalRegistration === '') {
            $errors[] = 'Prestador: informe a inscrição municipal.';
        }

        $takerDoc = $this->onlyDigits($data->taker->cnpj ?? $data->taker->cpf ?? null);

        if ($takerDoc === '' && empty($data->taker->foreignId)) {
            $errors[] = 'Tomador: informe CNPJ, CPF ou identificação estrangeira.';
        }

        if ($data->taker->cnpj !== null && strlen($this->onlyDigits($data->taker->cnpj)) !== 14) {
            $errors[] = 'Tomador: CNPJ deve conter 14 dígitos.';
        }

        if ($data->taker->cpf !== null && strlen($this->onlyDigits($data->taker->cpf)) !== 11) {
            $errors[] = 'Tomador: CPF deve conter 11 dígitos.';
        }

        if (trim((string) $data->taker->name) === '') {
            $errors[] = 'Tomador: informe o nome/razão social.';
        }

        if ($data->taker->address !== null) {
            $address = $data->taker->address;

            if ($this->onlyDigits($address->ibgeCode) === '') {
                $errors[] = 'Tomador: informe o código IBGE do município.';
            }

            $zipCode = str_pad(
                $this->onlyDigits($address->zipCode),
                8,
                '0',
                STR_PAD_LEFT
            );

            if ($zipCode === '') {
                $errors[] = 'Tomador: informe o CEP.';
            } elseif (strlen($zipCode) !== 8) {
                $errors[] = 'Tomador: CEP deve conter 8 dígitos.';
            }

            if (trim((string) $address->street) === '') {
                $errors[] = 'Tomador: informe o logradouro.';
            }

            if (trim((string) $address->number) === '') {
                $errors[] = 'Tomador: informe o número.';
            }

            if (trim((string) $address->neighborhood) === '') {
                $errors[] = 'Tomador: informe o bairro.';
            }
        }

        if (trim((string) $data->service->serviceMunicipalityIbge) === '') {
            $errors[] = 'Serviço: informe o município de prestação do serviço.';
        }

        if (trim((string) $data->service->nationalServiceCode) === '') {
            $errors[] = 'Serviço: informe o código nacional de tributação.';
        }

        if (trim((string) $data->service->description) === '') {
            $errors[] = 'Serviço: informe a descrição do serviço.';
        }

        if ($data->values->serviceValue <= 0) {
            $errors[] = 'Valores: o valor do serviço deve ser maior que zero.';
        }

        if ($data->values->issAliquot < 0 || $data->values->issAliquot > 1) {
            $errors[] = 'Valores: alíquota ISS deve estar em decimal entre 0 e 1. Exemplo: 0.06 para 6%.';
        }

        if ($data->values->issRetained && $data->taker->address === null) {
            $errors[] = 'Tomador: endereço obrigatório quando o ISSQN for retido pelo tomador.';
        }

        if ($errors !== []) {
            throw new InvalidArgumentException(
                "Dados inválidos para emissão da NFS-e:\n- " . implode("\n- ", $errors)
            );
        }
    }

    private function onlyDigits(?string $value): string
    {
        return preg_replace('/\D/', '', (string) $value);
    }
}
