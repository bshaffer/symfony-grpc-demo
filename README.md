# symfony-grpc-demo

Generate custom client with the following command:

```sh
protoc \
   --plugin=protoc-gen-custom=bin/protoc-gen-custom.php \
   --custom_out=generated-client \
   proto/pinger.proto
```
