#!/usr/bin/env php
<?php

// Autoload Composer dependencies
require __DIR__ . '/../vendor/autoload.php';

use Google\Protobuf\Compiler\CodeGeneratorRequest;
use Google\Protobuf\Compiler\CodeGeneratorResponse;
use Google\Protobuf\Internal\FileDescriptorProto;
use Google\Protobuf\Internal\ServiceDescriptorProto;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

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
function processProtoFile(FileDescriptorProto $fileDescriptor, CodeGeneratorResponse $response)
{
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
        $generatedFile->setName(str_replace('\\', '/', $namespace) . '/' . $className . 'Client.php');
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
function generateClientCode(
    string $packageName,
    string $className,
    string $namespace,
    ServiceDescriptorProto $serviceType
): string {
    $methods = [];
    foreach ($serviceType->getMethod() as $method) {
        $methods[] = [
            'name' => $method->getName(),
            'request' => ltrim(str_replace($packageName, '', $method->getInputType()), '.'),
            'response' => ltrim(str_replace($packageName, '', $method->getOutputType()), '.'),
        ];
    }
    $loader = new FilesystemLoader(__DIR__ . '/../templates/apiclient');
    return (new Environment($loader))->render('ServiceClient.php.twig', [
        'namespace' => $namespace,
        'className' => $className,
        'host' => 'mysymfonyapi.com:8080',
        'methods' => $methods,
    ]);
}
