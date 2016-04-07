<?php

namespace Tabasoft;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tabasoft\SendPush;

class SendPushCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('send')
            ->setDescription('Send iOS Push Notification')
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'The message to show in the push notification'
            )->addArgument(
                'to',
                InputArgument::REQUIRED,
                'The token of the device to send the message to'
            )
            ->addOption(
                'mode',
                '-p',
                InputOption::VALUE_OPTIONAL,
                "'development' (default) or 'production' mode",
                'development'
            )
            ->addOption(
                'app',
                null,
                InputOption::VALUE_OPTIONAL,
                "the app bundle id",
                'it.tabasoft.samplepush2'
            )
            ->addOption(
                'cert',
                null,
                InputOption::VALUE_OPTIONAL,
                "the APNS certificate",
                '/Users/valfer/Sites/sendPush/development.pem'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument('message');
        if (!$message)
            throw new \Exception("il parametro 'message' è obbligatorio");
        $token = $input->getArgument('to');
        if (!$token)
            throw new \Exception("il parametro 'to' è obbligatorio");

        $messageJson = "{\"aps\":{\"alert\":\"{$message}\",\"sound\":\"default\"}}";
        $cert = $input->getOption('cert');
        $mode = $input->getOption('mode');
        $app_bundle_id = $input->getOption('app');

        $sendPush = new SendPush($cert, $messageJson, $token, $mode, $app_bundle_id);

        $sendPush->openConnection();
        $result = $sendPush->sendPush();
        $sendPush->closeConnection();

        $output->writeln($result);
    }
}