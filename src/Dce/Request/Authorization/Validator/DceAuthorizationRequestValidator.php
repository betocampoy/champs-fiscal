<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Validator;

use BetoCampoy\Champs\Fiscal\Brazil\UfCodeMap;
use BetoCampoy\Champs\Fiscal\Dce\Enum\DceIssuerType;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAdditionalInfoRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAuthorizationRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAutXmlRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDestAddressRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDestRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDetRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmitAddressRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmitRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmpEmisPropRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceIdeRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceMarketplaceRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceProdRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTotalRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTranspRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTransportRequest;
use BetoCampoy\Champs\Fiscal\Validator\ValidationResult;

final class DceAuthorizationRequestValidator
{
    public function validate(DceAuthorizationRequest $request): ValidationResult
    {
        $result = new ValidationResult();

        $this->validateRoot($request, $result);
        $this->validateIde($request->getIde(), $result);
        $this->validateEmit($request->getEmit(), $result);
        $this->validateIdeEmitConsistency($request->getIde(), $request->getEmit(), $result);
        $this->validateIssuerSpecificGroup($request, $result);
        $this->validateDest($request->getDest(), $result);
        $this->validateAuthorizedXmlViewers($request->getAuthorizedXmlViewers(), $result);
        $this->validateDetails($request->getDetails(), $result);
        $this->validateTotal($request->getTotal(), $result);
        $this->validateTransport($request->getTransport(), $result);
        $this->validateAdditionalInfo($request->getAdditionalInfo(), $result);

        return $result;
    }

    private function validateRoot(DceAuthorizationRequest $request, ValidationResult $result): void
    {
        if ($request->getIde() === null) {
            $result->addError('ide', 'O grupo ide é obrigatório.');
        }

        if ($request->getEmit() === null) {
            $result->addError('emit', 'O grupo emit é obrigatório.');
        }

        if ($request->getDest() === null) {
            $result->addError('dest', 'O grupo dest é obrigatório.');
        }

        if ($request->getDetails() === []) {
            $result->addError('details', 'É obrigatório informar ao menos um item em det.');
        }

        if ($request->getTotal() === null) {
            $result->addError('total', 'O grupo total é obrigatório.');
        }

        if ($request->getTransport() === null) {
            $result->addError('transport', 'O grupo transport é obrigatório.');
        }
    }

    private function validateIde(?DceIdeRequest $ide, ValidationResult $result): void
    {
        if ($ide === null) {
            return;
        }

        $this->requireNotBlank($ide->getState(), 'ide.state', 'A UF é obrigatória.', $result);
        $this->requireNotBlank($ide->getUfCode(), 'ide.ufCode', 'O código da UF é obrigatório.', $result);
        $this->requireNotBlank($ide->getRandomCode(), 'ide.randomCode', 'O código numérico é obrigatório.', $result);
        $this->requireNotBlank($ide->getModel(), 'ide.model', 'O modelo é obrigatório.', $result);
        $this->requireNotBlank($ide->getSeries(), 'ide.series', 'A série é obrigatória.', $result);
        $this->requireNotBlank($ide->getNumber(), 'ide.number', 'O número é obrigatório.', $result);
        $this->requireNotNull($ide->getIssuedAt(), 'ide.issuedAt', 'A data/hora de emissão é obrigatória.', $result);
        $this->requireNotBlank($ide->getEmissionType(), 'ide.emissionType', 'O tipo de emissão é obrigatório.', $result);
        $this->requireNotBlank($ide->getIssuerType(), 'ide.issuerType', 'O tipo de emitente é obrigatório.', $result);
        $this->requireNotBlank($ide->getAuthorizerSiteNumber(), 'ide.authorizerSiteNumber', 'O número do site autorizador é obrigatório.', $result);
        $this->requireNotBlank($ide->getCheckDigit(), 'ide.checkDigit', 'O dígito verificador é obrigatório.', $result);
        $this->requireNotBlank($ide->getEnvironment(), 'ide.environment', 'O ambiente é obrigatório.', $result);
        $this->requireNotBlank($ide->getProcessVersion(), 'ide.processVersion', 'A versão do processo é obrigatória.', $result);

        if ($ide->getState() !== null && !UfCodeMap::isValidState($ide->getState())) {
            $result->addError('ide.state', 'A UF informada em ide é inválida.');
        }

        if (
            $ide->getState() !== null
            && $ide->getUfCode() !== null
            && UfCodeMap::isValidState($ide->getState())
            && UfCodeMap::codeFromState($ide->getState()) !== $ide->getUfCode()
        ) {
            $result->addError('ide.ufCode', 'O código da UF não corresponde à UF informada em ide.');
        }

        if ($ide->getModel() !== null && $ide->getModel() !== '99') {
            $result->addError('ide.model', 'O modelo da DC-e deve ser 99.');
        }

        if ($ide->getEnvironment() !== null && !in_array($ide->getEnvironment(), ['1', '2'], true)) {
            $result->addError('ide.environment', 'O ambiente deve ser 1 (produção) ou 2 (homologação).');
        }

        if ($ide->getEmissionType() !== null && !in_array($ide->getEmissionType(), ['1', '9'], true)) {
            $result->addError('ide.emissionType', 'O tipo de emissão deve ser 1 (normal) ou 9 (contingência offline).');
        }

        if ($ide->getIssuerType() !== null) {
            try {
                DceIssuerType::from((int) $ide->getIssuerType());
            } catch (\ValueError) {
                $result->addError('ide.issuerType', 'O tipo de emitente informado é inválido.');
            }
        }
    }

