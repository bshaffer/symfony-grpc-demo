<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use GRPC\AiChat\AiChatGrpcClient;
use GRPC\AiChat\GenerateRequest;

#[AsCommand(
    name: 'ai:generate',
    description: 'Calls the gRPC streaming API'
)]
class AiGenerateCommand extends Command
{
    private $aiChatClient;

    public function __construct(AiChatGrpcClient $aiChatClient)
    {
        parent::__construct();
        $this->aiChatClient = $aiChatClient;
    }

    protected function configure()
    {
        $this
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Message to send to the gRPC server')
            ->addOption('model', null, InputOption::VALUE_OPTIONAL, 'Model to use for generation', 'gpt2');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $input->getOption('message');
        $model = $input->getOption('model');

        if (!$message) {
            $helper = $this->getHelper('question');
            $question = new Question('Please enter your message: ');
            $message = $helper->ask($input, $output, $question);

            if (!$message) {
                $output->writeln('<error>Message cannot be empty.</error>');
                return Command::FAILURE;
            }
        }

        $output->writeln("<info>Sending message to gRPC server: '$message'</info>");
        $output->writeln("<info>Using model: '$model'</info>");

        $request = new GenerateRequest();
        $request->setMessage($message);
        $request->setModel($model);

        $streamingCall = $this->aiChatClient->generate($request);

        $responses = $streamingCall->responses();

        $output->writeln('<info>Responses from gRPC server:</info>');
        foreach ($responses as $response) {
            $output->write($response->getResponse());
        }

        return Command::SUCCESS;
    }
}
