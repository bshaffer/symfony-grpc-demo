<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: proto/aichat.proto

namespace GRPC\GPBMetadata;

class Aichat
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            "\x0A\xED\x01\x0A\x12proto/aichat.proto\x12\x06aichat\"1\x0A\x0FGenerateRequest\x12\x0F\x0A\x07message\x18\x01 \x01(\x09\x12\x0D\x0A\x05model\x18\x02 \x01(\x09\"\$\x0A\x10GenerateResponse\x12\x10\x0A\x08response\x18\x01 \x01(\x092K\x0A\x06AiChat\x12A\x0A\x08generate\x12\x17.aichat.GenerateRequest\x1A\x18.aichat.GenerateResponse\"\x000\x01B!\xCA\x02\x0BGRPC\\AiChat\xE2\x02\x10GRPC\\GPBMetadatab\x06proto3"
        , true);

        static::$is_initialized = true;
    }
}

