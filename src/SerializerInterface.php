<?php
namespace ZeroEvents;

interface SerializerInterface
{
    /**
     * Serialize message frames in json
     *
     * @param array $payload
     * @return array
     */
    public function encode(array $payload);
    /**
     * Unserialize message frames in json
     *
     * @param array $frames
     * @return array
     */
    public function decode(array $frames);
}
