<?php

declare(strict_types=1);

namespace Toonify\Decoder;

use Toonify\Exception\DecodingException;
use Toonify\Util\StringUtil;

/**
 * Decoder: converts TOON format to PHP arrays/objects
 *
 * @author Roni Sommerfeld<roni@phpiando.com>
 * @version 1.0.0
 * @license MIT
 */
class Decoder
{
    /**
     * If true, strictly validates the number of items in arrays
     *
     * @version 1.0.0
     * @var bool
     */
    private bool $strict;

    /**
     * Indentation level
     *
     * @version 1.0.0
     * @var int
     */
    private int $indent;

    /**
     * Constructor
     *
     * @public
     * @version 1.0.0
     * @param array<string, mixed> $options Configuration options
     */
    public function __construct(array $options = [])
    {
        $this->strict = $options['strict'] ?? true;
        $this->indent = $options['indent'] ?? 2;
    }

    /**
     * Converts TOON to PHP array/object
     *
     * @public
     * @version 1.0.0
     * @param string $toon TOON string
     * @throws DecodingException If TOON string is empty
     * @return mixed PHP array/object
     */
    public function decode(string $toon): mixed
    {
        $toon = trim($toon);

        if (empty($toon)) {
            throw new DecodingException('Empty TOON string');
        }

        $lines = explode("\n", $toon);
        $result = $this->parseLines($lines, 0);

        return $result['value'];
    }

