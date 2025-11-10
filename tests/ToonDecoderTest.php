<?php

namespace Phpiando\Toonify\Tests;

use PHPUnit\Framework\TestCase;
use Phpiando\Toonify\ToonDecoder;

class ToonDecoderTest extends TestCase
{
    private ToonDecoder $decoder;

    protected function setUp(): void
    {
        $this->decoder = new ToonDecoder();
    }

    public function testDecodeNull(): void
    {
        $this->assertNull($this->decoder->decode('n'));
    }

    public function testDecodeBoolean(): void
    {
        $this->assertTrue($this->decoder->decode('t'));
        $this->assertFalse($this->decoder->decode('f'));
    }

    public function testDecodeInteger(): void
    {
        $this->assertEquals(42, $this->decoder->decode('42'));
        $this->assertEquals(-10, $this->decoder->decode('-10'));
        $this->assertEquals(0, $this->decoder->decode('0'));
    }

    public function testDecodeFloat(): void
    {
        $this->assertEquals(3.14, $this->decoder->decode('3.14'));
        $this->assertEquals(-2.5, $this->decoder->decode('-2.5'));
    }

    public function testDecodeString(): void
    {
        $this->assertEquals('hello', $this->decoder->decode("'hello'"));
        $this->assertEquals('Hello World', $this->decoder->decode("'Hello World'"));
        $this->assertEquals('', $this->decoder->decode("''"));
    }

    public function testDecodeStringWithEscaping(): void
    {
        $this->assertEquals("It's working", $this->decoder->decode("'It\\'s working'"));
        $this->assertEquals("key:value", $this->decoder->decode("'key\\:value'"));
        $this->assertEquals("item1,item2", $this->decoder->decode("'item1\\,item2'"));
    }

    public function testDecodeEmptyArray(): void
    {
        $this->assertEquals([], $this->decoder->decode('()'));
    }

    public function testDecodeSimpleArray(): void
    {
        $this->assertEquals([1, 2, 3], $this->decoder->decode('(1,2,3)'));
        $this->assertEquals(['a', 'b', 'c'], $this->decoder->decode("('a','b','c')"));
    }

    public function testDecodeEmptyObject(): void
    {
        $this->assertEquals([], $this->decoder->decode('[]'));
    }

    public function testDecodeSimpleObject(): void
    {
        $result = $this->decoder->decode("['name':'John';'age':30]");
        $this->assertEquals(['name' => 'John', 'age' => 30], $result);
    }

    public function testDecodeNestedArray(): void
    {
        $result = $this->decoder->decode('(1,(2,3),4)');
        $this->assertEquals([1, [2, 3], 4], $result);
    }

    public function testDecodeNestedObject(): void
    {
        $result = $this->decoder->decode("['user':['name':'Alice';'age':25]]");
        $expected = [
            'user' => [
                'name' => 'Alice',
                'age' => 25
            ]
        ];
        $this->assertEquals($expected, $result);
    }

    public function testDecodeComplexStructure(): void
    {
        $toon = "['name':'Bob';'active':t;'score':95.5;'tags':('php','developer');'metadata':['created':'2024-01-01';'verified':f]]";
        $result = $this->decoder->decode($toon);
        
        $this->assertEquals('Bob', $result['name']);
        $this->assertTrue($result['active']);
        $this->assertEquals(95.5, $result['score']);
        $this->assertEquals(['php', 'developer'], $result['tags']);
        $this->assertEquals('2024-01-01', $result['metadata']['created']);
        $this->assertFalse($result['metadata']['verified']);
    }

    public function testDecodeMixedTypes(): void
    {
        $result = $this->decoder->decode("('string',42,t,n,3.14)");
        $this->assertEquals(['string', 42, true, null, 3.14], $result);
    }

    public function testDecodeWithWhitespace(): void
    {
        $result = $this->decoder->decode("[ 'name' : 'John' ; 'age' : 30 ]");
        $this->assertEquals(['name' => 'John', 'age' => 30], $result);
    }

    public function testDecodeInvalidToonThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->decoder->decode("['invalid'");
    }

    public function testDecodeUnterminatedStringThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->decoder->decode("'unterminated");
    }
}
