<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal;

final class Package
{
    public static function rootPath(): string
    {
        return dirname(__DIR__);
    }

    public static function templatesPath(): string
    {
        return self::rootPath() . '/src/resources/templates';
    }
}
