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
    public function serialize(array $payload);
    /**
     * Unserialize message frames in json
     *
     * @param array $frames
     * @return array
     */
    public function unserialize(array $frames);
}
