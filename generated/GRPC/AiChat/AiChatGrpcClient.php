<?php
// GENERATED CODE -- DO NOT EDIT!

namespace GRPC\AiChat;

/**
 */
class AiChatGrpcClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \GRPC\AiChat\GenerateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function generate(\GRPC\AiChat\GenerateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/aichat.AiChat/generate',
        $argument,
        ['\GRPC\AiChat\GenerateResponse', 'decode'],
        $metadata, $options);
    }

}
