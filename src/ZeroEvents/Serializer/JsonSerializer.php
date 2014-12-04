<?php
namespace ZeroEvents\Serializer;

class JsonSerializer implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize($event, array $payload)
    {
        return array_merge([$event], array_map('json_encode', $payload));
    }

    /**
     * @inheritdoc
     */
    public function unserialize(array $frames)
    {
        return [
            'event' => array_shift($frames),
            'payload' => array_map('json_decode', $frames)
        ];
    }
}
