<?php

declare(strict_types=1);

namespace Toonify\Encoder;

use Toonify\Exception\EncodingException;
use Toonify\Util\StringUtil;

/**
 * Encoder: converts PHP arrays/objects to TOON format
 *
 * @author Roni Sommerfeld<roni@phpiando.com>
 * @version 1.0.0
 * @license MIT
 */
class Encoder
{
    /**
     * Field delimiter in primitive and tabular arrays
     *
     * @version 1.0.0
     * @var string
     */
    private string $delimiter;

    /**
     * Indentation level
     *
     * @version 1.0.0
     * @var int
     */
    private int $indent;

    /**
     * Length marker for tabular arrays
     *
     * @version 1.0.0
     * @var string
     */
    private string $lengthMarker;

    /**
     * Constructor
     *
     * @public
     * @version 1.0.0
     * @param array<string, mixed> $options Configuration options
     */
    public function __construct(array $options = [])
    {
        $this->delimiter = $options['delimiter'] ?? ',';
        $this->indent = $options['indent'] ?? 2;
        $this->lengthMarker = $options['lengthMarker'] ?? '';

        $this->validateOptions();
    }

    /**
     * Validates the provided options
     *
     * @private
     * @version 1.0.0
     * @throws EncodingException If options are invalid
     * @return void
     */
    private function validateOptions(): void
    {
        $validDelimiters = [',', "\t", '|'];
        if (!in_array($this->delimiter, $validDelimiters, true)) {
            throw new EncodingException('Invalid delimiter. Use ",", "\t" or "|"');
        }

        if ($this->indent < 0) {
            throw new EncodingException('Indent must be >= 0');
        }
    }

    /**
     * Converts PHP array/object to TOON
     *
     * @public
     * @version 1.0.0
     * @param mixed $data Data to convert
     * @throws EncodingException If JSON is invalid
     * @return string TOON string
     */
    public function encode(mixed $data): string
    {
        if (is_string($data) && StringUtil::isJsonString($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new EncodingException('Invalid JSON: ' . json_last_error_msg());
            }
            $data = $decoded;
        }

        return $this->encodeValue($data, 0);
    }