    /**
     * Converts TOON to JSON string
     *
     * @public
     * @version 1.0.0
     * @param string $toon TOON string
     * @param int $jsonOptions json_encode options
     * @throws DecodingException If conversion to JSON fails
     * @return string JSON string
     */
    public function toJson(string $toon, int $jsonOptions = JSON_PRETTY_PRINT): string
    {
        $data = $this->decode($toon);
        $json = json_encode($data, $jsonOptions);

        if ($json === false) {
            throw new DecodingException('Error converting to JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Parses TOON lines
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines Lines to parse
     * @param int $startIndex Start index
     * @return array{value: mixed, endIndex: int}
     */
    private function parseLines(array $lines, int $startIndex): array
    {
        $result = [];
        $i = $startIndex;

        $baseIndent = $this->calculateBaseIndent($lines, $i);

        if ($baseIndent === null) {
            return ['value' => $result, 'endIndex' => $i];
        }

        while ($i < count($lines)) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if ($this->shouldBreakOnIndent($line, $baseIndent)) {
                break;
            }

            if ($trimmed === '') {
                $i++;
                continue;
            }

            $parsed = $this->parseSingleLine($lines, $i, $trimmed, $result);

            if ($parsed !== null) {
                $result = $parsed['result'];
                $i = $parsed['index'];
                continue;
            }

            $i++;
        }

        return ['value' => $result, 'endIndex' => $i];
    }

    /**
     * Calculates base indentation from first non-empty line
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines Lines to check
     * @param int $startIndex Start index
     * @return int|null Base indentation level or null if no non-empty lines
     */
    private function calculateBaseIndent(array $lines, int $startIndex): ?int
    {
        for ($j = $startIndex; $j < count($lines); $j++) {
            if (trim($lines[$j]) !== '') {
                return $this->getIndentLevel($lines[$j]);
            }
        }
        return null;
    }

    /**
     * Checks if loop should break based on indentation
     *
     * @private
     * @version 1.0.0
     * @param string $line Current line
     * @param int $baseIndent Base indentation level
     * @return bool True if should break, false otherwise
     */
    private function shouldBreakOnIndent(string $line, int $baseIndent): bool
    {
        $currentIndent = $this->getIndentLevel($line);
        return $currentIndent < $baseIndent && trim($line) !== '';
    }

    /**
     * Parses a single line and updates result
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param string $trimmed Trimmed line content
     * @param array<mixed, mixed> $result Current result array
     * @return array{result: array<mixed, mixed>, index: int}|null Parsed result or null if not handled
     */
    private function parseSingleLine(array $lines, int $index, string $trimmed, array $result): ?array
    {
        // Named array pattern
        if ($this->isNamedArrayPattern($trimmed)) {
            return $this->handleNamedArray($lines, $index, $trimmed, $result);
        }

        // Unnamed array pattern
        if ($this->isUnnamedArrayPattern($trimmed)) {
            return $this->handleUnnamedArray($lines, $index, $trimmed, $result);
        }

        // Key-value pattern
        if ($this->isKeyValuePattern($trimmed)) {
            return $this->handleKeyValue($lines, $index, $trimmed, $result);
        }

        // List item with inline content
        if ($this->isListItemPattern($trimmed)) {
            return $this->handleListItem($lines, $index, $trimmed, $result);
        }

        // List item (just hyphen)
        if ($trimmed === '-') {
            return $this->handleEmptyListItem($lines, $index, $result);
        }

        return null;
    }

    /**
     * Checks if line matches named array pattern
     *
     * @private
     * @version 1.0.0
     * @param string $trimmed Trimmed line
     * @return bool True if matches pattern
     */
    private function isNamedArrayPattern(string $trimmed): bool
    {
        return (bool)preg_match('/^([a-z_][a-z0-9_]*)\[(\d+)([,|\t])?\](?:\{([^}]+)\})?:\s*(.*)$/i', $trimmed);
    }

    /**
     * Checks if line matches unnamed array pattern
     *
     * @private
     * @version 1.0.0
     * @param string $trimmed Trimmed line
     * @return bool True if matches pattern
     */
    private function isUnnamedArrayPattern(string $trimmed): bool
    {
        return (bool)preg_match('/^\[(\d+)([,|\t])?\](?:\{([^}]+)\})?:\s*(.*)$/', $trimmed);
    }

    /**
     * Checks if line matches key-value pattern
     *
     * @private
     * @version 1.0.0
     * @param string $trimmed Trimmed line
     * @return bool True if matches pattern
     */
    private function isKeyValuePattern(string $trimmed): bool
    {
        return (bool)preg_match('/^([a-z_][a-z0-9_]*):(.*)$/i', $trimmed);
    }

    /**
     * Checks if line matches list item pattern
     *
     * @private
     * @version 1.0.0
     * @param string $trimmed Trimmed line
     * @return bool True if matches pattern
     */
    private function isListItemPattern(string $trimmed): bool
    {
        return (bool)preg_match('/^-\s+(.+)/', $trimmed);
    }

    /**
     * Handles named array parsing
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param string $trimmed Trimmed line content
     * @param array<mixed, mixed> $result Current result array
     * @return array{result: array<mixed, mixed>, index: int}
     */
    private function handleNamedArray(array $lines, int $index, string $trimmed, array $result): array
    {
        preg_match('/^([a-z_][a-z0-9_]*)\[(\d+)([,|\t])?\](?:\{([^}]+)\})?:\s*(.*)$/i', $trimmed, $matches);

        $key = $matches[1];
        $count = (int)$matches[2];
        $delimiter = $matches[3] ?? ',';
        $keys = isset($matches[4]) && $matches[4] !== '' ? explode(',', $matches[4]) : null;
        $inlineValue = trim($matches[5]);

        $arrayMatches = [$matches[0], $count, $delimiter, $matches[4] ?? null, $inlineValue];
        $parsed = $this->parseArrayHeader($lines, $index, $arrayMatches);

        $result[$key] = $parsed['value'];

        return ['result' => $result, 'index' => $parsed['endIndex']];
    }

    /**
     * Handles unnamed array parsing
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param string $trimmed Trimmed line content
     * @param array<mixed, mixed> $result Current result array
     * @return array{result: array<mixed, mixed>, index: int}
     */
    private function handleUnnamedArray(array $lines, int $index, string $trimmed, array $result): array
    {
        preg_match('/^\[(\d+)([,|\t])?\](?:\{([^}]+)\})?:\s*(.*)$/', $trimmed, $matches);
        $parsed = $this->parseArrayHeader($lines, $index, $matches);

        if (empty($result)) {
            $result = $parsed['value'];
        } else {
            $result[] = $parsed['value'];
        }

        return ['result' => $result, 'index' => $parsed['endIndex']];
    }

    /**
     * Handles key-value parsing
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param string $trimmed Trimmed line content
     * @param array<mixed, mixed> $result Current result array
     * @return array{result: array<mixed, mixed>, index: int}
     */
    private function handleKeyValue(array $lines, int $index, string $trimmed, array $result): array
    {
        preg_match('/^([a-z_][a-z0-9_]*):(.*)$/i', $trimmed, $matches);
        $key = $matches[1];
        $valueStr = trim($matches[2]);

        if ($valueStr === '') {
            $parsed = $this->parseLines($lines, $index + 1);
            $result[$key] = $parsed['value'];
            return ['result' => $result, 'index' => $parsed['endIndex']];
        }

        $result[$key] = $this->parseValue($valueStr);
        return ['result' => $result, 'index' => $index + 1];
    }

    /**
     * Handles list item parsing
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param string $trimmed Trimmed line content
     * @param array<mixed, mixed> $result Current result array
     * @return array{result: array<mixed, mixed>, index: int}
     */
    private function handleListItem(array $lines, int $index, string $trimmed, array $result): array
    {
        preg_match('/^-\s+(.+)/', $trimmed, $matches);
        $content = $matches[1];

        if ($this->isInlineObjectFormat($content)) {
            $item = $this->parseInlineObject($content);
            $result[] = $item;
            return ['result' => $result, 'index' => $index + 1];
        }

        $result[] = $this->parseValue($content);
        return ['result' => $result, 'index' => $index + 1];
    }

    /**
     * Handles empty list item (just hyphen)
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param array<mixed, mixed> $result Current result array
     * @return array{result: array<mixed, mixed>, index: int}
     */
    private function handleEmptyListItem(array $lines, int $index, array $result): array
    {
        $parsed = $this->parseLines($lines, $index + 1);
        $result[] = $parsed['value'];
        return ['result' => $result, 'index' => $parsed['endIndex']];
    }

    /**
     * Checks if content is inline object format
     *
     * @private
     * @version 1.0.0
     * @param string $content Content to check
     * @return bool True if inline object format
     */
    private function isInlineObjectFormat(string $content): bool
    {
        $matches = [];
        return (bool)preg_match_all('/([a-z_][a-z0-9_]*)\s*:\s*([^,]+)(?:,|$)/i', $content, $matches, PREG_SET_ORDER);
    }

    /**
     * Parses inline object format
     *
     * @private
     * @version 1.0.0
     * @param string $content Content to parse
     * @return array<string, mixed> Parsed object
     */
    private function parseInlineObject(string $content): array
    {
        $item = [];
        preg_match_all('/([a-z_][a-z0-9_]*)\s*:\s*([^,]+)(?:,|$)/i', $content, $pairMatches, PREG_SET_ORDER);

        foreach ($pairMatches as $pair) {
            $item[$pair[1]] = $this->parseValue(trim($pair[2]));
        }

        return $item;
    }

    /**
     * Parses an array header
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param array<int, string|null> $matches Regex matches
     * @throws DecodingException If array length mismatch in strict mode
     * @return array{value: mixed, endIndex: int}
     */
    private function parseArrayHeader(array $lines, int $index, array $matches): array
    {
        $count = (int)$matches[1];
        $delimiter = $matches[2] ?? ',';
        $keys = isset($matches[3]) && $matches[3] !== null && $matches[3] !== '' ? explode(',', $matches[3]) : null;
        $inlineValue = trim($matches[4] ?? '');

        if ($inlineValue !== '') {
            return $this->parsePrimitiveArrayInline($inlineValue, $delimiter, $count, $index);
        }

        if ($keys !== null) {
            return $this->parseTabularArray($lines, $index + 1, $count, $keys, $delimiter);
        }

        return $this->parseListArray($lines, $index + 1, $count);
    }

    /**
     * Parses primitive array inline
     *
     * @private
     * @version 1.0.0
     * @param string $inlineValue Inline value string
     * @param string $delimiter Delimiter character
     * @param int $count Expected count
     * @param int $index Current line index
     * @throws DecodingException If array length mismatch in strict mode
     * @return array{value: array<int, mixed>, endIndex: int}
     */
    private function parsePrimitiveArrayInline(string $inlineValue, string $delimiter, int $count, int $index): array
    {
        $values = $this->splitByDelimiter($inlineValue, $delimiter);
        $result = array_map(fn($v) => $this->parseValue($v), $values);

        if ($this->strict && count($result) !== $count) {
            throw new DecodingException("Array length mismatch: expected $count, found " . count($result));
        }

        return ['value' => $result, 'endIndex' => $index + 1];
    }

    /**
     * Parses tabular array
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $startIndex Start index
     * @param int $count Expected number of rows
     * @param array<int, string> $keys Column keys
     * @param string $delimiter Delimiter character
     * @throws DecodingException If incomplete array in strict mode
     * @return array{value: array<int, array<string, mixed>>, endIndex: int}
     */
    private function parseTabularArray(
        array $lines,
        int $startIndex,
        int $count,
        array $keys,
        string $delimiter
    ): array {
        $result = [];
        $i = $startIndex;

        for ($row = 0; $row < $count; $row++) {
            if ($i >= count($lines)) {
                if ($this->strict) {
                    throw new DecodingException("Incomplete tabular array: expected $count lines");
                }
                break;
            }

            $line = trim($lines[$i]);

            if ($line === '') {
                $i++;
                $row--;
                continue;
            }

            $item = $this->parseTabularRow($line, $keys, $delimiter);
            $result[] = $item;
            $i++;
        }

        return ['value' => $result, 'endIndex' => $i];
    }

    /**
     * Parses a single tabular row
     *
     * @private
     * @version 1.0.0
     * @param string $line Line to parse
     * @param array<int, string> $keys Column keys
     * @param string $delimiter Delimiter character
     * @throws DecodingException If value count doesn't match key count in strict mode
     * @return array<string, mixed> Parsed row
     */
    private function parseTabularRow(string $line, array $keys, string $delimiter): array
    {
        $values = $this->splitByDelimiter($line, $delimiter);

        if ($this->strict && count($values) !== count($keys)) {
            throw new DecodingException('Number of values does not match number of keys');
        }

        $item = [];
        foreach ($keys as $index => $key) {
            $item[$key] = $this->parseValue($values[$index] ?? null);
        }

        return $item;
    }

    /**
     * Parses list array
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $startIndex Start index
     * @param int $count Expected item count
     * @throws DecodingException If item count mismatch in strict mode or invalid format
     * @return array{value: array<int, mixed>, endIndex: int}
     */
    private function parseListArray(array $lines, int $startIndex, int $count): array
    {
        $result = [];
        $i = $startIndex;
        $itemCount = 0;

        while ($i < count($lines) && $itemCount < $count) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if ($trimmed === '') {
                $i++;
                continue;
            }

            if (!preg_match('/^\s*-\s*(.*)$/', $line, $mDash)) {
                $i++;
                continue;
            }

            $parsed = $this->parseListArrayItem($lines, $i, $line, $mDash[1]);
            $result[] = $parsed['value'];
            $itemCount++;
            $i = $parsed['endIndex'];
        }

        if ($this->strict && $itemCount !== $count) {
            throw new DecodingException("List array: expected $count items, found $itemCount");
        }

        return ['value' => $result, 'endIndex' => $i];
    }

    /**
     * Parses a single list array item
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param string $line Full line with indentation
     * @param string $tail Content after dash
     * @throws DecodingException If invalid format
     * @return array{value: mixed, endIndex: int}
     */
    private function parseListArrayItem(array $lines, int $index, string $line, string $tail): array
    {
        $itemIndent = $this->getIndentFromLine($line);

        // Object format: "- key: value" or "- key:"
        if ($tail !== '' && preg_match('/^([^\s:][^:]*)\s*:\s*(.*)$/', trim($tail), $mKV)) {
            return $this->parseObjectListItem($lines, $index, $itemIndent, $mKV);
        }

        // Inline object with commas: "- k1: v1, k2: v2"
        if ($this->isInlineCommaObject($tail)) {
            return $this->parseInlineCommaObject($tail, $index);
        }

        // Scalar value: "- value"
        if ($tail !== '') {
            return ['value' => $this->parseValue(trim($tail)), 'endIndex' => $index + 1];
        }

        // Block content: "-" alone
        $parsed = $this->parseLines($lines, $index + 1);
        return ['value' => $parsed['value'], 'endIndex' => $parsed['endIndex']];
    }

    /**
     * Gets indentation from line
     *
     * @private
     * @version 1.0.0
     * @param string $line Line to check
     * @return int Indentation in spaces
     */
    private function getIndentFromLine(string $line): int
    {
        return strlen($line) - strlen(ltrim($line, " "));
    }

    /**
     * Checks if content is inline comma-separated object
     *
     * @private
     * @version 1.0.0
     * @param string $tail Content to check
     * @return bool True if inline comma object
     */
    private function isInlineCommaObject(string $tail): bool
    {
        if ($tail === '' || !str_contains($tail, ',')) {
            return false;
        }

        $matches = [];
        $numPairs = preg_match_all('/([a-z_][a-z0-9_]*)\s*:\s*([^,]+)(?:,|$)/i', trim($tail), $matches, PREG_SET_ORDER) ?: 0;

        return $numPairs >= 2;
    }

    /**
     * Parses inline comma-separated object
     *
     * @private
     * @version 1.0.0
     * @param string $tail Content to parse
     * @param int $index Current line index
     * @return array{value: array<string, mixed>, endIndex: int}
     */
    private function parseInlineCommaObject(string $tail, int $index): array
    {
        $item = [];
        preg_match_all('/([a-z_][a-z0-9_]*)\s*:\s*([^,]+)(?:,|$)/i', trim($tail), $matches, PREG_SET_ORDER);

        foreach ($matches as $pair) {
            $item[$pair[1]] = $this->parseValue(trim($pair[2]));
        }

        return ['value' => $item, 'endIndex' => $index + 1];
    }

    /**
     * Parses object list item
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param int $itemIndent Item indentation level
     * @param array<int, string> $matches Regex matches
     * @throws DecodingException If invalid format
     * @return array{value: array<string, mixed>, endIndex: int}
     */
    private function parseObjectListItem(array $lines, int $index, int $itemIndent, array $matches): array
    {
        $key = rtrim($matches[1]);
        $inline = $matches[2];
        $obj = [];
        $nextIndex = $index + 1;

        if ($inline !== '') {
            $obj[$key] = $this->parseValue($inline);
        } else {
            $obj[$key] = $this->parseBlockValue($lines, $nextIndex, $itemIndent);
            if ($obj[$key]['parsed']) {
                $nextIndex = $obj[$key]['index'];
                $obj[$key] = $obj[$key]['value'];
            }
        }

        $continuation = $this->parseContinuationKeys($lines, $nextIndex, $itemIndent);
        $obj = array_merge($obj, $continuation['object']);

        return ['value' => $obj, 'endIndex' => $continuation['endIndex']];
    }

    /**
     * Parses block value for object key
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $nextIndex Next line index
     * @param int $itemIndent Item indentation level
     * @return array{value: mixed, index: int, parsed: bool}
     */
    private function parseBlockValue(array $lines, int $nextIndex, int $itemIndent): array
    {
        if ($nextIndex >= count($lines)) {
            return ['value' => null, 'index' => $nextIndex, 'parsed' => false];
        }

        $after = $lines[$nextIndex];
        $afterIndent = $this->getIndentFromLine($after);

        if ($afterIndent > $itemIndent) {
            $parsed = $this->parseLines($lines, $nextIndex);
            return ['value' => $parsed['value'], 'index' => $parsed['endIndex'], 'parsed' => true];
        }

        return ['value' => null, 'index' => $nextIndex, 'parsed' => false];
    }

    /**
     * Parses continuation keys in object
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $nextIndex Starting index
     * @param int $itemIndent Item indentation level
     * @throws DecodingException If invalid key format
     * @return array{object: array<string, mixed>, endIndex: int}
     */
    private function parseContinuationKeys(array $lines, int $nextIndex, int $itemIndent): array
    {
        $obj = [];

        while ($nextIndex < count($lines)) {
            $peek = $lines[$nextIndex];

            if (trim($peek) === '') {
                $nextIndex++;
                continue;
            }

            $peekIndent = $this->getIndentFromLine($peek);

            if ($this->shouldStopContinuation($peek, $peekIndent, $itemIndent)) {
                break;
            }

            $parsed = $this->parseContinuationKey($lines, $nextIndex, $peekIndent);
            $obj[$parsed['key']] = $parsed['value'];
            $nextIndex = $parsed['endIndex'];
        }

        return ['object' => $obj, 'endIndex' => $nextIndex];
    }

    /**
     * Checks if should stop parsing continuation keys
     *
     * @private
     * @version 1.0.0
     * @param string $peek Line to check
     * @param int $peekIndent Line indentation
     * @param int $itemIndent Item indentation
     * @return bool True if should stop
     */
    private function shouldStopContinuation(string $peek, int $peekIndent, int $itemIndent): bool
    {
        if ($peekIndent === $itemIndent && preg_match('/^\s*-\s+/', $peek)) {
            return true;
        }

        if ($peekIndent <= $itemIndent) {
            return true;
        }

        return false;
    }

    /**
     * Parses a single continuation key
     *
     * @private
     * @version 1.0.0
     * @param array<int, string> $lines All lines
     * @param int $index Current line index
     * @param int $peekIndent Line indentation
     * @throws DecodingException If invalid key format
     * @return array{key: string, value: mixed, endIndex: int}
     */
    private function parseContinuationKey(array $lines, int $index, int $peekIndent): array
    {
        $peekTrim = trim($lines[$index]);

        if (!preg_match('/^([^\s:][^:]*)\s*:\s*(.*)$/', $peekTrim, $mCont)) {
            throw new DecodingException("Expected 'key: value' in list item continuation", $index + 1);
        }

        $key = rtrim($mCont[1]);
        $inline = $mCont[2];

        if ($inline !== '') {
            return ['key' => $key, 'value' => $this->parseValue($inline), 'endIndex' => $index + 1];
        }

        if ($index + 1 < count($lines)) {
            $after = $lines[$index + 1];
            $afterIndent = $this->getIndentFromLine($after);

            if ($afterIndent > $peekIndent) {
                $parsed = $this->parseLines($lines, $index + 1);
                return ['key' => $key, 'value' => $parsed['value'], 'endIndex' => $parsed['endIndex']];
            }
        }

        return ['key' => $key, 'value' => null, 'endIndex' => $index + 1];
    }

    /**
     * Parses a primitive value
     *
     * @private
     * @version 1.0.0
     * @param string|null $value Value to parse
     * @return mixed Parsed value
     */
    private function parseValue(?string $value): mixed
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        $lower = strtolower($value);
        if ($lower === 'null') {
            return null;
        }
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }

