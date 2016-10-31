<?php
namespace ZeroEvents\Serializers;

use ZeroEvents\SerializerInterface;

class JsonSerializer implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function encode(array $payload)
    {
        return array_map(function ($value) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $payload);
    }

    /**
     * @inheritdoc
     */
    public function decode(array $frames)
    {
        return array_map(function ($frame) {
            return json_decode($frame, true);
        }, $frames);
    }
}
