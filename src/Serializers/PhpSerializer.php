<?php
namespace ZeroEvents\Serializers;

use ZeroEvents\SerializerInterface;

class PhpSerializer implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize(array $payload)
    {
        return array_map(function ($value) {
            return serialize($value);
        }, $payload);
    }

    /**
     * @inheritdoc
     */
    public function unserialize(array $frames)
    {
        return array_map(function ($frame) {
            return unserialize($frame);
        }, $frames);
    }
}
