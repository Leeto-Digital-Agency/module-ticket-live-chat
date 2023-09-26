<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;

class ChangeTicketStatus extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param TicketStatusHelper    $ticketStatusHelper
     */
    public function __construct(
        Context               $context,
        JsonFactory           $resultJsonFactory,
        TicketStatusHelper    $ticketStatusHelper
    ) {
        $this->ticketStatusHelper = $ticketStatusHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $newStatusId = $this->getRequest()->getParam('status_value');
            $ticketId = $this->getRequest()->getParam('ticket_id');
            $data = $this->ticketStatusHelper->changeTicketStatus($newStatusId, $ticketId);
            $this->ticketStatusHelper->addStatusChangeMessage($newStatusId, $ticketId);
        } catch (\Exception $e) {
            return $result->setData(['error' => $e->getMessage()]);
        }
        if (isset($data['error'])) {
            return $result->setData(['error' => $data['error'], 'message' => $data['message']]);
        } elseif (isset($data['success'])) {
            return $result->setData(['success' => true]);
        }
    }
}
