<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Event\Validator;

use BetoCampoy\Champs\Fiscal\Dce\Request\Event\Input\DceEventRequest;
use BetoCampoy\Champs\Fiscal\Validator\ValidationResult;

final class DceEventRequestValidator
{
    public function validate(DceEventRequest $request): ValidationResult
    {
        $result = new ValidationResult();

        $this->validateAccessKey($request->getAccessKey(), $result);
        $this->validateEnvironment($request->getEnvironment(), $result);
        $this->validateVersion($request->getVersion(), $result);
        $this->validateService($request->getService(), $result);
        $this->validateEventVersion($request->getEventVersion(), $result);
        $this->validateEventType($request->getEventType(), $result);
        $this->validateSequence($request->getSequence(), $result);
        $this->validateEventDate($request->getEventDate(), $result);
        $this->validateAuthorDocument($request->getAuthorDocument(), $result);
        $this->validateJustification($request->getJustification(), $result);

        return $result;
    }

    private function validateAccessKey(?string $accessKey, ValidationResult $result): void
    {
        if (!$this->hasText($accessKey)) {
            $result->addError('accessKey', 'A chave de acesso é obrigatória.');
            return;
        }

        if (!preg_match('/^\d{44}$/', $accessKey)) {
            $result->addError('accessKey', 'A chave de acesso deve conter 44 dígitos.');
        }
    }

    private function validateEnvironment(?string $environment, ValidationResult $result): void
    {
        if (!$this->hasText($environment)) {
            $result->addError('environment', 'O ambiente é obrigatório.');
            return;
        }

        if (!in_array($environment, ['1', '2'], true)) {
            $result->addError('environment', 'O ambiente deve ser 1 (produção) ou 2 (homologação).');
        }
    }

    private function validateVersion(?string $version, ValidationResult $result): void
    {
        if (!$this->hasText($version)) {
            $result->addError('version', 'A versão é obrigatória.');
            return;
        }

        if ($version !== '1.00') {
            $result->addError('version', 'A versão do evento deve ser 1.00.');
        }
    }

    private function validateService(?string $service, ValidationResult $result): void
    {
        if (!$this->hasText($service)) {
            $result->addError('service', 'O serviço é obrigatório.');
            return;
        }

        if ($service !== 'RECEPCIONAR_EVENTO') {
            $result->addError('service', 'O serviço do evento deve ser RECEPCIONAR_EVENTO.');
        }
    }

    private function validateEventVersion(?string $eventVersion, ValidationResult $result): void
    {
        if (!$this->hasText($eventVersion)) {
            $result->addError('eventVersion', 'A versão do evento é obrigatória.');
            return;
        }

        if ($eventVersion !== '1.00') {
            $result->addError('eventVersion', 'A versão do detalhe do evento deve ser 1.00.');
        }
    }

    private function validateEventType(?string $eventType, ValidationResult $result): void
    {
        if (!$this->hasText($eventType)) {
            $result->addError('eventType', 'O tipo do evento é obrigatório.');
            return;
        }

        if (!preg_match('/^\d+$/', $eventType)) {
            $result->addError('eventType', 'O tipo do evento deve conter apenas dígitos.');
        }
    }

    private function validateSequence(?string $sequence, ValidationResult $result): void
    {
        if (!$this->hasText($sequence)) {
            $result->addError('sequence', 'A sequência do evento é obrigatória.');
            return;
        }

        if (!preg_match('/^\d+$/', $sequence)) {
            $result->addError('sequence', 'A sequência do evento deve conter apenas dígitos.');
            return;
        }

        if ((int) $sequence < 1) {
            $result->addError('sequence', 'A sequência do evento deve ser maior ou igual a 1.');
        }
    }

    private function validateEventDate(?string $eventDate, ValidationResult $result): void
    {
        if (!$this->hasText($eventDate)) {
            $result->addError('eventDate', 'A data/hora do evento é obrigatória.');
            return;
        }

        try {
            new \DateTimeImmutable($eventDate);
        } catch (\Throwable) {
            $result->addError('eventDate', 'A data/hora do evento é inválida.');
        }
    }

    private function validateAuthorDocument(?string $authorDocument, ValidationResult $result): void
    {
        if (!$this->hasText($authorDocument)) {
            $result->addError('authorDocument', 'O documento do autor do evento é obrigatório.');
            return;
        }

        if (!preg_match('/^\d{11}$|^\d{14}$/', $authorDocument)) {
            $result->addError('authorDocument', 'O documento do autor do evento deve conter 11 ou 14 dígitos.');
        }
    }

    private function validateJustification(?string $justification, ValidationResult $result): void
    {
        if (!$this->hasText($justification)) {
            $result->addError('justification', 'A justificativa do evento é obrigatória.');
            return;
        }

        $length = mb_strlen(trim($justification));

        if ($length < 15) {
            $result->addError('justification', 'A justificativa do evento deve ter no mínimo 15 caracteres.');
        }

        if ($length > 255) {
            $result->addError('justification', 'A justificativa do evento deve ter no máximo 255 caracteres.');
        }
    }

    private function hasText(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }
}
