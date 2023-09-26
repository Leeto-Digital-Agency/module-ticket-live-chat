<?php

namespace Leeto\TicketLiveChat\Controller\Ticket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketMessageHelper;

class Message extends Action
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
     * Construct
     *
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param TicketMessageHelper   $ticketMessageHelper
     */
    public function __construct(
        Context             $context,
        JsonFactory         $resultJsonFactory,
        TicketMessageHelper $ticketMessageHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ticketMessageHelper = $ticketMessageHelper;
        parent::__construct($context);
    }

    /**
     *
     * @return Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');

        $data = $this->ticketMessageHelper->getTicketMessages($ticketId);
        $returnLatestMessage = end($data);

        $result = $this->resultJsonFactory->create();
        return $result->setData($returnLatestMessage);
    }
}
