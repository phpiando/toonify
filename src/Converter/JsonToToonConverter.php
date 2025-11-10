<?php

declare(strict_types=1);

namespace Toonify\Converter;

use Toonify\Encoder\Encoder;
use Toonify\Exception\FileException;
use Toonify\Exception\NetworkException;

/**
 * JSON to TOON converter with support for strings, files and URLs
 *
 * @author Roni Sommerfeld<roni@phpiando.com>
 * @version 1.0.0
 * @license MIT
 */
class JsonToToonConverter
{
    /**
     * @version 1.0.0
     * @var Encoder
     */
    private Encoder $encoder;

    /**
     * Constructor
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->encoder = new Encoder($options);
    }

    /**
     * Converts JSON string to TOON
     *
     * @public
     * @version 1.0.0
     * @param string $json JSON string
     * @return string TOON string
     */
    public function fromString(string $json): string
    {
        return $this->encoder->encode($json);
    }

    /**
     * Converts JSON file from disk to TOON
     *
     * @public
     * @version 1.0.0
     * @param string $path File path
     * @return string TOON string
     * @throws FileException
     */
    public function fromDisk(string $path): string
    {
        if (!file_exists($path)) {
            throw new FileException("File not found: $path");
        }

        if (!is_readable($path)) {
            throw new FileException("File cannot be read: $path");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new FileException("Error reading file: $path");
        }

        return $this->fromString($content);
    }

    /**
     * Converts JSON from URL to TOON
     *
     * @public
     * @version 1.0.0
     * @param string $url JSON URL
     * @param array<string, mixed> $options HTTP context options
     * @return string TOON string
     * @throws NetworkException
     */
    public function fromUrl(string $url, array $options = []): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new NetworkException("Invalid URL: $url");
        }

        $defaultOptions = [
            'http' => [
                'method' => 'GET',
                'timeout' => 30,
                'user_agent' => 'Toonify/1.0',
                'follow_location' => true,
                'max_redirects' => 5,
            ],
        ];

        $contextOptions = array_merge_recursive($defaultOptions, $options);
        $context = stream_context_create($contextOptions);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            $error = error_get_last();
            throw new NetworkException(
                "Error fetching URL: " . ($error['message'] ?? 'Unknown error')
            );
        }

        return $this->fromString($content);
    }

    /**
     * Converts JSON to TOON and saves to file
     *
     * @public
     * @version 1.0.0
     * @param string $json JSON string
     * @param string $outputPath Output file path
     * @return bool
     * @throws FileException
     */
    public function toFile(string $json, string $outputPath): bool
    {
        $toon = $this->fromString($json);

        $result = file_put_contents($outputPath, $toon);

        if ($result === false) {
            throw new FileException("Error writing file: $outputPath");
        }

        return true;
    }

    /**
     * Converts JSON file to TOON file
     *
     * @public
     * @version 1.0.0
     * @param string $inputPath JSON file path
     * @param string $outputPath TOON file path
     * @return bool
     */
    public function convertFile(string $inputPath, string $outputPath): bool
    {
        $toon = $this->fromDisk($inputPath);

        $result = file_put_contents($outputPath, $toon);

        if ($result === false) {
            throw new FileException("Error writing file: $outputPath");
        }

        return true;
    }
}
