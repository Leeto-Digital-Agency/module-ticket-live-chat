<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketDataHelper;

class ShowTicketsByStatus extends Action
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
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param TicketDataHelper      $ticketDataHelper
     */
    public function __construct(
        Context               $context,
        JsonFactory           $resultJsonFactory,
        TicketDataHelper      $ticketDataHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ticketDataHelper = $ticketDataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $statusId = $this->getRequest()->getParam('status_id');
        $data = $this->ticketDataHelper->getTicketsByStatus($statusId);
        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