        if ($this->isQuotedString($value)) {
            return StringUtil::unquote($value);
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float)$value : (int)$value;
        }

        return $value;
    }

    /**
     * Checks if value is a quoted string
     *
     * @private
     * @version 1.0.0
     * @param string $value Value to check
     * @return bool True if quoted string
     */
    private function isQuotedString(string $value): bool
    {
        return (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
               (str_starts_with($value, "'") && str_ends_with($value, "'"));
    }

    /**
     * Splits string by delimiter respecting quotes
     *
     * @private
     * @version 1.0.0
     * @param string $str String to split
     * @param string $delimiter Delimiter character
     * @return array<int, string> Split values
     */
    private function splitByDelimiter(string $str, string $delimiter): array
    {
        $result = [];
        $current = '';
        $inQuotes = false;
        $escapeNext = false;

        for ($i = 0; $i < strlen($str); $i++) {
            $char = $str[$i];

            if ($escapeNext) {
                $current .= $char;
                $escapeNext = false;
                continue;
            }

            if ($char === '\\') {
                $current .= $char;
                $escapeNext = true;
                continue;
            }

            if ($char === '"') {
                $inQuotes = !$inQuotes;
                $current .= $char;
                continue;
            }

            if (!$inQuotes && $char === $delimiter) {
                $result[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if ($current !== '' || !empty($result)) {
            $result[] = trim($current);
        }

        return $result;
    }

    /**
     * Gets the indentation level of a line
     *
     * @private
     * @version 1.0.0
     * @param string $line Line to check
     * @return int Indentation level
     */
    private function getIndentLevel(string $line): int
    {
        preg_match('/^(\s*)/', $line, $matches);
        return (int)(strlen($matches[1]) / $this->indent);
    }
}