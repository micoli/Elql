<?php

declare(strict_types=1);

namespace Micoli\Elql\Encoder;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Yaml\Yaml;

class YamlEncoder implements EncoderInterface, DecoderInterface
{
    public const FORMAT = 'yaml';
    private const ALTERNATIVE_FORMAT = 'yml';

    public function encode($data, string $format, array $context = []): string
    {
        return Yaml::dump($data);
    }

    public function supportsEncoding(string $format, array $context = []): bool
    {
        return $format === self::FORMAT || $format === self::ALTERNATIVE_FORMAT;
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    public function decode(string $data, string $format, array $context = []): array
    {
        return Yaml::parse($data);
    }

    public function supportsDecoding(string $format, array $context = []): bool
    {
        return $format === 'yaml';
    }
}
