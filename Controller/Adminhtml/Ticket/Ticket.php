<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketDataHelper;

class Ticket extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TicketDataHelper
     */
    protected $ticketDataHelper;

    /**
     * @param Context             $context
     * @param JsonFactory         $resultJsonFactory
     * @param TicketDataHelper    $ticketDataHelper
     */
    public function __construct(
        Context             $context,
        JsonFactory         $resultJsonFactory,
        TicketDataHelper    $ticketDataHelper
    ) {
        $this->ticketDataHelper = $ticketDataHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');

        $data = $this->ticketDataHelper->getTicketData($ticketId);
        
        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
