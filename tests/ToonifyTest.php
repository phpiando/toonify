<?php

namespace Phpiando\Toonify\Tests;

use PHPUnit\Framework\TestCase;
use Phpiando\Toonify\Toonify;

class ToonifyTest extends TestCase
{
    private Toonify $toonify;

    protected function setUp(): void
    {
        $this->toonify = new Toonify();
    }

    public function testJsonToToon(): void
    {
        $json = '{"name":"John","age":30}';
        $toon = $this->toonify->jsonToToon($json);
        $this->assertEquals("['name':'John';'age':30]", $toon);
    }

    public function testToonToJson(): void
    {
        $toon = "['name':'John';'age':30]";
        $json = $this->toonify->toonToJson($toon);
        $expected = json_encode(['name' => 'John', 'age' => 30]);
        $this->assertEquals($expected, $json);
    }

    public function testRoundTripJsonToToonToJson(): void
    {
        $original = [
            'name' => 'Alice',
            'age' => 25,
            'active' => true,
            'tags' => ['php', 'developer']
        ];
        
        $json = json_encode($original);
        $toon = $this->toonify->jsonToToon($json);
        $jsonBack = $this->toonify->toonToJson($toon);
        $result = json_decode($jsonBack, true);
        
        $this->assertEquals($original, $result);
    }

    public function testRoundTripToonToJsonToToon(): void
    {
        $originalToon = "['name':'Bob';'score':95.5;'active':t]";
        $json = $this->toonify->toonToJson($originalToon);
        $toonBack = $this->toonify->jsonToToon($json);
        
        // Decode both to compare data, not format
        $originalData = $this->toonify->decode($originalToon);
        $resultData = $this->toonify->decode($toonBack);
        
        $this->assertEquals($originalData, $resultData);
    }

    public function testEncode(): void
    {
        $data = ['hello' => 'world'];
        $result = $this->toonify->encode($data);
        $this->assertEquals("['hello':'world']", $result);
    }

    public function testDecode(): void
    {
        $toon = "['hello':'world']";
        $result = $this->toonify->decode($toon);
        $this->assertEquals(['hello' => 'world'], $result);
    }

    public function testStaticFromJson(): void
    {
        $json = '{"test":"value"}';
        $toon = Toonify::fromJson($json);
        $this->assertEquals("['test':'value']", $toon);
    }

    public function testStaticToJson(): void
    {
        $toon = "['test':'value']";
        $json = Toonify::toJson($toon);
        $this->assertEquals('{"test":"value"}', $json);
    }

    public function testInvalidJsonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->toonify->jsonToToon('invalid json {]');
    }

    public function testGetEncoder(): void
    {
        $encoder = $this->toonify->getEncoder();
        $this->assertInstanceOf(\Phpiando\Toonify\ToonEncoder::class, $encoder);
    }

    public function testGetDecoder(): void
    {
        $decoder = $this->toonify->getDecoder();
        $this->assertInstanceOf(\Phpiando\Toonify\ToonDecoder::class, $decoder);
    }

    public function testComplexDataRoundTrip(): void
    {
        $complex = [
            'user' => [
                'id' => 123,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'active' => true
            ],
            'settings' => [
                'theme' => 'dark',
                'notifications' => false,
                'values' => [1, 2, 3, 4, 5]
            ],
            'tags' => ['important', 'urgent'],
            'metadata' => null
        ];

        $json = json_encode($complex);
        $toon = $this->toonify->jsonToToon($json);
        $jsonBack = $this->toonify->toonToJson($toon);
        $result = json_decode($jsonBack, true);

        $this->assertEquals($complex, $result);
    }

    public function testToonToJsonWithPrettyPrint(): void
    {
        $toon = "['name':'John';'age':30]";
        $json = $this->toonify->toonToJson($toon, JSON_PRETTY_PRINT);
        $this->assertStringContainsString("\n", $json);
    }
}
