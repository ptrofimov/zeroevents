<?php
namespace ZeroEvents\Serializer;

interface SerializerInterface
{
    /**
     * Transform event and payload to array of frames
     *
     * @param string $event
     * @param array $payload
     * @return array
     */
    public function serialize($event, array $payload);

    /**
     * Transform array of frames to event and payload
     *
     * @param array $frames
     * @return array [event, payload]
     */
    public function unserialize(array $frames);
}
