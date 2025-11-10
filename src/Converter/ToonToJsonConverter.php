<?php

declare(strict_types=1);

namespace Toonify\Converter;

use Toonify\Decoder\Decoder;
use Toonify\Exception\FileException;
use Toonify\Exception\NetworkException;
use Toonify\Util\StringUtil;

/**
 * Converts TOON to JSON with support for strings, files, and URLs
 *
 * @author Roni Sommerfeld<roni@phpiando.com>
 * @version 1.0.0
 * @license MIT
 */
class ToonToJsonConverter
{
    /**
     * @version 1.0.0
     * @var Decoder
     */
    private Decoder $decoder;
    /**
     * @version 1.0.0
     * @var int
     */
    private int $jsonOptions;

    /**
     * Constructor
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->decoder = new Decoder($options);
        $this->jsonOptions = $options['jsonOptions'] ?? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
    }

    /**
     * Converts TOON string to JSON
     *
     * @public
     * @version 1.0.0
     * @param string $toon String TOON
     * @return string String JSON
     */
    public function toString(string $toon): string
    {
        return $this->decoder->toJson($toon, $this->jsonOptions);
    }

    /**
     * Converts TOON file from disk to JSON
     *
     * @public
     * @version 1.0.0
     * @param string $path File path
     * @return string JSON string
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

        return $this->toString($content);
    }

    /**
     * Converts TOON from URL to JSON
     *
     * @public
     * @version 1.0.0
     * @param string $url TOON URL
     * @param array<string, mixed> $options HTTP context options
     * @return string JSON string
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

        return $this->toString($content);
    }

    /**
     * Converts TOON to JSON and saves to file
     *
     * @public
     * @version 1.0.0
     * @param string $toon TOON string
     * @param string $outputPath Output file path
     * @return bool
     * @throws FileException
     */
    public function toFile(string $toon, string $outputPath): bool
    {
        $json = $this->toString($toon);

        $result = file_put_contents($outputPath, $json);

        if ($result === false) {
            throw new FileException("Error writing file: $outputPath");
        }

        return true;
    }

    /**
     * Converts TOON file to JSON file
     *
     * @public
     * @version 1.0.0
     * @param string $inputPath TOON file path
     * @param string $outputPath JSON file path
     * @return bool
     */
    public function convertFile(string $inputPath, string $outputPath): bool
    {
        $json = $this->fromDisk($inputPath);

        $result = file_put_contents($outputPath, $json);

        if ($result === false) {
            throw new FileException("Error writing file: $outputPath");
        }

        return true;
    }

    /**
     * Extracts TOON content from markdown (useful for LLM responses)
     *
     * @public
     * @version 1.0.0
     * @param string $content Content that may contain TOON in markdown
     * @return string|null Extracted TOON string or null if not found
     */
    public function extractFromMarkdown(string $content): ?string
    {
        return StringUtil::extractFromMarkdown($content);
    }

    /**
     * Converts TOON extracted from markdown to JSON
     *
     * @public
     * @version 1.0.0
     * @param string $content Content with markdown
     * @return string|null JSON string or null if no TOON found
     */
    public function fromMarkdown(string $content): ?string
    {
        $toon = $this->extractFromMarkdown($content);

        if ($toon === null) {
            return null;
        }

        return $this->toString($toon);
    }
}
