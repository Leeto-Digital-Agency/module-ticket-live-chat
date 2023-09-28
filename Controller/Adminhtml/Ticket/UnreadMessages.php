<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketMessageHelper;

class UnreadMessages extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TicketMessageHelper
     */
    protected $ticketMessageHelper;

    /**
     * @param Context             $context
     * @param JsonFactory         $resultJsonFactory
     * @param TicketMessageHelper    $ticketMessageHelper
     */
    public function __construct(
        Context             $context,
        JsonFactory         $resultJsonFactory,
        TicketMessageHelper    $ticketMessageHelper
    ) {
        $this->ticketMessageHelper = $ticketMessageHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $count = $this->ticketMessageHelper->getTotalUnreadMessagesFromTickets();
        $data = ['count' => $count];
        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