    private function validateEmit(?DceEmitRequest $emit, ValidationResult $result): void
    {
        if ($emit === null) {
            return;
        }

        $this->validateDocumentChoice(
            cnpj: $emit->getCnpj(),
            cpf: $emit->getCpf(),
            otherId: $emit->getOther(),
            baseField: 'emit',
            result: $result
        );

        $this->requireNotBlank($emit->getName(), 'emit.name', 'O nome do emitente é obrigatório.', $result);
        $this->validateEmitAddress($emit->getAddress(), $result);
    }

    private function validateEmitAddress(?DceEmitAddressRequest $address, ValidationResult $result): void
    {
        if ($address === null) {
            $result->addError('emit.address', 'O endereço do emitente é obrigatório.');
            return;
        }

        $this->requireNotBlank($address->getStreet(), 'emit.address.street', 'O logradouro do emitente é obrigatório.', $result);
        $this->requireNotBlank($address->getNumber(), 'emit.address.number', 'O número do emitente é obrigatório.', $result);
        $this->requireNotBlank($address->getNeighborhood(), 'emit.address.district', 'O bairro do emitente é obrigatório.', $result);
        $this->requireNotBlank($address->getCityCode(), 'emit.address.cityCode', 'O código do município do emitente é obrigatório.', $result);
        $this->requireNotBlank($address->getCityName(), 'emit.address.cityName', 'O município do emitente é obrigatório.', $result);
        $this->requireNotBlank($address->getState(), 'emit.address.state', 'A UF do emitente é obrigatória.', $result);
        $this->requireNotBlank($address->getZipCode(), 'emit.address.zipCode', 'O CEP do emitente é obrigatório.', $result);
        $this->requireNotBlank($address->getCountryCode(), 'emit.address.countryCode', 'O código do país do emitente é obrigatório.', $result);
        $this->requireNotBlank($address->getCountryName(), 'emit.address.countryName', 'O nome do país do emitente é obrigatório.', $result);

        if ($address->getState() !== null && !UfCodeMap::isValidState($address->getState())) {
            $result->addError('emit.address.state', 'A UF do emitente é inválida.');
        }
    }

    private function validateIdeEmitConsistency(
        ?DceIdeRequest $ide,
        ?DceEmitRequest $emit,
        ValidationResult $result
    ): void {
        $ideState = $ide?->getState();
        $emitState = $emit?->getAddress()?->getState();

        if ($ideState === null || $emitState === null) {
            return;
        }

        if ($ideState !== $emitState) {
            $result->addError('ide.state', 'A UF informada em ide deve ser a mesma UF do endereço do emitente.');
        }
    }

