<?php
namespace ZeroEvents\Serializers;

use ZeroEvents\SerializerInterface;

class PhpSerializer implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function encode(array $payload)
    {
        return array_map(function ($value) {
            return serialize($value);
        }, $payload);
    }

    /**
     * @inheritdoc
     */
    public function decode(array $frames)
    {
        return array_map(function ($frame) {
            return unserialize($frame);
        }, $frames);
    }
}
