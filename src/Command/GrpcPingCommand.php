<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GRPC\Pinger\PingerGrpcClient;
use GRPC\Pinger\PingRequest;
use Grpc\ChannelCredentials;

class GrpcPingCommand extends Command
{
    protected static $defaultName = 'grpc:ping';

    protected function configure()
    {
        $this
            ->setDescription('Sends a ping request to the Pinger gRPC service')
            ->addArgument('host', InputArgument::REQUIRED, 'The hostname to use for the grpc client');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Connecting to Pinger gRPC service at localhost:9999...</info>');

        $host = $input->getArgument('host');
        $client = new PingerGrpcClient($host, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);

        $output->writeln("<info>Calling gRPC API at: {$host}</info>");

        $request = new PingRequest();
        $output->writeln('<info>Sending request...</info>');

        list($response, $status) = $client->Ping($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            $output->writeln('<error>gRPC call failed:</error>');
            $output->writeln('<error>' . $status->details . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Response received:</info>');
        $output->writeln('Status code: ' . $response->getStatusCode());

        return Command::SUCCESS;
    }
}
