<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Event\Normalizer;

use BetoCampoy\Champs\Fiscal\Dce\Request\Event\Input\DceEventRequest;

final class DceEventRequestNormalizer
{
    public function normalize(DceEventRequest $request): DceEventRequest
    {
        $this->applyInternalDefaults($request);
        $this->normalizeAuthorDocument($request);

        return $request;
    }

    private function applyInternalDefaults(DceEventRequest $request): void
    {
        $defaults = $this->getInternalDefaults();

        if (!$request->getVersion()) {
            $request->setVersion($defaults['version']);
        }

        if (!$request->getService()) {
            $request->setService($defaults['service']);
        }

        if (!$request->getEventVersion()) {
            $request->setEventVersion($defaults['eventVersion']);
        }
    }

    private function normalizeAuthorDocument(DceEventRequest $request): void
    {
        if ($request->getAuthorDocument()) {
            return;
        }

        if ($request->getIssuerCnpj()) {
            $request->setAuthorDocument($request->getIssuerCnpj());
            return;
        }

        if ($request->getIssuerCpf()) {
            $request->setAuthorDocument($request->getIssuerCpf());
            return;
        }

        if ($request->getIssuerOtherId()) {
            $request->setAuthorDocument($request->getIssuerOtherId());
        }
    }

    private function getInternalDefaults(): array
    {
        return [
            'version' => '1.00',
            'service' => 'RECEPCIONAR_EVENTO',
            'eventVersion' => '1.00',
        ];
    }
}
