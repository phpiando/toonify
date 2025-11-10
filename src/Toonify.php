<?php

declare(strict_types=1);

namespace Toonify;

use Toonify\Encoder\Encoder;
use Toonify\Decoder\Decoder;
use Toonify\Converter\JsonToToonConverter;
use Toonify\Converter\ToonToJsonConverter;

/**
 * Main class (facade) for JSON ↔ TOON conversion
 *
 * @author Roni Sommerfeld<roni@phpiando.com>
 * @version 1.0.0
 * @license MIT
 */
class Toonify
{
    /**
     * Converts JSON to TOON
     *
     * @public
     * @version 1.0.0
     * @param mixed $data PHP array/object or JSON string
     * @param array<string, mixed> $options Encoding options
     *   - delimiter: string (default: ',') - ',', "\t" or '|'
     *   - indent: int (default: 2) - Spaces per level
     *   - lengthMarker: string (default: '') - Prefix for array length
     * @return string String in TOON format
     */
    public static function encode(mixed $data, array $options = []): string
    {
        $encoder = new Encoder($options);
        return $encoder->encode($data);
    }

    /**
     * Converts TOON to JSON
     *
     * @public
     * @version 1.0.0
     * @param string $toon String in TOON format
     * @param array<string, mixed> $options Decoding options
     *   - strict: bool (default: true) - Strict validation
     *   - indent: int (default: 2) - Expected spaces per level
     * @return mixed PHP array/object
     */
    public static function decode(string $toon, array $options = []): mixed
    {
        $decoder = new Decoder($options);
        return $decoder->decode($toon);
    }

    /**
     * Converts JSON from string to TOON
     *
     * @public
     * @version 1.0.0
     * @param string $json JSON string
     * @param array<string, mixed> $options Encoding options
     * @return string String in TOON format
     */
    public static function fromJsonString(string $json, array $options = []): string
    {
        $converter = new JsonToToonConverter($options);
        return $converter->fromString($json);
    }

    /**
     * Converts JSON from local file to TOON
     *
     * @public
     * @version 1.0.0
     * @param string $path JSON file path
     * @param array<string, mixed> $options Encoding options
     * @return string String in TOON format
     */
    public static function fromJsonDisk(string $path, array $options = []): string
    {
        $converter = new JsonToToonConverter($options);
        return $converter->fromDisk($path);
    }

    /**
     * Converts JSON from URL to TOON
     *
     * @public
     * @version 1.0.0
     * @param string $url JSON file URL
     * @param array<string, mixed> $options Encoding options
     * @return string String in TOON format
     */
    public static function fromJsonUrl(string $url, array $options = []): string
    {
        $converter = new JsonToToonConverter($options);
        return $converter->fromUrl($url);
    }

    /**
     * Converts TOON from string to JSON
     *
     * @public
     * @version 1.0.0
     * @param string $toon TOON string
     * @param array<string, mixed> $options Decoding options
     * @return string JSON string
     */
    public static function toJsonString(string $toon, array $options = []): string
    {
        $converter = new ToonToJsonConverter($options);
        return $converter->toString($toon);
    }

    /**
     * Converts TOON from local file to JSON
     *
     * @public
     * @version 1.0.0
     * @param string $path TOON file path
     * @param array<string, mixed> $options Decoding options
     * @return string JSON string
     */
    public static function toJsonDisk(string $path, array $options = []): string
    {
        $converter = new ToonToJsonConverter($options);
        return $converter->fromDisk($path);
    }

    /**
     * Converts TOON from URL to JSON
     *
     * @public
     * @version 1.0.0
     * @param string $url TOON file URL
     * @param array<string, mixed> $options Decoding options
     * @return string JSON string
     */
    public static function toJsonUrl(string $url, array $options = []): string
    {
        $converter = new ToonToJsonConverter($options);
        return $converter->fromUrl($url);
    }

    /**
     * Detects and extracts TOON content from markdown (useful for LLM responses)
     *
     * @public
     * @version 1.0.0
     * @param string $content Content that may contain TOON in markdown
     * @return string|null Extracted TOON string or null if not found
     */
    public static function extractFromMarkdown(string $content): ?string
    {
        $converter = new ToonToJsonConverter();
        return $converter->extractFromMarkdown($content);
    }
}
