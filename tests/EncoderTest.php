<?php

declare(strict_types=1);

namespace Toonify\Tests;

use PHPUnit\Framework\TestCase;
use Toonify\Toonify;

class EncoderTest extends TestCase
{
    public function testEncodeSimpleObject(): void
    {
        $data = ['name' => 'Roni Sommerfeld', 'age' => 37];
        $toon = Toonify::encode($data);

        $this->assertStringContainsString('name: Roni Sommerfeld', $toon);
        $this->assertStringContainsString('age: 37', $toon);
    }

    public function testEncodePrimitiveArray(): void
    {
        $data = [1, 2, 3, 4, 5];
        $toon = Toonify::encode($data);

        $this->assertStringContainsString('[5,]:', $toon);
        $this->assertStringContainsString('1,2,3,4,5', $toon);
    }

    public function testEncodeTabularArray(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Roni Sommerfeld'],
            ['id' => 2, 'name' => 'PHPiando']
        ];
        $toon = Toonify::encode($data);

        $this->assertStringContainsString('[2,]', $toon);
        $this->assertStringContainsString('{id,name}', $toon);
        $this->assertStringContainsString('1,Roni Sommerfeld', $toon);
        $this->assertStringContainsString('2,PHPiando', $toon);
    }

    public function testEncodeWithTabDelimiter(): void
    {
        $data = [['id' => 1, 'name' => 'Roni Sommerfeld']];
        $toon = Toonify::encode($data, ['delimiter' => "\t"]);

        $this->assertStringContainsString("[1\t]", $toon);
    }

    public function testEncodeWithPipeDelimiter(): void
    {
        $data = [['id' => 1, 'name' => 'Roni Sommerfeld']];
        $toon = Toonify::encode($data, ['delimiter' => '|']);

        $this->assertStringContainsString('[1|]', $toon);
    }

    public function testEncodeNestedObject(): void
    {
        $data = [
            'user' => [
                'name' => 'Roni Sommerfeld',
                'address' => [
                    'city' => 'Portugal',
                    'zip' => '10001'
                ]
            ]
        ];
        $toon = Toonify::encode($data);

        $this->assertStringContainsString('user:', $toon);
        $this->assertStringContainsString('name: Roni Sommerfeld', $toon);
        $this->assertStringContainsString('address:', $toon);
        $this->assertStringContainsString('city: Portugal', $toon);
    }

    public function testEncodeSpecialValues(): void
    {
        $data = [
            'null_value' => null,
            'true_value' => true,
            'false_value' => false,
            'number' => 42,
            'float' => 3.14
        ];
        $toon = Toonify::encode($data);

        $this->assertStringContainsString('null_value: null', $toon);
        $this->assertStringContainsString('true_value: true', $toon);
        $this->assertStringContainsString('false_value: false', $toon);
        $this->assertStringContainsString('number: 42', $toon);
        $this->assertStringContainsString('float: 3.14', $toon);
    }

    public function testEncodeStringsWithSpaces(): void
    {
        $data = ['message' => 'Hello World'];
        $toon = Toonify::encode($data);

        // String com espaços internos não precisa de aspas
        $this->assertStringContainsString('message: Hello World', $toon);
    }

    public function testEncodeEmptyString(): void
    {
        $data = ['empty' => ''];
        $toon = Toonify::encode($data);

        // String vazia precisa de aspas
        $this->assertStringContainsString('empty: ""', $toon);
    }

    public function testEncodeFromJsonString(): void
    {
        $json = '{"name": "Roni Sommerfeld", "age": 37}';
        $toon = Toonify::fromJsonString($json);

        $this->assertStringContainsString('name: Roni Sommerfeld', $toon);
        $this->assertStringContainsString('age: 37', $toon);
    }
}
