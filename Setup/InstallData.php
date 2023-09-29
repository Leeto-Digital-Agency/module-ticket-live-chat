<?php

namespace Leeto\TicketLiveChat\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Leeto\TicketLiveChat\Model\TicketStatusFactory;
use Leeto\TicketLiveChat\Model\TicketTypeFactory;
use Leeto\TicketLiveChat\Model\ChatStatusFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Leeto\TicketLiveChat\Model\TicketStatusFactory
     */
    protected $ticketStatusFactory;

    /**
     * @var \Leeto\TicketLiveChat\Model\TicketTypeFactory
     */
    protected $ticketTypeFactory;

    /**
     * @var \Leeto\TicketLiveChat\Model\ChatStatusFactory
     */
    protected $chatStatusFactory;

    /**
     * @var string
     */
    protected $ticketStatusTable = 'leeto_ticket_status';

    /**
     * @var string
     */
    protected $ticketTypeTable = 'leeto_ticket_type';

    /**
     * @var string
     */
    protected $chatStatusTable = 'leeto_chat_status';

    public function __construct(
        TicketStatusFactory $ticketStatusFactory,
        TicketTypeFactory $ticketTypeFactory,
        ChatStatusFactory $chatStatusFactory
    ) {
        $this->ticketStatusFactory = $ticketStatusFactory;
        $this->ticketTypeFactory = $ticketTypeFactory;
        $this->chatStatusFactory = $chatStatusFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $ticketStatus = $this->ticketStatusFactory->create();
        $ticketType = $this->ticketTypeFactory->create();
        $chatStatus = $this->chatStatusFactory->create();

        $ticketStatusData = [
            [
                'label' => "Opened",
            ],
            [
                'label' => "Closed",
            ],
            [
                'label' => "Pending",
            ]
        ];
        $ticketTypeData = [
            [
                'label' => "General",
            ],
            [
                'label' => "Order",
            ]
        ];
        $chatStatusData = [
            [
                'label' => "Ongoing",
            ],
            [
                'label' => "Closed",
            ]
        ];

        foreach ($ticketStatusData as $data) {
            $setup->getConnection()->insert($setup->getTable($this->ticketStatusTable), $data);
        }
        foreach ($ticketTypeData as $data) {
            $setup->getConnection()->insert($setup->getTable($this->ticketTypeTable), $data);
        }
        foreach ($chatStatusData as $data) {
            $setup->getConnection()->insert($setup->getTable($this->chatStatusTable), $data);
        }

        $setup->endSetup();
    }
}
