<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: proto/pinger.proto

namespace GRPC\GPBMetadata;

class Pinger
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            "\x0A\xC7\x01\x0A\x12proto/pinger.proto\x12\x06pinger\"\x1A\x0A\x0BPingRequest\x12\x0B\x0A\x03url\x18\x01 \x01(\x09\"#\x0A\x0CPingResponse\x12\x13\x0A\x0Bstatus_code\x18\x01 \x01(\x052=\x0A\x06Pinger\x123\x0A\x04ping\x12\x13.pinger.PingRequest\x1A\x14.pinger.PingResponse\"\x00B!\xCA\x02\x0BGRPC\\Pinger\xE2\x02\x10GRPC\\GPBMetadatab\x06proto3"
        , true);

        static::$is_initialized = true;
    }
}

