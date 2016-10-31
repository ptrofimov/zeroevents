<?php
namespace ZeroEvents\Serializers;

use InvalidArgumentException;
use ZeroEvents\SerializerInterface;

class StringSerializer implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize(array $payload)
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return $value;
            }
            throw new InvalidArgumentException('Only string arguments supported');
        }, $payload);
    }

    /**
     * @inheritdoc
     */
    public function unserialize(array $frames)
    {
        return array_map(function ($frame) {
            return $frame;
        }, $frames);
    }
}
