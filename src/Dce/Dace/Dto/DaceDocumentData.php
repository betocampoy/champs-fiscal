<?php

namespace BetoCampoy\Champs\Fiscal\Dce\Dace\Dto;

final class DaceDocumentData
{
    public function __construct(
        private readonly string $number,
        private readonly string $numberFormatted,
        private readonly string $series,
        private readonly string $issueDateTime, // já formatado (DD-MM-AAAA HH:MM:SS)

        private readonly string $transportModeLabel,

        private readonly string $authorizationProtocol,

        private readonly string $accessKey,
        private readonly string $accessKeyFormatted, // 11 blocos de 4

        private readonly string $totalAmountFormatted,

        private readonly bool $isHomologation,
        private readonly bool $isContingency,

        private readonly ?string $contingencyMessage,

        private readonly ?string $sheetLabel // ex: "FOLHA 01/01"
    ) {
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getNumberFormatted(): string
    {
        return $this->numberFormatted;
    }

    public function getSeries(): string
    {
        return $this->series;
    }

    public function getIssueDateTime(): string
    {
        return $this->issueDateTime;
    }

    public function getTransportModeLabel(): string
    {
        return $this->transportModeLabel;
    }

    public function getAuthorizationProtocol(): string
    {
        return $this->authorizationProtocol;
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function getAccessKeyFormatted(): string
    {
        return $this->accessKeyFormatted;
    }

    public function getTotalAmountFormatted(): string
    {
        return $this->totalAmountFormatted;
    }

    public function isHomologation(): bool
    {
        return $this->isHomologation;
    }

    public function isContingency(): bool
    {
        return $this->isContingency;
    }

    public function getContingencyMessage(): ?string
    {
        return $this->contingencyMessage;
    }

    public function getSheetLabel(): ?string
    {
        return $this->sheetLabel;
    }

    public function hasContingencyMessage(): bool
    {
        return $this->contingencyMessage !== null && $this->contingencyMessage !== '';
    }
}
