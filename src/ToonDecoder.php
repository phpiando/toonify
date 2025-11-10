<?php

namespace Phpiando\Toonify;

/**
 * ToonDecoder - Converts TOON format to PHP arrays/JSON
 */
class ToonDecoder
{
    private string $input;
    private int $position;
    private int $length;

    /**
     * Decode TOON format to PHP data
     *
     * @param string $toon The TOON-encoded string
     * @return mixed The decoded data
     * @throws \RuntimeException If parsing fails
     */
    public function decode(string $toon)
    {
        $this->input = $toon;
        $this->position = 0;
        $this->length = strlen($toon);

        $result = $this->parseValue();
        $this->skipWhitespace();

        if ($this->position < $this->length) {
            throw new \RuntimeException('Unexpected characters after parsing at position ' . $this->position);
        }

        return $result;
    }

    /**
     * Parse a value from the current position
     *
     * @return mixed The parsed value
     * @throws \RuntimeException If parsing fails
     */
    private function parseValue()
    {
        $this->skipWhitespace();

        if ($this->position >= $this->length) {
            throw new \RuntimeException('Unexpected end of input');
        }

        $char = $this->input[$this->position];

        // Object
        if ($char === '[') {
            return $this->parseObject();
        }

        // Array
        if ($char === '(') {
            return $this->parseArray();
        }

        // String
        if ($char === "'") {
            return $this->parseString();
        }

        // Boolean true
        if ($char === 't') {
            $this->position++;
            return true;
        }

        // Boolean false
        if ($char === 'f') {
            $this->position++;
            return false;
        }

        // Null
        if ($char === 'n') {
            $this->position++;
            return null;
        }

        // Number (int or float)
        if ($char === '-' || ctype_digit($char)) {
            return $this->parseNumber();
        }

        throw new \RuntimeException('Unexpected character "' . $char . '" at position ' . $this->position);
    }

    /**
     * Parse an object (associative array)
     *
     * @return array The parsed object
     * @throws \RuntimeException If parsing fails
     */
    private function parseObject(): array
    {
        $this->position++; // Skip '['
        $this->skipWhitespace();

        $object = [];

        // Empty object
        if ($this->position < $this->length && $this->input[$this->position] === ']') {
            $this->position++;
            return $object;
        }

        while ($this->position < $this->length) {
            $this->skipWhitespace();

            // Parse key (must be a string)
            if ($this->input[$this->position] !== "'") {
                throw new \RuntimeException('Expected string key at position ' . $this->position);
            }
            $key = $this->parseString();

            $this->skipWhitespace();

            // Expect ':'
            if ($this->position >= $this->length || $this->input[$this->position] !== ':') {
                throw new \RuntimeException('Expected ":" after key at position ' . $this->position);
            }
            $this->position++;

            // Parse value
            $value = $this->parseValue();
            $object[$key] = $value;

            $this->skipWhitespace();

            // Check for continuation or end
            if ($this->position >= $this->length) {
                throw new \RuntimeException('Unexpected end of input while parsing object');
            }

            $char = $this->input[$this->position];
            if ($char === ']') {
                $this->position++;
                break;
            } elseif ($char === ';') {
                $this->position++;
            } else {
                throw new \RuntimeException('Expected ";" or "]" at position ' . $this->position);
            }
        }

        return $object;
    }

    /**
     * Parse an array (indexed array)
     *
     * @return array The parsed array
     * @throws \RuntimeException If parsing fails
     */
    private function parseArray(): array
    {
        $this->position++; // Skip '('
        $this->skipWhitespace();

        $array = [];

        // Empty array
        if ($this->position < $this->length && $this->input[$this->position] === ')') {
            $this->position++;
            return $array;
        }

        while ($this->position < $this->length) {
            $value = $this->parseValue();
            $array[] = $value;

            $this->skipWhitespace();

            // Check for continuation or end
            if ($this->position >= $this->length) {
                throw new \RuntimeException('Unexpected end of input while parsing array');
            }

            $char = $this->input[$this->position];
            if ($char === ')') {
                $this->position++;
                break;
            } elseif ($char === ',') {
                $this->position++;
            } else {
                throw new \RuntimeException('Expected "," or ")" at position ' . $this->position);
            }
        }

        return $array;
    }

    /**
     * Parse a string value
     *
     * @return string The parsed string
     * @throws \RuntimeException If parsing fails
     */
    private function parseString(): string
    {
        $this->position++; // Skip opening quote
        $result = '';
        $escaped = false;

        while ($this->position < $this->length) {
            $char = $this->input[$this->position];

            if ($escaped) {
                // Handle escaped characters
                $result .= $char;
                $escaped = false;
            } elseif ($char === '\\') {
                $escaped = true;
            } elseif ($char === "'") {
                $this->position++; // Skip closing quote
                return $result;
            } else {
                $result .= $char;
            }

            $this->position++;
        }

        throw new \RuntimeException('Unterminated string');
    }

    /**
     * Parse a number (int or float)
     *
     * @return int|float The parsed number
     * @throws \RuntimeException If parsing fails
     */
    private function parseNumber()
    {
        $start = $this->position;
        $hasDecimal = false;

        // Handle negative sign
        if ($this->input[$this->position] === '-') {
            $this->position++;
        }

        // Parse digits
        while ($this->position < $this->length) {
            $char = $this->input[$this->position];

            if (ctype_digit($char)) {
                $this->position++;
            } elseif ($char === '.' && !$hasDecimal) {
                $hasDecimal = true;
                $this->position++;
            } else {
                break;
            }
        }

        $numberStr = substr($this->input, $start, $this->position - $start);

        if ($hasDecimal) {
            return (float)$numberStr;
        } else {
            return (int)$numberStr;
        }
    }

    /**
     * Skip whitespace characters
     */
    private function skipWhitespace(): void
    {
        while ($this->position < $this->length && ctype_space($this->input[$this->position])) {
            $this->position++;
        }
    }
}
