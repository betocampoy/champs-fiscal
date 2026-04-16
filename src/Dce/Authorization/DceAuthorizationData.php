<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Authorization;

use DateTimeImmutable;

final class DceAuthorizationData
{
    /**
     * @param DceAuthorizationItem[] $items
     * @param array<int, array{document:string,type:string}> $autXml
     */
    public function __construct(
        // 📄 Identificação
        public readonly string $versao,
        public readonly int $cUF,
        public readonly string $cDC,
        public readonly int $mod,
        public readonly int $serie,
        public readonly int $nDC,
        public readonly DateTimeImmutable $dhEmi,
        public readonly int $tpEmis,
        public readonly int $tpEmit,
        public readonly int $nSiteAutoriz,
        public readonly int $tpAmb,
        public readonly string $verProc,

        // 🏢 Emitente
        public readonly ?string $emitCnpj,
        public readonly ?string $emitCpf,
        public readonly ?string $emitIdOutros,
        public readonly string $emitNome,
        public readonly array $emitEndereco,

        // 🧩 Tipo de emissão (grupos condicionais)
        public readonly ?array $fisco,
        public readonly ?array $marketplace,
        public readonly ?array $transportadoraEmissora,
        public readonly ?array $emissaoPropria,

        // 👤 Destinatário
        public readonly ?string $destCnpj,
        public readonly ?string $destCpf,
        public readonly ?string $destIdOutros,
        public readonly ?string $destNome,
        public readonly array $destEndereco,
        public readonly ?string $destEmail,

        // 🔐 Autorizados XML
        public readonly array $autXml,

        // 📦 Itens
        public readonly array $items,

        // 💰 Total
        public readonly float $vDC,

        // 🚚 Transporte
        public readonly int $modTrans,
        public readonly ?string $cnpjTransp,

        // 📝 Informações adicionais
        public readonly ?string $infAdFisco,
        public readonly ?string $infCpl,
        public readonly ?string $infAdMarketplace,
        public readonly ?string $infAdTransp,

        // 🧾 Observações
        public readonly string $xObs1,
        public readonly string $xObs2,

        // 📬 Solicitação
        public readonly ?string $xSolic,

        // 🔗 Suplementar
        public readonly string $qrCode,
        public readonly string $urlChave,

        // 🔑 Chave de acesso (IMPORTANTE para assinatura)
        public readonly string $accessKey,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // 🔒 Garantir que existe documento do emitente
        if (!$this->emitCnpj && !$this->emitCpf && !$this->emitIdOutros) {
            throw new \InvalidArgumentException('Emitente deve possuir CNPJ, CPF ou IdOutros.');
        }

        // 🔒 Garantir que existe documento do destinatário
        if (!$this->destCnpj && !$this->destCpf && !$this->destIdOutros) {
            throw new \InvalidArgumentException('Destinatário deve possuir CNPJ, CPF ou IdOutros.');
        }

        // 🔒 Itens obrigatórios
        if (empty($this->items)) {
            throw new \InvalidArgumentException('DC-e deve possuir ao menos um item.');
        }

        // 🔒 Total válido
        if ($this->vDC < 0) {
            throw new \InvalidArgumentException('Valor total inválido.');
        }

        // 🔒 Chave obrigatória
        if (empty($this->accessKey)) {
            throw new \InvalidArgumentException('Chave de acesso é obrigatória.');
        }
    }

    public function getSignatureReferenceId(): string
    {
        return 'DCe' . $this->accessKey;
    }
}
