<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Event\Builder;

use BetoCampoy\Champs\Fiscal\Dce\Request\Event\Input\DceEventRequest;

final class DceEventPayloadBuilder
{
    public function build(DceEventRequest $request): DceEventPayload
    {
        $payload = [
            // 🔧 Envelope
            'accessKey' => $request->getAccessKey(),
            'environment' => $request->getEnvironment(),
            'version' => $request->getVersion(),
            'service' => $request->getService(),

            // 🔧 Evento
            'eventVersion' => $request->getEventVersion(),
            'eventType' => $request->getEventType(),
            'sequence' => $request->getSequence(),
            'eventDate' => $request->getEventDate(),

            // 👤 Autor
            'authorDocument' => $request->getAuthorDocument(),

            // 📝 Evento
            'justification' => $request->getJustification(),
            'descEvento' => $request->getEventDescription(),

            // 🏛️ Identificação
            'cOrgao' => $request->getCOrgao(),
            'tpEmit' => $request->getTpEmit(),

            // 📄 Protocolo
            'nProt' => $request->getProtocolNumber(),

            // 🏢 Emitente (usado no XML dependendo do tpEmit)
            'cnpjUsEmit' => $request->getEmitCnpj(),
            'cpfUsEmit' => $request->getEmitCpf(),
            'idOutrosUsEmit' => $request->getEmitOtherId(),
        ];

        return new DceEventPayload($this->filterNulls($payload));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterNulls(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterNulls($value);
            }
        }

        return array_filter(
            $data,
            static fn ($value) => $value !== null && $value !== ''
        );
    }
}
