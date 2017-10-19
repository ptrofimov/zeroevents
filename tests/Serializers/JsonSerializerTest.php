<?php
namespace ZeroEvents\Serializers;

class JsonSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function serializer()
    {
        return new JsonSerializer();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('ZeroEvents\Serializers\JsonSerializer', $this->serializer());
    }

    public function testSerialize()
    {
        $this->assertSame(
            [
                'null',
                '1',
                'true',
                '"string"',
                '[1,2,3]',
                '{"key":"value"}',
                '"при/вет"',
                '{}',
            ],
            $this->serializer()->serialize(
                [null, 1, true, 'string', [1, 2, 3], ['key' => 'value'], 'при/вет', new \stdClass]
            )
        );
    }

    public function testUnserialize()
    {
        $this->assertSame(
            [null, 1, true, 'string', [1, 2, 3], ['key' => 'value'], 'при/вет', []],
            $this->serializer()->unserialize([
                'null',
                '1',
                'true',
                '"string"',
                '[1,2,3]',
                '{"key":"value"}',
                '"при/вет"',
                '{}',
            ])
        );
    }
}
