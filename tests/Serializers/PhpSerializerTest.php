<?php
namespace ZeroEvents\Serializers;

class PhpSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function serializer()
    {
        return new PhpSerializer();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('ZeroEvents\Serializers\PhpSerializer', $this->serializer());
    }

    public function testEncode()
    {
        $this->assertSame(
            [
                'N;',
                'i:1;',
                'b:1;',
                's:6:"string";',
                'a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}',
                'a:1:{s:3:"key";s:5:"value";}',
                's:13:"при/вет";',
                'O:8:"stdClass":0:{}',
            ],
            $this->serializer()->encode(
                [null, 1, true, 'string', [1, 2, 3], ['key' => 'value'], 'при/вет', new \stdClass]
            )
        );
    }

    public function testDecode()
    {
        $this->assertEquals(
            [null, 1, true, 'string', [1, 2, 3], ['key' => 'value'], 'при/вет', new \stdClass],
            $this->serializer()->decode([
                'N;',
                'i:1;',
                'b:1;',
                's:6:"string";',
                'a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}',
                'a:1:{s:3:"key";s:5:"value";}',
                's:13:"при/вет";',
                'O:8:"stdClass":0:{}',
            ])
        );
    }
}