    private function validateIssuerSpecificGroup(
        DceAuthorizationRequest $request,
        ValidationResult $result
    ): void {
        $rawIssuerType = $request->getIde()?->getIssuerType();

        if ($rawIssuerType === null || $rawIssuerType === '') {
            return;
        }

        try {
            $issuerType = DceIssuerType::from((int) $rawIssuerType);
        } catch (\ValueError) {
            return;
        }

        $marketplace = $request->getMarketplace();
        $empEmisProp = $request->getEmpEmisProp();
        $transp = $request->getTransp();

        match ($issuerType) {
            DceIssuerType::FISCO => $this->validateFiscoIssuerGroups($marketplace, $empEmisProp, $transp, $result),
            DceIssuerType::MARKETPLACE => $this->validateMarketplaceIssuerGroups($marketplace, $empEmisProp, $transp, $result),
            DceIssuerType::OWN => $this->validateEmpEmisPropIssuerGroups($marketplace, $empEmisProp, $transp, $result),
            DceIssuerType::CARRIER => $this->validateTransportadoraIssuerGroups($marketplace, $empEmisProp, $transp, $result),
        };
    }

    private function validateFiscoIssuerGroups(
        ?DceMarketplaceRequest $marketplace,
        ?DceEmpEmisPropRequest $empEmisProp,
        ?DceTranspRequest $transp,
        ValidationResult $result
    ): void {
        if ($marketplace !== null) {
            $result->addError('marketplace', 'O grupo Marketplace não pode ser informado quando o tipo de emitente for Fisco.');
        }

        if ($empEmisProp !== null) {
            $result->addError('empEmisProp', 'O grupo EmpEmisProp não pode ser informado quando o tipo de emitente for Fisco.');
        }

        if ($transp !== null) {
            $result->addError('transp', 'O grupo Transportadora não pode ser informado quando o tipo de emitente for Fisco.');
        }
    }

    private function validateMarketplaceIssuerGroups(
        ?DceMarketplaceRequest $marketplace,
        ?DceEmpEmisPropRequest $empEmisProp,
        ?DceTranspRequest $transp,
        ValidationResult $result
    ): void {
        if ($marketplace === null) {
            $result->addError('marketplace', 'Quando o tipo de emitente for Marketplace, o grupo Marketplace é obrigatório.');
        } else {
            $this->validateMarketplace($marketplace, $result);
        }

        if ($empEmisProp !== null) {
            $result->addError('empEmisProp', 'O grupo EmpEmisProp só pode ser informado quando o tipo de emitente for Emissor Próprio.');
        }

        if ($transp !== null) {
            $result->addError('transp', 'O grupo Transportadora só pode ser informado quando o tipo de emitente for Transportadora.');
        }
    }

    private function validateEmpEmisPropIssuerGroups(
        ?DceMarketplaceRequest $marketplace,
        ?DceEmpEmisPropRequest $empEmisProp,
        ?DceTranspRequest $transp,
        ValidationResult $result
    ): void {
        if ($marketplace !== null) {
            $result->addError('marketplace', 'O grupo Marketplace só pode ser informado quando o tipo de emitente for Marketplace.');
        }

        if ($empEmisProp !== null) {
            $result->addError('empEmisProp', 'O grupo EmpEmisProp não deve ser informado no XML da DC-e para Emissor Próprio.');
        }

        if ($transp !== null) {
            $result->addError('transp', 'O grupo Transportadora só pode ser informado quando o tipo de emitente for Transportadora.');
        }
    }

//    private function validateEmpEmisPropIssuerGroups(
//        ?DceMarketplaceRequest $marketplace,
//        ?DceEmpEmisPropRequest $empEmisProp,
//        ?DceTranspRequest $transp,
//        ValidationResult $result
//    ): void {
//        if ($empEmisProp === null) {
//            $result->addError('empEmisProp', 'Quando o tipo de emitente for Emissor Próprio, o grupo EmpEmisProp é obrigatório.');
//        } else {
//            $this->validateEmpEmisProp($empEmisProp, $result);
//        }
//
//        if ($marketplace !== null) {
//            $result->addError('marketplace', 'O grupo Marketplace só pode ser informado quando o tipo de emitente for Marketplace.');
//        }
//
//        if ($transp !== null) {
//            $result->addError('transp', 'O grupo Transportadora só pode ser informado quando o tipo de emitente for Transportadora.');
//        }
//    }

