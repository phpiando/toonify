<?php

namespace Phpiando\Toonify;

/**
 * ToonEncoder - Converts JSON/PHP arrays to TOON format
 * 
 * TOON Format Specification:
 * - Objects: key:value separated by semicolons (k1:v1;k2:v2)
 * - Arrays: values separated by commas (v1,v2,v3)
 * - Strings: enclosed in single quotes ('string')
 * - Numbers: no quotes (123, 45.67)
 * - Booleans: t for true, f for false
 * - Null: n
 * - Nested objects: enclosed in brackets [k1:v1;k2:v2]
 * - Nested arrays: enclosed in parentheses (v1,v2,v3)
 */
class ToonEncoder
{
    /**
     * Encode PHP data to TOON format
     *
     * @param mixed $data The data to encode
     * @return string The TOON-encoded string
     */
    public function encode($data): string
    {
        return $this->encodeValue($data);
    }

    /**
     * Encode a value based on its type
     *
     * @param mixed $value The value to encode
     * @return string The encoded value
     */
    private function encodeValue($value): string
    {
        if ($value === null) {
            return 'n';
        }

        if (is_bool($value)) {
            return $value ? 't' : 'f';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_string($value)) {
            return $this->encodeString($value);
        }

        if (is_array($value)) {
            return $this->encodeArray($value);
        }

        if (is_object($value)) {
            // Empty objects should be encoded as []
            if (empty((array)$value)) {
                return '[]';
            }
            return $this->encodeArray((array)$value);
        }

        throw new \InvalidArgumentException('Unsupported data type: ' . gettype($value));
    }

    /**
     * Encode a string value
     *
     * @param string $value The string to encode
     * @return string The encoded string
     */
    private function encodeString(string $value): string
    {
        // Escape special characters
        $value = str_replace(['\\', "'", ':', ';', ',', '[', ']', '(', ')'], 
                           ['\\\\', "\\'", '\\:', '\\;', '\\,', '\\[', '\\]', '\\(', '\\)'], 
                           $value);
        return "'" . $value . "'";
    }

    /**
     * Encode an array
     *
     * @param array $array The array to encode
     * @return string The encoded array
     */
    private function encodeArray(array $array): string
    {
        if (empty($array)) {
            return $this->isAssociative($array) ? '[]' : '()';
        }

        if ($this->isAssociative($array)) {
            return $this->encodeObject($array);
        } else {
            return $this->encodeList($array);
        }
    }

    /**
     * Encode an associative array (object)
     *
     * @param array $array The associative array to encode
     * @return string The encoded object
     */
    private function encodeObject(array $array): string
    {
        $pairs = [];
        foreach ($array as $key => $value) {
            $encodedKey = $this->encodeString((string)$key);
            $encodedValue = $this->encodeValue($value);
            $pairs[] = $encodedKey . ':' . $encodedValue;
        }
        return '[' . implode(';', $pairs) . ']';
    }

    /**
     * Encode an indexed array (list)
     *
     * @param array $array The indexed array to encode
     * @return string The encoded list
     */
    private function encodeList(array $array): string
    {
        $values = array_map([$this, 'encodeValue'], array_values($array));
        return '(' . implode(',', $values) . ')';
    }

    /**
     * Check if an array is associative
     *
     * @param array $array The array to check
     * @return bool True if associative, false otherwise
     */
    private function isAssociative(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
