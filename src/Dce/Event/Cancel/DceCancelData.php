<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Event\Cancel;

use InvalidArgumentException;

final class DceCancelData
{
    public function __construct(
        public readonly string $versao,
        public readonly string $cOrgao,
        public readonly int $tpAmb,
        public readonly int $tpEmit,
        public readonly string $cnpjAutor,
        public readonly ?string $cnpjUsEmit,
        public readonly ?string $cpfUsEmit,
        public readonly ?string $idOutrosUsEmit,
        public readonly string $chDCe,
        public readonly string $dhEvento,
        public readonly string $tpEvento,
        public readonly string $nSeqEvento,
        public readonly string $versaoEvento,
        public readonly string $descEvento,
        public readonly string $nProt,
        public readonly string $xJust,
    ) {
        $this->validate();
    }

    public function getEventId(): string
    {
        return 'ID' . $this->tpEvento . $this->chDCe . str_pad($this->nSeqEvento, 3, '0', STR_PAD_LEFT);
    }

    private function validate(): void
    {
        if (trim($this->versao) === '') {
            throw new InvalidArgumentException('Versão do evento é obrigatória.');
        }

        if (!preg_match('/^\d{2}$/', $this->cOrgao)) {
            throw new InvalidArgumentException('cOrgao deve conter 2 dígitos.');
        }

        if (!preg_match('/^\d{44}$/', $this->chDCe)) {
            throw new InvalidArgumentException('chDCe deve conter exatamente 44 dígitos.');
        }

        if ($this->cOrgao !== substr($this->chDCe, 0, 2)) {
            throw new InvalidArgumentException(
                'cOrgao deve ser igual à UF da chave de acesso'
            );
        }

        if (!in_array($this->tpAmb, [1, 2], true)) {
            throw new InvalidArgumentException('tpAmb inválido. Valores aceitos: 1 ou 2.');
        }

        if (!in_array($this->tpEmit, [0, 1, 2, 3], true)) {
            throw new InvalidArgumentException('tpEmit inválido. Valores aceitos: 0, 1, 2 ou 3.');
        }

        if (!preg_match('/^\d{14}$/', $this->cnpjAutor)) {
            throw new InvalidArgumentException('CNPJAutor deve conter exatamente 14 dígitos.');
        }

        $emitIdentifiers = array_filter([
            $this->cnpjUsEmit,
            $this->cpfUsEmit,
            $this->idOutrosUsEmit,
        ], static fn ($value) => $value !== null && trim($value) !== '');

        if (count($emitIdentifiers) !== 1) {
            throw new InvalidArgumentException('Informe exatamente um entre CNPJUsEmit, CPFUsEmit ou IdOutrosUsEmit.');
        }

        if ($this->cnpjUsEmit !== null && !preg_match('/^\d{14}$/', $this->cnpjUsEmit)) {
            throw new InvalidArgumentException('CNPJUsEmit deve conter exatamente 14 dígitos.');
        }

        if ($this->cpfUsEmit !== null && !preg_match('/^\d{11}$/', $this->cpfUsEmit)) {
            throw new InvalidArgumentException('CPFUsEmit deve conter exatamente 11 dígitos.');
        }

        if (trim($this->dhEvento) === '') {
            throw new InvalidArgumentException('dhEvento é obrigatório.');
        }

        if (!preg_match('/^\d{6}$/', $this->tpEvento)) {
            throw new InvalidArgumentException('tpEvento deve conter 6 dígitos.');
        }

        if (!preg_match('/^\d{1,3}$/', $this->nSeqEvento)) {
            throw new InvalidArgumentException('nSeqEvento deve conter de 1 a 3 dígitos.');
        }

        if (trim($this->versaoEvento) === '') {
            throw new InvalidArgumentException('versaoEvento é obrigatória.');
        }

        if (trim($this->descEvento) === '') {
            throw new InvalidArgumentException('descEvento é obrigatório.');
        }

        if (!preg_match('/^\d{16}$/', $this->nProt)) {
            throw new InvalidArgumentException('nProt deve conter exatamente 16 dígitos.');
        }

        if (trim($this->xJust) === '') {
            throw new InvalidArgumentException('xJust é obrigatório.');
        }
    }

    public function getSignatureReferenceId(): string
    {
        return $this->getEventId();
    }
}