    private function validateTransportadoraIssuerGroups(
        ?DceMarketplaceRequest $marketplace,
        ?DceEmpEmisPropRequest $empEmisProp,
        ?DceTranspRequest $transp,
        ValidationResult $result
    ): void {
        if ($transp === null) {
            $result->addError('transp', 'Quando o tipo de emitente for Transportadora, o grupo Transportadora é obrigatório.');
        } else {
            $this->validateTransp($transp, $result);
        }

        if ($marketplace !== null) {
            $result->addError('marketplace', 'O grupo Marketplace só pode ser informado quando o tipo de emitente for Marketplace.');
        }

        if ($empEmisProp !== null) {
            $result->addError('empEmisProp', 'O grupo EmpEmisProp só pode ser informado quando o tipo de emitente for Emissor Próprio.');
        }
    }

    private function validateMarketplace(DceMarketplaceRequest $marketplace, ValidationResult $result): void
    {
        $this->requireNotBlank($marketplace->getCnpj(), 'marketplace.cnpj', 'O CNPJ do Marketplace é obrigatório.', $result);
        $this->requireNotBlank($marketplace->getName(), 'marketplace.name', 'O nome do Marketplace é obrigatório.', $result);
        $this->requireNotBlank($marketplace->getSite(), 'marketplace.site', 'O site do Marketplace é obrigatório.', $result);
    }

//    private function validateEmpEmisProp(DceEmpEmisPropRequest $empEmisProp, ValidationResult $result): void
//    {
//        $this->requireNotBlank($empEmisProp->getCnpj(), 'empEmisProp.cnpj', 'O CNPJ da empresa de emissão própria é obrigatório.', $result);
//        $this->requireNotBlank($empEmisProp->getName(), 'empEmisProp.name', 'O nome da empresa de emissão própria é obrigatório.', $result);
//    }

    private function validateTransp(?DceTranspRequest $transp, ValidationResult $result): void
    {
        if ($transp === null) {
            return;
        }

        $filled = 0;

        if ($this->hasText($transp->getCnpj())) {
            $filled++;
        }

        if ($this->hasText($transp->getCpf())) {
            $filled++;
        }

        if ($filled === 0) {
            $result->addError('transp.document', 'Informe CNPJ ou CPF da transportadora.');
        }

        if ($filled > 1) {
            $result->addError('transp.document', 'Informe apenas um documento da transportadora: CNPJ ou CPF.');
        }

        $this->requireNotBlank($transp->getName(), 'transp.name', 'O nome da transportadora é obrigatório.', $result);
    }

    private function validateDest(?DceDestRequest $dest, ValidationResult $result): void
    {
        if ($dest === null) {
            return;
        }

        $this->validateDocumentChoice(
            cnpj: $dest->getCnpj(),
            cpf: $dest->getCpf(),
            otherId: $dest->getOtherId(),
            baseField: 'dest',
            result: $result
        );

        $this->requireNotBlank($dest->getName(), 'dest.name', 'O nome do destinatário é obrigatório.', $result);
        $this->validateDestAddress($dest->getAddress(), $result);
    }

    private function validateDestAddress(?DceDestAddressRequest $address, ValidationResult $result): void
    {
        if ($address === null) {
            $result->addError('dest.address', 'O endereço do destinatário é obrigatório.');
            return;
        }

        $this->requireNotBlank($address->getStreet(), 'dest.address.street', 'O logradouro do destinatário é obrigatório.', $result);
        $this->requireNotBlank($address->getNumber(), 'dest.address.number', 'O número do destinatário é obrigatório.', $result);
        $this->requireNotBlank($address->getNeighborhood(), 'dest.address.district', 'O bairro do destinatário é obrigatório.', $result);
        $this->requireNotBlank($address->getCityCode(), 'dest.address.cityCode', 'O código do município do destinatário é obrigatório.', $result);
        $this->requireNotBlank($address->getCityName(), 'dest.address.cityName', 'O município do destinatário é obrigatório.', $result);
        $this->requireNotBlank($address->getState(), 'dest.address.state', 'A UF do destinatário é obrigatória.', $result);
        $this->requireNotBlank($address->getZipCode(), 'dest.address.zipCode', 'O CEP do destinatário é obrigatório.', $result);
        $this->requireNotBlank($address->getCountryCode(), 'dest.address.countryCode', 'O código do país do destinatário é obrigatório.', $result);
        $this->requireNotBlank($address->getCountryName(), 'dest.address.countryName', 'O nome do país do destinatário é obrigatório.', $result);

        if ($address->getState() !== null && !UfCodeMap::isValidState($address->getState())) {
            $result->addError('dest.address.state', 'A UF do destinatário é inválida.');
        }
    }

