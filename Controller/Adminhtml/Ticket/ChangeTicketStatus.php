<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\TicketStatusFactory;

class ChangeTicketStatus extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var TicketStatusFactory
     */
    protected $ticketStatusFactory;

    /**
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param TicketFactory         $ticketFactory
     * @param TicketStatusFactory   $ticketStatusFactory
     */
    public function __construct(
        Context               $context,
        JsonFactory           $resultJsonFactory,
        TicketFactory         $ticketFactory,
        TicketStatusFactory   $ticketStatusFactory
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->ticketStatusFactory = $ticketStatusFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $newStatusId = $this->getRequest()->getParam('status_value');
            $ticketStatusModel = $this->ticketStatusFactory->create();
            $ticketStatus = $ticketStatusModel->load($newStatusId);
            if (!$ticketStatus->getId()) {
                $errorMessage = "Status doesn't seem to exist!";
                $result = $this->resultJsonFactory->create();
                return $result->setData(['error' => true, 'message' => $errorMessage]);
            }
            $ticketId = $this->getRequest()->getParam('ticket_id');
            $ticketModel = $this->ticketFactory->create();
            $ticket = $ticketModel->load($ticketId);
            $ticket->setStatusId($newStatusId);
            $ticket->save();
        } catch (\Exception $e) {
            $result = $this->resultJsonFactory->create();
            return $result->setData(['error' => $e->getMessage()]);
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true]);
    }
}
