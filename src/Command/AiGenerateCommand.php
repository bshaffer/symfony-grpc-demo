<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use GRPC\AiChat\AiChatGrpcClient;
use \Grpc\ChannelCredentials;
use GRPC\AiChat\GenerateRequest;

class AiGenerateCommand extends Command
{
    protected static $defaultName = 'ai:generate';

    protected function configure()
    {
        $this
            ->setDescription('Calls the gRPC streaming API with a message and streams the response.')
            ->addArgument('message', InputArgument::REQUIRED, 'The message to send to the API.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new AiChatGrpcClient('localhost:50051', [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);

        $message = $input->getArgument('message');

        $request = new GenerateRequest();
        $request->setMessage($message);

        $streamingCall = $client->generate($request);

        $responses = $streamingCall->responses();

        $output->writeln('<info>Responses from gRPC server:</info>');
        foreach ($responses as $response) {
            $output->write($response->getResponse());
        }

        $client->close();

        return Command::SUCCESS;
    }
}
