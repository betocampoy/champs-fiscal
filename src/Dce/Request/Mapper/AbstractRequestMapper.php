<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Mapper;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

abstract class AbstractRequestMapper implements RequestMapperInterface
{
    final protected function doMap(object $source): mixed
    {
        if (!$this->supports($source)) {
            throw new InvalidArgumentException(sprintf(
                'Mapper %s não suporta objetos do tipo %s.',
                static::class,
                $source::class
            ));
        }

        return $this->mapInternal($source);
    }

    abstract protected function mapInternal(object $source): mixed;

    protected function toDateTimeImmutable(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return new DateTimeImmutable($value->format('Y-m-d H:i:s'));
        }

        if ($value === null || $value === '') {
            return null;
        }

        return new DateTimeImmutable((string) $value);
    }
}
