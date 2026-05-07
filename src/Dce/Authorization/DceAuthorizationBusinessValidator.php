<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Authorization;

use BetoCampoy\Champs\Fiscal\Dce\Enum\DceIssuerType;
use InvalidArgumentException;

final class DceAuthorizationBusinessValidator
{
    public function validate(DceAuthorizationData $data): void
    {
        $this->validateModel($data);
        $this->validateEnvironment($data);
        $this->validateEmitter($data);
        $this->validateActorGroup($data);
        $this->validateRecipient($data);
        $this->validateItems($data);
        $this->validateTotals($data);
        $this->validateTransport($data);
        $this->validateQrCode($data);
    }

    private function validateModel(DceAuthorizationData $data): void
    {
        if ($data->mod !== 99) {
            throw new InvalidArgumentException('Modelo da DC-e deve ser 99.');
        }

        if (!preg_match('/^\d{44}$/', $data->accessKey)) {
            throw new InvalidArgumentException('Chave de acesso inválida.');
        }

        if (!preg_match('/^\d{6}$/', $data->cDC)) {
            throw new InvalidArgumentException('cDC deve conter 6 dígitos.');
        }
    }

    private function validateEnvironment(DceAuthorizationData $data): void
    {
        if (!in_array($data->tpAmb, [1, 2], true)) {
            throw new InvalidArgumentException('tpAmb inválido.');
        }

        if (!in_array($data->tpEmis, [1, 9], true)) {
            throw new InvalidArgumentException('tpEmis inválido.');
        }

        if (!in_array($data->tpEmit, [0, 1, 2, 3], true)) {
            throw new InvalidArgumentException('tpEmit inválido.');
        }

        if ($data->tpAmb === 2 && $data->destNome !== 'DCE EMITIDA EM AMBIENTE DE HOMOLOGACAO') {
            throw new InvalidArgumentException(
                'Em homologação, o nome do destinatário deve ser "DCE EMITIDA EM AMBIENTE DE HOMOLOGACAO".'
            );
        }
    }

    private function validateEmitter(DceAuthorizationData $data): void
    {
        $count = 0;

        if ($this->hasValue($data->emitCnpj)) {
            $count++;
        }

        if ($this->hasValue($data->emitCpf)) {
            $count++;
        }

        if ($this->hasValue($data->emitIdOutros)) {
            $count++;
        }

        if ($count !== 1) {
            throw new InvalidArgumentException('Emitente deve informar exatamente um entre CNPJ, CPF ou idOutros.');
        }

        if ($this->hasValue($data->emitCpf) && $data->tpEmit === 2) {
            throw new InvalidArgumentException('Emissão própria exige emitente com CNPJ.');
        }
    }

    private function validateActorGroup(DceAuthorizationData $data): void
    {
        $issuerType = DceIssuerType::from($data->tpEmit);

        $count = 0;

        if ($data->fisco !== null) {
            $count++;
        }

        if ($data->marketplace !== null) {
            $count++;
        }

        if ($data->transportadoraEmissora !== null) {
            $count++;
        }

        match ($issuerType) {
            DceIssuerType::FISCO => $this->assertGroupExists($data->fisco, 'Fisco'),
            DceIssuerType::MARKETPLACE => $this->assertGroupExists($data->marketplace, 'Marketplace'),
            DceIssuerType::OWN => null,
            DceIssuerType::CARRIER => $this->assertGroupExists(
                $data->transportadoraEmissora,
                'Transportadora'
            ),
        };

        if ($issuerType === DceIssuerType::OWN && $count !== 0) {
            throw new InvalidArgumentException(
                'Emissão própria não deve possuir grupo emissor complementar.'
            );
        }

        if (
            in_array($issuerType, [
                DceIssuerType::FISCO,
                DceIssuerType::MARKETPLACE,
                DceIssuerType::CARRIER
            ], true)
            && $count !== 1
        ) {
            throw new InvalidArgumentException(
                'Deve existir exatamente um grupo emissor complementar.'
            );
        }

        if (
            $issuerType === DceIssuerType::OWN
            && !$this->hasValue($data->emitCnpj)
        ) {
            throw new InvalidArgumentException(
                'Emissão própria exige emitente com CNPJ.'
            );
        }
    }

    private function assertGroupExists(?array $group, string $label): void
    {
        if ($group === null) {
            throw new InvalidArgumentException(
                sprintf('tpEmit exige grupo %s.', $label)
            );
        }
    }

