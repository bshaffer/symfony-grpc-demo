<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GRPC\Pinger\PingerClient;
use GRPC\Pinger\PingRequest;
use GRPC\Pinger\PingerGrpcClient;
use Grpc\ChannelCredentials;
use GuzzleHttp\Client as GuzzleClient;

require __DIR__ . '/../../generated-client/GRPC/Pinger/PingerClient.php';

class ApiPingCommand extends Command
{
    protected static $defaultName = 'api:ping';

    protected function configure()
    {
        $this
            ->setDescription('Sends a ping request using either REST or gRPC')
            ->addOption('protocol', 'p', InputOption::VALUE_REQUIRED, 'Protocol to use (rest or grpc)', 'grpc');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $protocol = strtolower($input->getOption('protocol'));

        if (!in_array($protocol, ['rest', 'grpc'])) {
            $output->writeln('<error>Protocol must be either "rest" or "grpc"</error>');
            return Command::FAILURE;
        }

        $output->writeln("<info>Using {$protocol} protocol</info>");

        // Create the client with appropriate transport
        $client = new PingerClient(['transport' => $protocol]);

        // Create and send request
        $request = new PingRequest();

        try {
            $output->writeln('<info>Sending request...</info>');
            $response = $client->ping($request);
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        // Display the response
        $output->writeln('<info>Response received:</info>');
        $output->writeln('Status code: ' . $response->getStatusCode());

        return Command::SUCCESS;
    }
}
