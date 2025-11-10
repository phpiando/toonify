<?php

declare(strict_types=1);

namespace Toonify\Tests;

use PHPUnit\Framework\TestCase;
use Toonify\Toonify;

class DecoderTest extends TestCase
{
    public function testDecodeSimpleObject(): void
    {
        $toon = "name: Roni Sommerfeld\nage: 37";
        $data = Toonify::decode($toon);

        $this->assertEquals('Roni Sommerfeld', $data['name']);
        $this->assertEquals(37, $data['age']);
    }

    public function testDecodePrimitiveArray(): void
    {
        $toon = "[3,]: 1,2,3";
        $data = Toonify::decode($toon);

        $this->assertEquals([1, 2, 3], $data);
    }

    public function testDecodeTabularArray(): void
    {
        $toon = "[2,]{id,name}:\n  1,Roni Sommerfeld\n  2,PHPiando";
        $data = Toonify::decode($toon);

        $this->assertCount(2, $data);
        $this->assertEquals(1, $data[0]['id']);
        $this->assertEquals('Roni Sommerfeld', $data[0]['name']);
        $this->assertEquals(2, $data[1]['id']);
        $this->assertEquals('PHPiando', $data[1]['name']);
    }

    public function testDecodeSpecialValues(): void
    {
        $toon = "null_val: null\ntrue_val: true\nfalse_val: false";
        $data = Toonify::decode($toon);

        $this->assertNull($data['null_val']);
        $this->assertTrue($data['true_val']);
        $this->assertFalse($data['false_val']);
    }

    public function testDecodeQuotedString(): void
    {
        $toon = 'message: "Hello, World!"';
        $data = Toonify::decode($toon);

        $this->assertEquals('Hello, World!', $data['message']);
    }

    public function testDecodeEmptyString(): void
    {
        $toon = 'empty: ""';
        $data = Toonify::decode($toon);

        $this->assertEquals('', $data['empty']);
    }

    public function testDecodeNestedObject(): void
    {
        $toon = "user:\n  name: Roni Sommerfeld\n  address:\n    city: Portugal\n    zip: 10001";
        $data = Toonify::decode($toon);

        $this->assertEquals('Roni Sommerfeld', $data['user']['name']);
        $this->assertEquals('Portugal', $data['user']['address']['city']);
        $this->assertEquals('10001', $data['user']['address']['zip']);
    }

    public function testDecodeToJsonString(): void
    {
        $toon = "name: Alice\nage: 30";
        $json = Toonify::toJsonString($toon);
        $data = json_decode($json, true);

        $this->assertEquals('Alice', $data['name']);
        $this->assertEquals(30, $data['age']);
    }

    public function testRoundTrip(): void
    {
        $original = [
            'users' => [
                ['id' => 1, 'name' => 'Roni Sommerfeld', 'active' => true],
                ['id' => 2, 'name' => 'PHPiando', 'active' => false]
            ]
        ];

        $toon = Toonify::encode($original);
        $decoded = Toonify::decode($toon);

        $this->assertEquals($original, $decoded);
    }

    public function testDecodeWithEscapedCharacters(): void
    {
        $toon = 'text: "Line 1\\nLine 2"';
        $data = Toonify::decode($toon);

        $this->assertStringContainsString("\n", $data['text']);
    }

    public function testDecodeListArray(): void
    {
        $toon = "[3,]:\n  - 42\n  - hello\n  - x: 1";
        $data = Toonify::decode($toon);

        $this->assertCount(3, $data);
        $this->assertEquals(42, $data[0]);
        $this->assertEquals('hello', $data[1]);
        $this->assertEquals(['x' => 1], $data[2]);
    }
}
