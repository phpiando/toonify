<?php

declare(strict_types=1);

namespace Toonify\Util;

/**
 * String manipulation utilities for TOON format
 *
 * @author Roni Sommerfeld<roni@phpiando.com>
 * @version 1.0.0
 * @license MIT
 */
class StringUtil
{
    /**
     * Checks if a string needs quotes in TOON format
     *
     * @public
     * @version 1.0.0
     * @param string $str String to check
     * @param string $delimiter Active delimiter
     * @return bool
     */
    public static function needsQuoting(string $str, string $delimiter = ','): bool
    {
        // Empty string always needs quotes
        if ($str === '') {
            return true;
        }

        // Keywords that need quotes
        $keywords = ['true', 'false', 'null'];
        if (in_array(strtolower($str), $keywords, true)) {
            return true;
        }

        // If looks like a number, needs quotes
        if (is_numeric($str)) {
            return true;
        }

        // If contains delimiter, needs quotes
        if (str_contains($str, $delimiter)) {
            return true;
        }

        // If contains special characters, needs quotes
        $specialChars = [':', '[', ']', '{', '}', '#', '-', "\n", "\r", "\t"];
        foreach ($specialChars as $char) {
            if (str_contains($str, $char)) {
                return true;
            }
        }

        // If starts or ends with space
        if ($str !== trim($str)) {
            return true;
        }

        // If contains quotes, needs quotes and escape
        if (str_contains($str, '"') || str_contains($str, "'")) {
            return true;
        }

        return false;
    }

    /**
     * Summary of isJsonString
     *
     * @public
     * @version 1.0.0
     * @param string $str
     * @return bool
     */
    public static function isJsonString(string $str): bool
    {
        json_decode($str);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Escapes a string for use in TOON format
     *
     * @public
     * @version 1.0.0
     * @param string $str String to escape
     * @return string Escaped string
     */
    public static function escape(string $str): string
    {
        $str = str_replace('\\', '\\\\', $str);
        $str = str_replace('"', '\\"', $str);
        $str = str_replace("\n", '\\n', $str);
        $str = str_replace("\r", '\\r', $str);
        $str = str_replace("\t", '\\t', $str);
        return $str;
    }

    /**
     * Removes escapes from a TOON string
     *
     * @public
     * @version 1.0.0
     * @param string $str Escaped string
     * @return string String without escapes
     */
    public static function unescape(string $str): string
    {
        $str = str_replace('\\n', "\n", $str);
        $str = str_replace('\\r', "\r", $str);
        $str = str_replace('\\t', "\t", $str);
        $str = str_replace('\\"', '"', $str);
        $str = str_replace('\\\\', '\\', $str);
        return $str;
    }

    /**
     * Adds quotes to a string if necessary
     *
     * @public
     * @version 1.0.0
     * @param string $str String to process
     * @param string $delimiter Active delimiter
     * @return string String with quotes if necessary
     */
    public static function quote(string $str, string $delimiter = ','): string
    {
        if (self::needsQuoting($str, $delimiter)) {
            return '"' . self::escape($str) . '"';
        }
        return $str;
    }

    /**
     * Removes quotes from a TOON string
     *
     * @public
     * @version 1.0.0
     * @param string $str String with quotes
     * @return string String without quotes
     */
    public static function unquote(string $str): string
    {
        $str = trim($str);

        if (
            (str_starts_with($str, '"') && str_ends_with($str, '"')) ||
            (str_starts_with($str, "'") && str_ends_with($str, "'"))
        ) {
            $str = substr($str, 1, -1);
            return self::unescape($str);
        }

        return $str;
    }

    /**
     * Extracts TOON content from markdown blocks
     *
     * Supports:
     * - ```toon ... ```
     * - ```TOON ... ```
     * - ``` ... ``` (if content looks like TOON)
     * - Direct content (if looks like TOON)
     *
     * @public
     * @version 1.0.0
     * @param string $content Content that may contain TOON in markdown
     * @return string|null Extracted TOON content or null
     */
    public static function extractFromMarkdown(string $content): ?string
    {
        // Pattern 1: Blocks explicitly marked as toon/TOON
        $patterns = [
            '/```\s*toon\s*\n(.*?)\n```/is',
            '/```\s*TOON\s*\n(.*?)\n```/is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return trim($matches[1]);
            }
        }

        // Pattern 2: Generic ``` blocks that look like TOON
        // Searches for blocks that start with TOON pattern
        if (preg_match('/```\s*\n(.*?)\n```/is', $content, $matches)) {
            $possibleToon = trim($matches[1]);
            if (self::looksToon($possibleToon)) {
                return $possibleToon;
            }
        }

        // Pattern 3: Multiple code blocks - get the first that looks like TOON
        if (preg_match_all('/```(?:\w+)?\s*\n(.*?)\n```/is', $content, $allMatches)) {
            foreach ($allMatches[1] as $block) {
                $block = trim($block);
                if (self::looksToon($block)) {
                    return $block;
                }
            }
        }

        // Pattern 4: If no markdown blocks found, check if entire content looks like TOON
        $trimmed = trim($content);
        if (self::looksToon($trimmed)) {
            return $trimmed;
        }

        return null;
    }

