<?php

namespace Leeto\TicketLiveChat\Controller\Ticket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketMessageHelper;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;

class OpenTicket extends Action
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
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * Construct
     *
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param TicketMessageHelper   $ticketMessageHelper
     * @param TicketStatusHelper    $ticketStatusHelper
     */
    public function __construct(
        Context             $context,
        JsonFactory         $resultJsonFactory,
        TicketMessageHelper $ticketMessageHelper,
        TicketStatusHelper  $ticketStatusHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ticketMessageHelper = $ticketMessageHelper;
        $this->ticketStatusHelper = $ticketStatusHelper;
        parent::__construct($context);
    }

    /**
     *
     * @return Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $pendingTicketId = $this->ticketStatusHelper->getStatusIdByLabel('pending');
        $this->ticketStatusHelper->changeTicketStatus($pendingTicketId, $ticketId);

        $data = $this->ticketMessageHelper->addTicketReopenedAlertMessage($ticketId);

        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
