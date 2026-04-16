<?php

declare(strict_types=1);

namespace BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Hydrator;

use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAdditionalInfoRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceAuthorizationRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDestAddressRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceDestRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmitAddressRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceEmitRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceIdeRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTotalRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTranspRequest;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Input\DceTransportRequest;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

final class DceAuthorizationRequestHydrator
{
    /**
     * Mapeia paths de objetos intermediários para suas classes.
     *
     * @var array<string, class-string>
     */
    private const OBJECT_PATH_CLASS_MAP = [
        'ide' => DceIdeRequest::class,
        'emit' => DceEmitRequest::class,
        'emit.address' => DceEmitAddressRequest::class,
        'transp' => DceTranspRequest::class,
        'dest' => DceDestRequest::class,
        'dest.address' => DceDestAddressRequest::class,
        'total' => DceTotalRequest::class,
        'transport' => DceTransportRequest::class,
        'additionalInfo' => DceAdditionalInfoRequest::class,
    ];

    /**
     * Mapeia paths de objetos filhos para o nome da propriedade do pai.
     *
     * @var array<string, string>
     */
    private const OBJECT_PATH_PROPERTY_MAP = [
        'ide' => 'ide',
        'emit' => 'emit',
        'emit.address' => 'address',
        'transp' => 'transp',
        'dest' => 'dest',
        'dest.address' => 'address',
        'total' => 'total',
        'transport' => 'transport',
        'additionalInfo' => 'additionalInfo',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $fieldMap
     */
    public function hydrate(array $data, array $fieldMap): DceAuthorizationRequest
    {
        $request = new DceAuthorizationRequest();

        foreach ($fieldMap as $sourceField => $targetPath) {
            if (!array_key_exists($sourceField, $data)) {
                continue;
            }

            $value = $data[$sourceField];

            $this->applyValue($request, $targetPath, $value);
        }

        return $request;
    }

    private function applyValue(DceAuthorizationRequest $request, string $targetPath, mixed $value): void
    {
        $parts = explode('.', $targetPath);

        if (count($parts) < 2) {
            throw new InvalidArgumentException(sprintf(
                'Path inválido "%s". O path deve ter ao menos objeto e propriedade.',
                $targetPath
            ));
        }

        $property = array_pop($parts);
        if ($property === null || $property === '') {
            throw new InvalidArgumentException(sprintf('Path inválido "%s".', $targetPath));
        }

        $currentObject = $request;
        $currentPath = '';

        foreach ($parts as $segment) {
            $currentPath = $currentPath === '' ? $segment : $currentPath . '.' . $segment;
            $currentObject = $this->resolveObject($currentObject, $currentPath);
        }

        $setter = 'set' . ucfirst($property);

        if (!method_exists($currentObject, $setter)) {
            throw new RuntimeException(sprintf(
                'Setter "%s" não encontrado em %s para o path "%s".',
                $setter,
                $currentObject::class,
                $targetPath
            ));
        }

        $normalizedValue = $this->transformValueForSetter($currentObject, $setter, $value);

        $currentObject->{$setter}($normalizedValue);
    }

    private function resolveObject(object $parentObject, string $objectPath): object
    {
        if (!isset(self::OBJECT_PATH_PROPERTY_MAP[$objectPath], self::OBJECT_PATH_CLASS_MAP[$objectPath])) {
            throw new RuntimeException(sprintf(
                'Path de objeto "%s" não está mapeado no hydrator.',
                $objectPath
            ));
        }

        $property = self::OBJECT_PATH_PROPERTY_MAP[$objectPath];
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);

        if (!method_exists($parentObject, $getter) || !method_exists($parentObject, $setter)) {
            throw new RuntimeException(sprintf(
                'Getter/setter para a propriedade "%s" não encontrado em %s.',
                $property,
                $parentObject::class
            ));
        }

        $object = $parentObject->{$getter}();

        if ($object !== null) {
            return $object;
        }

        $className = self::OBJECT_PATH_CLASS_MAP[$objectPath];
        $object = new $className();

        $parentObject->{$setter}($object);

        return $object;
    }

    private function transformValueForSetter(object $object, string $setter, mixed $value): mixed
    {
        $parameterType = $this->getSetterParameterType($object, $setter);

        if ($parameterType === DateTimeImmutable::class) {
            if ($value === null || $value === '') {
                return null;
            }

            if ($value instanceof DateTimeImmutable) {
                return $value;
            }

            return new DateTimeImmutable((string) $value);
        }

        if ($parameterType === 'string') {
            if ($value === null) {
                return null;
            }

            return (string) $value;
        }

        return $value;
    }

    private function getSetterParameterType(object $object, string $setter): ?string
    {
        $reflectionMethod = new \ReflectionMethod($object, $setter);
        $parameters = $reflectionMethod->getParameters();

        if ($parameters === []) {
            return null;
        }

        $type = $parameters[0]->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return null;
        }

        return $type->getName();
    }
}