    /**
     * Checks if a string looks like TOON format
     *
     * @public
     * @version 1.0.0
     * @param string $content Content to check
     * @return bool
     */
    public static function looksToon(string $content): bool
    {
        $content = trim($content);

        if (empty($content)) {
            return false;
        }

        // Strong TOON format characteristics (high confidence):
        $strongPatterns = [
            // Tabular array: [N,]{keys}: or key[N,]{keys}:
            '/^[a-z_][a-z0-9_]*\[\d+[,|\t]?\]\{[a-z_][a-z0-9_,]*\}:/im',
            '/^\[\d+[,|\t]?\]\{[a-z_][a-z0-9_,]*\}:/im',

            // Array with length marker: [N,]: or [N|]:
            '/^\[\d+[,|\t]\]:/im',
        ];

        foreach ($strongPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        // Medium TOON format characteristics (medium confidence):
        // Multiple lines with key: value
        $lines = explode("\n", $content);
        $keyValueCount = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                continue;
            }

            // Count lines with key: value pattern
            if (preg_match('/^[a-z_][a-z0-9_]*:\s*.+$/i', $trimmed)) {
                $keyValueCount++;
            }
        }

        // If has 2+ lines with key: value, probably TOON
        if ($keyValueCount >= 2) {
            return true;
        }

        // Weak characteristics (low confidence):
        // Only one line with key: value is not enough
        // as it could be YAML or another format

        return false;
    }

    /**
     * Normalizes string indentation
     *
     * @public
     * @version 1.0.0
     * @param string $content Content
     * @param int $spaces Number of spaces per level
     * @return string Normalized content
     */
    public static function normalizeIndentation(string $content, int $spaces = 2): string
    {
        $lines = explode("\n", $content);
        $normalized = [];

        foreach ($lines as $line) {
            // Count spaces at the beginning
            preg_match('/^(\s*)/', $line, $matches);
            $currentIndent = strlen($matches[1]);

            // Calculate new indentation level
            $level = (int)($currentIndent / $spaces);
            $newIndent = str_repeat(' ', $level * $spaces);

            // Remove old indentation and add new
            $normalized[] = $newIndent . ltrim($line);
        }

        return implode("\n", $normalized);
    }

    /**
     * Removes markdown code blocks from content
     * Useful for cleaning text before processing
     *
     * @public
     * @version 1.0.0
     * @param string $content Content with markdown
     * @return string Content without code blocks
     */
    public static function stripMarkdownCodeBlocks(string $content): string
    {
        // Remove ```...``` blocks
        $content = preg_replace('/```.*?```/s', '', $content);

        // Remove inline code `...`
        $content = preg_replace('/`[^`]+`/', '', $content);

        return trim($content);
    }

    /**
     * Detects the delimiter type used in a TOON
     *
     * @public
     * @version 1.0.0
     * @param string $toon TOON content
     * @return string Detected delimiter: ',', "\t" or '|'
     */
    public static function detectDelimiter(string $toon): string
    {
        // Look for array headers with explicit delimiter
        if (preg_match('/\[\d+\t\]/', $toon)) {
            return "\t";
        }

        if (preg_match('/\[\d+\|\]/', $toon)) {
            return '|';
        }

        // Default: comma
        return ',';
    }

    /**
     * Validates if a TOON string has valid basic syntax
     *
     * @public
     * @version 1.0.0
     * @param string $toon TOON string
     * @return array{valid: bool, errors: array<string>}
     */
    public static function validateToonSyntax(string $toon): array
    {
        $errors = [];
        $lines = explode("\n", $toon);

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);

            if (empty($trimmed)) {
                continue;
            }

            // Check balanced parentheses/brackets
            $brackets = 0;
            $braces = 0;

            for ($i = 0; $i < strlen($trimmed); $i++) {
                if ($trimmed[$i] === '[') $brackets++;
                if ($trimmed[$i] === ']') $brackets--;
                if ($trimmed[$i] === '{') $braces++;
                if ($trimmed[$i] === '}') $braces--;
            }

            if ($brackets !== 0) {
                $errors[] = "Line " . ($lineNum + 1) . ": Unbalanced brackets";
            }

            if ($braces !== 0) {
                $errors[] = "Line " . ($lineNum + 1) . ": Unbalanced braces";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}