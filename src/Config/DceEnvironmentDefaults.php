<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Config;

final class DceEnvironmentDefaults
{
    public const ENV_EMISSION_TYPE = 'CHAMPS_FISCAL_DCE_EMISSION_TYPE';
    public const ENV_AUTHORIZER_SITE_NUMBER = 'CHAMPS_FISCAL_DCE_AUTHORIZER_SITE_NUMBER';
    public const ENV_ENVIRONMENT = 'CHAMPS_FISCAL_DCE_ENVIRONMENT';
    public const ENV_APPLICATION_VERSION = 'CHAMPS_FISCAL_DCE_APPLICATION_VERSION';
    public const ENV_QRCODE_BASE_URL = 'CHAMPS_DCE_QRCODE_BASE_URL';

    /**
     * @return array<string, string>
     */
    public static function fromEnv(?callable $reader = null): array
    {
        $reader ??= static fn (string $name, ?string $default = null): ?string => $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: $default;

        return array_filter([
            'ide.emissionType' => self::string($reader, self::ENV_EMISSION_TYPE),
            'ide.authorizerSiteNumber' => self::string($reader, self::ENV_AUTHORIZER_SITE_NUMBER),
            'ide.environment' => self::string($reader, self::ENV_ENVIRONMENT),
            'ide.processVersion' => self::string($reader, self::ENV_APPLICATION_VERSION),
            'qrCode.baseUrl' => self::string($reader, self::ENV_QRCODE_BASE_URL),
        ], static fn (?string $value): bool => $value !== null && $value !== '');
    }

    private static function string(callable $reader, string $name): ?string
    {
        $value = $reader($name, null);

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
