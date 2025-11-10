<?php

namespace Phpiando\Toonify\Tests;

use PHPUnit\Framework\TestCase;
use Phpiando\Toonify\ToonEncoder;

class ToonEncoderTest extends TestCase
{
    private ToonEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new ToonEncoder();
    }

    public function testEncodeNull(): void
    {
        $this->assertEquals('n', $this->encoder->encode(null));
    }

    public function testEncodeBoolean(): void
    {
        $this->assertEquals('t', $this->encoder->encode(true));
        $this->assertEquals('f', $this->encoder->encode(false));
    }

    public function testEncodeInteger(): void
    {
        $this->assertEquals('42', $this->encoder->encode(42));
        $this->assertEquals('-10', $this->encoder->encode(-10));
        $this->assertEquals('0', $this->encoder->encode(0));
    }

    public function testEncodeFloat(): void
    {
        $this->assertEquals('3.14', $this->encoder->encode(3.14));
        $this->assertEquals('-2.5', $this->encoder->encode(-2.5));
    }

    public function testEncodeString(): void
    {
        $this->assertEquals("'hello'", $this->encoder->encode('hello'));
        $this->assertEquals("'Hello World'", $this->encoder->encode('Hello World'));
        $this->assertEquals("''", $this->encoder->encode(''));
    }

    public function testEncodeStringWithEscaping(): void
    {
        $this->assertEquals("'It\\'s working'", $this->encoder->encode("It's working"));
        $this->assertEquals("'key\\:value'", $this->encoder->encode("key:value"));
        $this->assertEquals("'item1\\,item2'", $this->encoder->encode("item1,item2"));
    }

    public function testEncodeEmptyArray(): void
    {
        $this->assertEquals('()', $this->encoder->encode([]));
    }

    public function testEncodeSimpleArray(): void
    {
        $this->assertEquals("(1,2,3)", $this->encoder->encode([1, 2, 3]));
        $this->assertEquals("('a','b','c')", $this->encoder->encode(['a', 'b', 'c']));
    }

    public function testEncodeEmptyObject(): void
    {
        $this->assertEquals('[]', $this->encoder->encode((object)[]));
    }

    public function testEncodeSimpleObject(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $result = $this->encoder->encode($data);
        $this->assertEquals("['name':'John';'age':30]", $result);
    }

    public function testEncodeNestedArray(): void
    {
        $data = [1, [2, 3], 4];
        $result = $this->encoder->encode($data);
        $this->assertEquals("(1,(2,3),4)", $result);
    }

    public function testEncodeNestedObject(): void
    {
        $data = [
            'user' => [
                'name' => 'Alice',
                'age' => 25
            ]
        ];
        $result = $this->encoder->encode($data);
        $this->assertEquals("['user':['name':'Alice';'age':25]]", $result);
    }

    public function testEncodeComplexStructure(): void
    {
        $data = [
            'name' => 'Bob',
            'active' => true,
            'score' => 95.5,
            'tags' => ['php', 'developer'],
            'metadata' => [
                'created' => '2024-01-01',
                'verified' => false
            ]
        ];
        
        $result = $this->encoder->encode($data);
        $this->assertStringContainsString("'name':'Bob'", $result);
        $this->assertStringContainsString("'active':t", $result);
        $this->assertStringContainsString("'score':95.5", $result);
        $this->assertStringContainsString("'tags':('php','developer')", $result);
    }

    public function testEncodeMixedTypes(): void
    {
        $data = ['string', 42, true, null, 3.14];
        $result = $this->encoder->encode($data);
        $this->assertEquals("('string',42,t,n,3.14)", $result);
    }
}