    /**
     * @param DceAutXmlRequest[] $authorizedXmlViewers
     */
    private function validateAuthorizedXmlViewers(array $authorizedXmlViewers, ValidationResult $result): void
    {
        if (count($authorizedXmlViewers) > 10) {
            $result->addError('authorizedXmlViewers', 'O grupo autXML permite no máximo 10 ocorrências.');
        }

        foreach ($authorizedXmlViewers as $index => $viewer) {
            $filled = 0;

            if ($this->hasText($viewer->getCnpj())) {
                $filled++;
            }

            if ($this->hasText($viewer->getCpf())) {
                $filled++;
            }

            if ($filled === 0) {
                $result->addError("authorizedXmlViewers[$index].document", 'Informe CNPJ ou CPF do autorizado ao XML.');
            }

            if ($filled > 1) {
                $result->addError("authorizedXmlViewers[$index].document", 'Informe apenas um documento do autorizado ao XML.');
            }
        }
    }

    /**
     * @param DceDetRequest[] $details
     */
    private function validateDetails(array $details, ValidationResult $result): void
    {
        foreach ($details as $index => $detail) {
            $this->validateDetail($detail, $index, $result);
        }
    }

    private function validateDetail(DceDetRequest $detail, int $index, ValidationResult $result): void
    {
        $base = "details[$index]";

        $this->requireNotBlank($detail->getItemNumber(), "$base.itemNumber", 'O número do item é obrigatório.', $result);

        if ($detail->getProd() === null) {
            $result->addError("$base.prod", 'O grupo prod é obrigatório.');
            return;
        }

        $this->validateProd($detail->getProd(), $base, $result);
    }

    private function validateProd(DceProdRequest $prod, string $base, ValidationResult $result): void
    {
        $this->requireNotBlank($prod->getName(), "$base.prod.name", 'A descrição do produto é obrigatória.', $result);
        $this->requireNotBlank($prod->getCommercialQuantity(), "$base.prod.commercialQuantity", 'A quantidade comercial é obrigatória.', $result);
        $this->requireNotBlank($prod->getUnitValue(), "$base.prod.unitValue", 'O valor unitário é obrigatório.', $result);
        $this->requireNotBlank($prod->getTotalValue(), "$base.prod.totalValue", 'O valor total do item é obrigatório.', $result);
    }

    private function validateTotal(?DceTotalRequest $total, ValidationResult $result): void
    {
        if ($total === null) {
            return;
        }

        $this->requireNotBlank($total->getTotalValue(), 'total.totalValue', 'O valor total da DC-e é obrigatório.', $result);
    }

    private function validateTransport(?DceTransportRequest $transport, ValidationResult $result): void
    {
        if ($transport === null) {
            return;
        }
    }

    private function validateAdditionalInfo(?DceAdditionalInfoRequest $additionalInfo, ValidationResult $result): void
    {
        if ($additionalInfo === null) {
            return;
        }

        $info = $additionalInfo->getComplementaryInfo();

        if ($info !== null && mb_strlen($info) > 5000) {
            $result->addError('additionalInfo.complementaryInfo', 'As informações complementares devem ter no máximo 5000 caracteres.');
        }
    }

    private function validateDocumentChoice(
        ?string $cnpj,
        ?string $cpf,
        ?string $otherId,
        string $baseField,
        ValidationResult $result
    ): void {
        $filled = 0;

        if ($this->hasText($cnpj)) {
            $filled++;
        }

        if ($this->hasText($cpf)) {
            $filled++;
        }

        if ($this->hasText($otherId)) {
            $filled++;
        }

        if ($filled === 0) {
            $result->addError("$baseField.document", 'Informe um documento válido: CNPJ, CPF ou identificação alternativa.');
        }

        if ($filled > 1) {
            $result->addError("$baseField.document", 'Informe apenas um documento: CNPJ, CPF ou identificação alternativa.');
        }
    }

    private function requireNotBlank(?string $value, string $field, string $message, ValidationResult $result): void
    {
        if (!$this->hasText($value)) {
            $result->addError($field, $message);
        }
    }

    private function requireNotNull(mixed $value, string $field, string $message, ValidationResult $result): void
    {
        if ($value === null) {
            $result->addError($field, $message);
        }
    }

    private function hasText(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }
}
