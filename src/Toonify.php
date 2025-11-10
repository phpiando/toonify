<?php

namespace Phpiando\Toonify;

/**
 * Toonify - Main facade class for easy JSON <-> TOON conversion
 * 
 * This class provides a simple interface for converting between JSON and TOON format.
 * TOON format is designed to optimize data exchange for LLMs with compact syntax
 * and efficient token usage.
 */
class Toonify
{
    private ToonEncoder $encoder;
    private ToonDecoder $decoder;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->encoder = new ToonEncoder();
        $this->decoder = new ToonDecoder();
    }

    /**
     * Convert JSON string to TOON format
     *
     * @param string $json The JSON string to convert
     * @return string The TOON-encoded string
     * @throws \InvalidArgumentException If JSON is invalid
     */
    public function jsonToToon(string $json): string
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        return $this->encoder->encode($data);
    }

    /**
     * Convert TOON format to JSON string
     *
     * @param string $toon The TOON string to convert
     * @param int $options JSON encoding options (default: 0)
     * @return string The JSON-encoded string
     * @throws \RuntimeException If TOON parsing fails
     */
    public function toonToJson(string $toon, int $options = 0): string
    {
        $data = $this->decoder->decode($toon);
        $json = json_encode($data, $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Encode PHP data to TOON format
     *
     * @param mixed $data The data to encode
     * @return string The TOON-encoded string
     */
    public function encode($data): string
    {
        return $this->encoder->encode($data);
    }

    /**
     * Decode TOON format to PHP data
     *
     * @param string $toon The TOON string to decode
     * @return mixed The decoded data
     */
    public function decode(string $toon)
    {
        return $this->decoder->decode($toon);
    }

    /**
     * Get the encoder instance
     *
     * @return ToonEncoder
     */
    public function getEncoder(): ToonEncoder
    {
        return $this->encoder;
    }

    /**
     * Get the decoder instance
     *
     * @return ToonDecoder
     */
    public function getDecoder(): ToonDecoder
    {
        return $this->decoder;
    }

    /**
     * Static helper: Convert JSON to TOON
     *
     * @param string $json The JSON string
     * @return string The TOON string
     */
    public static function fromJson(string $json): string
    {
        $toonify = new self();
        return $toonify->jsonToToon($json);
    }

    /**
     * Static helper: Convert TOON to JSON
     *
     * @param string $toon The TOON string
     * @param int $options JSON encoding options
     * @return string The JSON string
     */
    public static function toJson(string $toon, int $options = 0): string
    {
        $toonify = new self();
        return $toonify->toonToJson($toon, $options);
    }
}
