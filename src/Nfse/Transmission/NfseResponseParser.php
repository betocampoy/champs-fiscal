<?php

namespace BetoCampoy\Champs\Fiscal\Nfse\Transmission;

use BetoCampoy\Champs\Fiscal\Transmission\Transport\HttpTransportResponse;

final class NfseResponseParser
{
    public function parse(HttpTransportResponse $response): array
    {
        $data = $response->toArray();

        $nfse = $data['nfse']['infNFSe'] ?? [];

        return [
            'http_status'    => $response->getStatusCode(),
            'http_headers'   => $response->getHeaders(),
            'success'        => $response->isSuccess(),
            'raw'            => $response->getBody(),
            'access_key'     => $data['chaveAcesso'] ?? $nfse['chNFSe'] ?? $data['chNFSe'] ?? null,
            'number'         => $data['numero'] ?? $nfse['nNFSe'] ?? null,
            'issue_date'     => $data['dhEmi'] ?? $nfse['dhEmi'] ?? null,
            'dps_id'         => $data['idDps'] ?? null,
            'nfse_xml_b64'   => $data['nfseXmlGZipB64'] ?? null,
            'warnings'       => $data['alertas'] ?? [],
            'errors'         => $data['erros'] ?? $data['mensagens'] ?? [],
            'status_code'    => $data['cStat'] ?? null,
            'status_msg'     => $data['xMotivo'] ?? ($data['erros'][0]['xMotivo'] ?? null),
            'payload'        => $data,
        ];
    }
}
