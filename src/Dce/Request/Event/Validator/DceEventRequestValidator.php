<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Event\Validator;

use BetoCampoy\Champs\Fiscal\Dce\Request\Event\Input\DceEventRequest;
use InvalidArgumentException;

final class DceEventRequestValidator
{
    public function validate(DceEventRequest $request): void
    {
        $this->validateAccessKey($request->getAccessKey());
        $this->validateEnvironment($request->getEnvironment());
        $this->validateVersion($request->getVersion());
        $this->validateService($request->getService());
        $this->validateEventVersion($request->getEventVersion());
        $this->validateEventType($request->getEventType());
        $this->validateSequence($request->getSequence());
        $this->validateEventDate($request->getEventDate());
        $this->validateAuthorDocument($request->getAuthorDocument());
        $this->validateJustification($request->getJustification());
    }

    private function validateAccessKey(?string $accessKey): void
    {
        if ($accessKey === null || $accessKey === '') {
            throw new InvalidArgumentException('A chave de acesso é obrigatória.');
        }

        if (!preg_match('/^\d{44}$/', $accessKey)) {
            throw new InvalidArgumentException('A chave de acesso deve conter 44 dígitos.');
        }
    }

    private function validateEnvironment(?string $environment): void
    {
        if ($environment === null || $environment === '') {
            throw new InvalidArgumentException('O ambiente é obrigatório.');
        }

        if (!in_array($environment, ['1', '2'], true)) {
            throw new InvalidArgumentException('O ambiente deve ser 1 (produção) ou 2 (homologação).');
        }
    }

    private function validateVersion(?string $version): void
    {
        if ($version === null || $version === '') {
            throw new InvalidArgumentException('A versão é obrigatória.');
        }

        if ($version !== '1.00') {
            throw new InvalidArgumentException('A versão do evento deve ser 1.00.');
        }
    }

    private function validateService(?string $service): void
    {
        if ($service === null || $service === '') {
            throw new InvalidArgumentException('O serviço é obrigatório.');
        }

        if ($service !== 'RECEPCIONAR_EVENTO') {
            throw new InvalidArgumentException('O serviço do evento deve ser RECEPCIONAR_EVENTO.');
        }
    }

    private function validateEventVersion(?string $eventVersion): void
    {
        if ($eventVersion === null || $eventVersion === '') {
            throw new InvalidArgumentException('A versão do evento é obrigatória.');
        }

        if ($eventVersion !== '1.00') {
            throw new InvalidArgumentException('A versão do detalhe do evento deve ser 1.00.');
        }
    }

    private function validateEventType(?string $eventType): void
    {
        if ($eventType === null || $eventType === '') {
            throw new InvalidArgumentException('O tipo do evento é obrigatório.');
        }

        if (!preg_match('/^\d+$/', $eventType)) {
            throw new InvalidArgumentException('O tipo do evento deve conter apenas dígitos.');
        }
    }

    private function validateSequence(?string $sequence): void
    {
        if ($sequence === null || $sequence === '') {
            throw new InvalidArgumentException('A sequência do evento é obrigatória.');
        }

        if (!preg_match('/^\d+$/', $sequence)) {
            throw new InvalidArgumentException('A sequência do evento deve conter apenas dígitos.');
        }

        if ((int) $sequence < 1) {
            throw new InvalidArgumentException('A sequência do evento deve ser maior ou igual a 1.');
        }
    }

    private function validateEventDate(?string $eventDate): void
    {
        if ($eventDate === null || $eventDate === '') {
            throw new InvalidArgumentException('A data/hora do evento é obrigatória.');
        }

        try {
            new \DateTimeImmutable($eventDate);
        } catch (\Throwable) {
            throw new InvalidArgumentException('A data/hora do evento é inválida.');
        }
    }

    private function validateAuthorDocument(?string $authorDocument): void
    {
        if ($authorDocument === null || $authorDocument === '') {
            throw new InvalidArgumentException('O documento do autor do evento é obrigatório.');
        }

        if (!preg_match('/^\d{11}$|^\d{14}$/', $authorDocument)) {
            throw new InvalidArgumentException('O documento do autor do evento deve conter 11 ou 14 dígitos.');
        }
    }

    private function validateJustification(?string $justification): void
    {
        if ($justification === null || $justification === '') {
            throw new InvalidArgumentException('A justificativa do evento é obrigatória.');
        }

        $length = mb_strlen($justification);

        if ($length < 15) {
            throw new InvalidArgumentException('A justificativa do evento deve ter no mínimo 15 caracteres.');
        }

        if ($length > 255) {
            throw new InvalidArgumentException('A justificativa do evento deve ter no máximo 255 caracteres.');
        }
    }
}
