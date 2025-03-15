#!/usr/bin/env php
<?php

// Autoload Composer dependencies
require __DIR__ . '/../vendor/autoload.php';

use Google\Protobuf\Compiler\CodeGeneratorRequest;
use Google\Protobuf\Compiler\CodeGeneratorResponse;
use Google\Protobuf\Internal\FileDescriptorProto;
use Google\Protobuf\Internal\ServiceDescriptorProto;

// Read the CodeGeneratorRequest from stdin
$input = file_get_contents('php://stdin');
$request = new CodeGeneratorRequest();
$request->mergeFromString($input);

// Create a CodeGeneratorResponse
$response = new CodeGeneratorResponse();

// Process each proto file
foreach ($request->getProtoFile() as $fileDescriptorBytes) {
    $fileDescriptor = new FileDescriptorProto();
    $fileDescriptor->mergeFromString($fileDescriptorBytes);
    processProtoFile($fileDescriptor, $response);
}

// Serialize the response and write it to stdout
$output = $response->serializeToString();
file_put_contents('php://stdout', $output);

/**
 * Processes a single proto file descriptor.
 *
 * @param FileDescriptorProto $fileDescriptor The file descriptor.
 * @param CodeGeneratorResponse $response The response object.
 */
function processProtoFile(FileDescriptorProto $fileDescriptor, CodeGeneratorResponse $response) {
    $fileName = $fileDescriptor->getName();
    $packageName = $fileDescriptor->getPackage();
    $namespace = $fileDescriptor->getOptions()->getPhpNamespace() ?? str_replace('.', '/', $packageName);

    // Example: Generate a simple PHP class for each message
    $files = $response->getFile();
    foreach ($fileDescriptor->getService() as $serviceType) {
        $serviceName = $serviceType->getName();
        $className = ucfirst($serviceName); // Basic class name conversion

        $classContent = generateClientCode($packageName, $className, $namespace, $serviceType);

        // Create a new file in the response
        $generatedFile = new CodeGeneratorResponse\File();
        $generatedFile->setName(str_replace('\\', '/', $namespace) . '/' . $className . '.php');
        $generatedFile->setContent($classContent);
        $files[] = $generatedFile;
    }
    $response->setFile($files);
}

/**
  * Generates the PHP class code for a message.
  *
  * @param string $packageName
  * @param string $className The name of the class.
  * @param ServiceDescriptorProto $serviceType
  * @return string The generated PHP code.
  */
function generateClientCode(string $packageName, string $className, string $namespace, ServiceDescriptorProto $serviceType): string {
    $methods = [];
    foreach ($serviceType->getMethod() as $method) {
        $inputType = ltrim(str_replace($packageName, '', $method->getInputType()), '.');
        $outputType = ltrim(str_replace($packageName, '', $method->getOutputType()), '.');
        $methodName = $method->getName();
        $methods[] = <<<PHP
            public function $methodName($inputType \$request): $outputType
            {
                return \$this->call('$methodName', \$request);
            }\n
        PHP;
    }
    $methodsCode = implode("\n", $methods);

    $code = <<<PHP
<?php
namespace {$namespace};

class {$className}
{
    private \Grpc\BaseStub \$grpcClient;
    private \GuzzleHttp\Client \$httpClient;
    private string \$transport = 'grpc';
    private string \$host = 'mysymfonyapi.com:8080';

    public function __construct(array \$options = [])
    {
        \$this->transport = \$options['transport'] ?? 'grpc';
        \$this->httpClient = \$options['httpClient'] ?? new \GuzzleHttp\Client();
        \$this->grpcClient = \$options['grpcClient'] ?? new {$className}GrpcClient(\$this->host, [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
    }

    private function call(\$method, \$request)
    {
        // Implement the gRPC call here
        if (\$this->transport === 'grpc') {
            [\$response, \$status] = \$this->grpcClient->\$method(\$request)->wait();
        } else {
            return \$this->httpClient->request(
                'POST',
                \$this->host . '/' . \$method,
                [
                    'body' => \$request->serializeToJsonString(),
                ]
            );
        }

        return \$response;
    }

{$methodsCode}
}
PHP;
    return $code;
}