    private function validateActorGroup1(DceAuthorizationData $data): void
    {
        $count = 0;

        if ($data->fisco !== null) {
            $count++;
        }

        if ($data->marketplace !== null) {
            $count++;
        }

        if ($data->transportadoraEmissora !== null) {
            $count++;
        }

        if ($data->emissaoPropria !== null) {
            $count++;
        }

        if ($data->tpEmit === 0 && $data->fisco === null) {
            throw new InvalidArgumentException('tpEmit=0 exige grupo Fisco.');
        }

        if ($data->tpEmit === 1 && $data->marketplace === null) {
            throw new InvalidArgumentException('tpEmit=1 exige grupo Marketplace.');
        }

//        if ($data->tpEmit === 2 && $data->emissaoPropria === null) {
//            throw new InvalidArgumentException('tpEmit=2 exige grupo EmpEmisProp.');
//        }

        if ($data->tpEmit === 3 && $data->transportadoraEmissora === null) {
            throw new InvalidArgumentException('tpEmit=3 exige grupo Transportadora.');
        }

        if ($count !== 1) {
            throw new InvalidArgumentException('Deve existir exatamente um grupo emissor complementar.');
        }

        if ($data->tpEmit === 2) {
            if (!$this->hasValue($data->emissaoPropria['cnpj'] ?? null)) {
                throw new InvalidArgumentException('Na emissão própria, o CNPJ do grupo EmpEmisProp é obrigatório.');
            }

            if (!$this->hasValue($data->emissaoPropria['xNome'] ?? null)) {
                throw new InvalidArgumentException('Na emissão própria, o xNome do grupo EmpEmisProp é obrigatório.');
            }

            if ($data->emitCnpj !== $data->emissaoPropria['cnpj']) {
                throw new InvalidArgumentException('Na emissão própria, o CNPJ do grupo EmpEmisProp deve ser igual ao CNPJ do emitente.');
            }
        }

        if ($data->tpEmit === 3) {
            if (!$this->hasValue($data->transportadoraEmissora['cnpj'] ?? null)) {
                throw new InvalidArgumentException('Na emissão por transportadora, o CNPJ da Transportadora é obrigatório.');
            }

            if (!$this->hasValue($data->transportadoraEmissora['xNome'] ?? null)) {
                throw new InvalidArgumentException('Na emissão por transportadora, o xNome da Transportadora é obrigatório.');
            }
        }
    }

    private function validateRecipient(DceAuthorizationData $data): void
    {
        $count = 0;

        if ($this->hasValue($data->destCnpj)) {
            $count++;
        }

        if ($this->hasValue($data->destCpf)) {
            $count++;
        }

        if ($this->hasValue($data->destIdOutros)) {
            $count++;
        }

        if ($count !== 1) {
            throw new InvalidArgumentException('Destinatário deve informar exatamente um entre CNPJ, CPF ou idOutros.');
        }
    }

    private function validateItems(DceAuthorizationData $data): void
    {
        if ($data->items === []) {
            throw new InvalidArgumentException('A DC-e deve possuir ao menos um item.');
        }

        foreach ($data->items as $index => $item) {
            if (trim($item->xProd) === '') {
                throw new InvalidArgumentException(sprintf('Descrição do item %d é obrigatória.', $index + 1));
            }

            if ($item->vProd > 100000) {
                throw new InvalidArgumentException(sprintf(
                    'Valor total bruto do produto acima do permitido no item %d.',
                    $index + 1
                ));
            }
        }
    }

    private function validateTotals(DceAuthorizationData $data): void
    {
        $sum = 0.0;

        foreach ($data->items as $item) {
            $sum += $item->vProd;
        }

        $sum = round($sum, 2);

        if (round($data->vDC, 2) !== $sum) {
            throw new InvalidArgumentException(sprintf(
                'Valor total da DC-e divergente. Informado: %.2f / Calculado: %.2f',
                $data->vDC,
                $sum
            ));
        }

        if ($data->vDC > 200000) {
            throw new InvalidArgumentException('Valor total da DC-e acima do permitido.');
        }
    }

    private function validateTransport(DceAuthorizationData $data): void
    {
        if (!in_array($data->modTrans, [0, 1, 2], true)) {
            throw new InvalidArgumentException('Modalidade de transporte inválida.');
        }

        if ($data->modTrans === 2 && !$this->hasValue($data->cnpjTransp)) {
            throw new InvalidArgumentException('Transporte por empresa transportadora exige CNPJ da transportadora.');
        }
    }

    private function validateQrCode(DceAuthorizationData $data): void
    {
        if (trim($data->urlChave) === '') {
            throw new InvalidArgumentException('urlChave é obrigatória.');
        }

        if (trim($data->qrCode) === '') {
            throw new InvalidArgumentException('qrCode é obrigatório.');
        }

        if (!str_contains($data->qrCode, 'chDCe=' . $data->accessKey)) {
            throw new InvalidArgumentException('QR Code não contém a chave de acesso correta.');
        }

        if (!str_contains($data->qrCode, 'tpAmb=' . $data->tpAmb)) {
            throw new InvalidArgumentException('QR Code não contém o tpAmb correto.');
        }

        if ($data->tpEmis === 1 && str_contains($data->qrCode, 'sign=')) {
            throw new InvalidArgumentException('QR Code de emissão normal não deve conter parâmetro sign.');
        }

        if ($data->tpEmis === 9 && !str_contains($data->qrCode, 'sign=')) {
            throw new InvalidArgumentException('QR Code de contingência deve conter parâmetro sign.');
        }
    }

    private function hasValue(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }
}
