<?php
namespace ZeroEvents\Serializers;

class StringSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function serializer()
    {
        return new StringSerializer();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('ZeroEvents\Serializers\StringSerializer', $this->serializer());
    }

    public function testSerialize()
    {
        $this->assertSame(
            ['string', 'при/вет'],
            $this->serializer()->serialize(['string', 'при/вет'])
        );
    }

    public function testUnserialize()
    {
        $this->assertSame(
            ['string', 'при/вет'],
            $this->serializer()->unserialize(['string', 'при/вет'])
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Only string arguments supported
     */
    public function testInvalidArgumentException()
    {
        $this->serializer()->serialize([['key' => 'value'], 1, null, new \stdClass]);
    }
}