    /**
     * Encodes a value recursively
     *
     * @private
     * @version 1.0.0
     * @param mixed $value Value to encode
     * @param int $level Indentation level
     * @return string Encoded value
     */
    private function encodeValue(mixed $value, int $level): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            if (is_infinite($value) || is_nan($value)) {
                return 'null';
            }
            return (string)$value;
        }

        if (is_string($value)) {
            return StringUtil::quote($value, $this->delimiter);
        }

        if (is_array($value)) {
            return $this->encodeArray($value, $level);
        }

        if (is_object($value)) {
            return $this->encodeArray((array)$value, $level);
        }

        return 'null';
    }

    /**
     * Encodes an array
     *
     * @private
     * @version 1.0.0
     * @param array<mixed, mixed> $array Array to encode
     * @param int $level Indentation level
     * @return string Encoded array
     */
    private function encodeArray(array $array, int $level): string
    {
        if (empty($array)) {
            return '[0]:';
        }

        if ($this->isAssociativeArray($array)) {
            return $this->encodeObject($array, $level);
        }

        if ($this->isTabularArray($array)) {
            return $this->encodeTabularArray($array, $level);
        }

        if ($this->isPrimitiveArray($array)) {
            return $this->encodePrimitiveArray($array, $level);
        }

        return $this->encodeListArray($array, $level);
    }

    /**
     * Checks if it's an associative array
     *
     * @private
     * @version 1.0.0
     * @param array<mixed, mixed> $array Array to check
     * @return bool True if associative, false otherwise
     */
    private function isAssociativeArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Checks if it's a tabular array (uniform objects)
     *
     * @private
     * @version 1.0.0
     * @param array<mixed, mixed> $array Array to check
     * @return bool True if tabular, false otherwise
     */
    private function isTabularArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        if (!is_array($array[0]) || !$this->isAssociativeArray($array[0])) {
            return false;
        }

        $firstKeys = array_keys($array[0]);

        foreach ($array as $item) {
            if (!is_array($item) || !$this->isAssociativeArray($item)) {
                return false;
            }

            if (array_keys($item) !== $firstKeys) {
                return false;
            }

            foreach ($item as $value) {
                if (is_array($value) || is_object($value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks if it's an array of primitives
     *
     * @private
     * @version 1.0.0
     * @param array<mixed, mixed> $array Array to check
     * @return bool True if primitive array, false otherwise
     */
    private function isPrimitiveArray(array $array): bool
    {
        foreach ($array as $item) {
            if (is_array($item) || is_object($item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Encodes an object (associative array)
     *
     * @private
     * @version 1.0.0
     * @param array<string, mixed> $object Object to encode
     * @param int $level Indentation level
     * @return string Encoded object
     */
    private function encodeObject(array $object, int $level): string
    {
        $lines = [];
        $indentStr = str_repeat(' ', $level * $this->indent);

        foreach ($object as $key => $value) {
            if (is_array($value) && !$this->isAssociativeArray($value)) {
                $lines[] = $this->encodeArrayWithKey($key, $value, $level);
                continue;
            }

            if (is_array($value) || is_object($value)) {
                $lines[] = $indentStr . $key . ':';
                $lines[] = $this->encodeValue($value, $level + 1);
                continue;
            }

            $lines[] = $indentStr . $key . ': ' . $this->encodeValue($value, $level);
        }

        return implode("\n", $lines);
    }

    /**
     * Encodes array with key (format: key[N]:)
     *
     * @private
     * @version 1.0.0
     * @param string $key Array key name
     * @param array<int, mixed> $array Array to encode
     * @param int $level Indentation level
     * @return string Encoded named array
     */
    private function encodeArrayWithKey(string $key, array $array, int $level): string
    {
        $count = count($array);
        $indentStr = str_repeat(' ', $level * $this->indent);

        if ($this->isTabularArray($array)) {
            return $this->encodeNamedTabularArray($key, $array, $level, $count, $indentStr);
        }

        if ($this->isPrimitiveArray($array)) {
            return $this->encodeNamedPrimitiveArray($key, $array, $level, $count, $indentStr);
        }

        return $this->encodeNamedListArray($key, $array, $level, $count, $indentStr);
    }

    /**
     * Encodes a named tabular array
     *
     * @private
     * @version 1.0.0
     * @param string $key Array key name
     * @param array<int, array<string, mixed>> $array Tabular array to encode
     * @param int $level Indentation level
     * @param int $count Number of items
     * @param string $indentStr Indentation string
     * @return string Encoded named tabular array
     */
    private function encodeNamedTabularArray(string $key, array $array, int $level, int $count, string $indentStr): string
    {
        $keys = array_keys($array[0]);
        $delimiterSymbol = $this->getDelimiterSymbol();

        $header = $indentStr . $key . '[' . $this->lengthMarker . $count . $delimiterSymbol . ']';
        $header .= '{' . implode(',', $keys) . '}:';

        $lines = [$header];
        $dataIndentStr = str_repeat(' ', ($level + 1) * $this->indent);

        foreach ($array as $item) {
            $values = [];
            foreach ($keys as $itemKey) {
                $values[] = $this->encodeValue($item[$itemKey], $level + 1);
            }
            $lines[] = $dataIndentStr . implode($this->delimiter, $values);
        }

        return implode("\n", $lines);
    }

    /**
     * Encodes a named primitive array
     *
     * @private
     * @version 1.0.0
     * @param string $key Array key name
     * @param array<int, mixed> $array Primitive array to encode
     * @param int $level Indentation level
     * @param int $count Number of items
     * @param string $indentStr Indentation string
     * @return string Encoded named primitive array
     */
    private function encodeNamedPrimitiveArray(string $key, array $array, int $level, int $count, string $indentStr): string
    {
        $delimiterSymbol = $this->getDelimiterSymbol();
        $values = array_map(fn($item) => $this->encodeValue($item, 0), $array);

        return $indentStr . $key . '[' . $this->lengthMarker . $count . $delimiterSymbol . ']: '
             . implode($this->delimiter, $values);
    }

    /**
     * Encodes a named list array
     *
     * @private
     * @version 1.0.0
     * @param string $key Array key name
     * @param array<int, mixed> $array List array to encode
     * @param int $level Indentation level
     * @param int $count Number of items
     * @param string $indentStr Indentation string
     * @return string Encoded named list array
     */
    private function encodeNamedListArray(string $key, array $array, int $level, int $count, string $indentStr): string
    {
        $header = $indentStr . $key . '[' . $this->lengthMarker . $count . ']:';
        $lines = [$header];
        $itemIndentStr = str_repeat(' ', ($level + 1) * $this->indent);

        foreach ($array as $item) {
            $lines[] = $this->encodeListItem($item, $level + 1, $itemIndentStr);
        }

        return implode("\n", $lines);
    }

    /**
     * Encodes tabular array
     *
     * @private
     * @version 1.0.0
     * @param array<int, array<string, mixed>> $array Tabular array to encode
     * @param int $level Indentation level
     * @return string Encoded tabular array
     */
    private function encodeTabularArray(array $array, int $level): string
    {
        $count = count($array);
        $keys = array_keys($array[0]);
        $indentStr = str_repeat(' ', $level * $this->indent);
        $delimiterSymbol = $this->getDelimiterSymbol();

        $header = $indentStr . '[' . $this->lengthMarker . $count . $delimiterSymbol . ']';
        $header .= '{' . implode(',', $keys) . '}:';

        $lines = [$header];
        $dataIndentStr = str_repeat(' ', ($level + 1) * $this->indent);

        foreach ($array as $item) {
            $values = [];
            foreach ($keys as $key) {
                $values[] = $this->encodeValue($item[$key], $level + 1);
            }
            $lines[] = $dataIndentStr . implode($this->delimiter, $values);
        }

        return implode("\n", $lines);
    }

    /**
     * Encodes primitive array
     *
     * @private
     * @version 1.0.0
     * @param array<int, mixed> $array Primitive array to encode
     * @param int $level Indentation level
     * @return string Encoded primitive array
     */
    private function encodePrimitiveArray(array $array, int $level): string
    {
        $count = count($array);
        $delimiterSymbol = $this->getDelimiterSymbol();
        $indentStr = str_repeat(' ', $level * $this->indent);

        $values = array_map(fn($item) => $this->encodeValue($item, 0), $array);

        return $indentStr . '[' . $this->lengthMarker . $count . $delimiterSymbol . ']: '
             . implode($this->delimiter, $values);
    }

    /**
     * Encodes mixed array (list)
     *
     * @private
     * @version 1.0.0
     * @param array<int, mixed> $array List array to encode
     * @param int $level Indentation level
     * @return string Encoded list array
     */
    private function encodeListArray(array $array, int $level): string
    {
        $count = count($array);
        $indentStr = str_repeat(' ', $level * $this->indent);
        $header = $indentStr . '[' . $this->lengthMarker . $count . $this->getDelimiterSymbol() . ']:';

        $lines = [$header];
        $itemIndentStr = str_repeat(' ', ($level + 1) * $this->indent);

        foreach ($array as $item) {
            $lines[] = $this->encodeListItem($item, $level + 1, $itemIndentStr);
        }

        return implode("\n", $lines);
    }

    /**
     * Encodes a list item
     *
     * @private
     * @version 1.0.0
     * @param mixed $item Item to encode
     * @param int $level Indentation level
     * @param string $itemIndentStr Item indentation string
     * @return string Encoded list item
     */
    private function encodeListItem(mixed $item, int $level, string $itemIndentStr): string
    {
        if (!is_array($item) || !$this->isAssociativeArray($item)) {
            return $this->encodeNonObjectListItem($item, $level, $itemIndentStr);
        }

        $hasComplexValues = $this->hasComplexValues($item);

        if (!$hasComplexValues && count($item) <= 5) {
            return $this->encodeSimpleInlineObject($item, $level, $itemIndentStr);
        }

        return $this->encodeComplexObject($item, $level, $itemIndentStr);
    }

    /**
     * Checks if array has complex values (arrays or objects)
     *
     * @private
     * @version 1.0.0
     * @param array<string, mixed> $item Array to check
     * @return bool True if has complex values, false otherwise
     */
    private function hasComplexValues(array $item): bool
    {
        foreach ($item as $v) {
            if (is_array($v) || is_object($v)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Encodes a simple inline object
     *
     * @private
     * @version 1.0.0
     * @param array<string, mixed> $item Object to encode
     * @param int $level Indentation level
     * @param string $itemIndentStr Item indentation string
     * @return string Encoded inline object
     */
    private function encodeSimpleInlineObject(array $item, int $level, string $itemIndentStr): string
    {
        $parts = [];
        foreach ($item as $k => $v) {
            $parts[] = $k . ': ' . $this->encodeValue($v, $level);
        }
        return $itemIndentStr . '- ' . implode(', ', $parts);
    }

    /**
     * Encodes a complex object with multiple lines
     *
     * @private
     * @version 1.0.0
     * @param array<string, mixed> $item Object to encode
     * @param int $level Indentation level
     * @param string $itemIndentStr Item indentation string
     * @return string Encoded complex object
     */
    private function encodeComplexObject(array $item, int $level, string $itemIndentStr): string
    {
        $continuationIndent = str_repeat(' ', ($level + 1) * $this->indent);
        $result = '';
        $isFirst = true;

        foreach ($item as $k => $v) {
            if ($isFirst) {
                $result = $this->encodeFirstObjectKey($k, $v, $level, $itemIndentStr);
                $isFirst = false;
                continue;
            }

            $result .= $this->encodeContinuationKey($k, $v, $level, $continuationIndent);
        }

        return $result;
    }

    /**
     * Encodes the first key of an object
     *
     * @private
     * @version 1.0.0
     * @param string $key Key name
     * @param mixed $value Key value
     * @param int $level Indentation level
     * @param string $itemIndentStr Item indentation string
     * @return string Encoded first key
     */
    private function encodeFirstObjectKey(string $key, mixed $value, int $level, string $itemIndentStr): string
    {
        if (is_array($value) || is_object($value)) {
            return $itemIndentStr . '- ' . $key . ':'
                 . "\n" . $this->encodeValue($value, $level + 2);
        }

        return $itemIndentStr . '- ' . $key . ': ' . $this->encodeValue($value, $level + 1);
    }

    /**
     * Encodes a continuation key of an object
     *
     * @private
     * @version 1.0.0
     * @param string $key Key name
     * @param mixed $value Key value
     * @param int $level Indentation level
     * @param string $continuationIndent Continuation indentation string
     * @return string Encoded continuation key
     */
    private function encodeContinuationKey(string $key, mixed $value, int $level, string $continuationIndent): string
    {
        if (is_array($value) || is_object($value)) {
            return "\n" . $continuationIndent . $key . ':'
                 . "\n" . $this->encodeValue($value, $level + 2);
        }

        return "\n" . $continuationIndent . $key . ': ' . $this->encodeValue($value, $level + 1);
    }

    /**
     * Encodes a non-object list item
     *
     * @private
     * @version 1.0.0
     * @param mixed $item Item to encode
     * @param int $level Indentation level
     * @param string $itemIndentStr Item indentation string
     * @return string Encoded non-object item
     */
    private function encodeNonObjectListItem(mixed $item, int $level, string $itemIndentStr): string
    {
        if (is_array($item) || is_object($item)) {
            return $itemIndentStr . '-' . "\n" . $this->encodeValue($item, $level + 1);
        }

        return $itemIndentStr . '- ' . $this->encodeValue($item, $level);
    }

    /**
     * Returns the delimiter symbol for the header
     *
     * @private
     * @version 1.0.0
     * @return string Delimiter symbol
     */
    private function getDelimiterSymbol(): string
    {
        return match($this->delimiter) {
            ',' => ',',
            "\t" => "\t",
            '|' => '|',
            default => ','
        };
    }
}