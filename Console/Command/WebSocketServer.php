<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Leeto\TicketLiveChat\WebSocket\ChatServer;
use Psr\Log\LoggerInterface;
use Leeto\TicketLiveChat\Helper\Data;

class WebSocketServer extends Command
{
    /**
     * @var \Leeto\TicketLiveChat\WebSocket\ChatServer
     */
    protected $chatServer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    public const NAME_ARGUMENT = "name";
    public const NAME_OPTION = "option";

    public function __construct(
        ChatServer $chatServer,
        LoggerInterface $logger,
        Data $helper
    ) {
        parent::__construct();
        $this->chatServer = $chatServer;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ) {
        $output->writeln("Websocket server started!");
        $portNumber = $this->helper->getWebsocketPort();

        try {
            $wsServer = new WsServer($this->chatServer);
            $httpServer = new HttpServer($wsServer);
            $server = IoServer::factory($httpServer, $portNumber);
            $server->run();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Configure command
     * @return void
     */
    protected function configure()
    {
        $this->setName("leeto_ticketlivechat:websocketserver");
        $this->setDescription("Start websocket server");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
}
