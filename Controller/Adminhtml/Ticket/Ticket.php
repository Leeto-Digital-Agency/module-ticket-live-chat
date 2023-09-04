<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\TicketStatusFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Leeto\TicketLiveChat\Helper\Ticket\TicketTypeHelper;
use Magento\Customer\Model\CustomerFactory;
use Leeto\TicketLiveChat\Model\TicketTypeFactory;
use Magento\Sales\Model\Order;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;

class Ticket extends Action
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
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var TicketTypeHelper
     */
    protected $ticketTypeHelper;

    /**
     * @var CustomerFactory
     */
    protected $customerModelFactory;

    /**
     * @var TicketTypeFactory
     */
    protected $ticketTypeFactory;

    /**
     * @var Order
     */
    protected $orderModel;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * @param Context             $context
     * @param JsonFactory         $resultJsonFactory
     * @param TicketFactory       $ticketFactory
     * @param TicketStatusFactory $ticketStatusFactory
     * @param DateTime            $dateTime
     * @param TicketTypeHelper    $ticketTypeHelper
     * @param CustomerFactory     $customerModelFactory
     * @param TicketTypeFactory   $ticketTypeFactory
     * @param Order               $orderModel
     * @param TicketStatusHelper  $ticketStatusHelper
     */
    public function __construct(
        Context             $context,
        JsonFactory         $resultJsonFactory,
        TicketFactory       $ticketFactory,
        TicketStatusFactory $ticketStatusFactory,
        DateTime            $dateTime,
        TicketTypeHelper    $ticketTypeHelper,
        CustomerFactory     $customerModelFactory,
        TicketTypeFactory   $ticketTypeFactory,
        Order               $orderModel,
        TicketStatusHelper  $ticketStatusHelper
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->ticketStatusFactory = $ticketStatusFactory;
        $this->dateTime = $dateTime;
        $this->ticketTypeHelper = $ticketTypeHelper;
        $this->customerModelFactory = $customerModelFactory;
        $this->ticketTypeFactory = $ticketTypeFactory;
        $this->orderModel = $orderModel;
        $this->ticketStatusHelper = $ticketStatusHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $ticketModel = $this->ticketFactory->create();
        $ticket = $ticketModel->load($ticketId);

        $ticketStatusModel = $this->ticketStatusFactory->create();
        $ticketStatus = $ticketStatusModel->load($ticket->getStatusId());
        $isTicketOpened = $this->ticketStatusHelper
            ->getStatusIdByLabel('opened') == $ticket->getStatusId() ? true : false;
        $isTicketPending = $this->ticketStatusHelper
            ->getStatusIdByLabel('pending') == $ticket->getStatusId() ? true : false;
        $isTicketClosed = $this->ticketStatusHelper
            ->getStatusIdByLabel('closed') == $ticket->getStatusId() ? true : false;
        $ticketStatusLabel = $ticketStatus->getLabel();
        
        $isTypeOrder = $ticket->getTicketTypeId() &&
            $ticket->getTicketTypeId() == $this->ticketTypeHelper->getTicketOrderTypeId();
        $orderUrl = '';
        if ($isTypeOrder && $ticket->getOrderId()) {
            $orderUrl = $this->_url->getUrl('sales/order/view', ['order_id' => $ticket->getOrderId()]);
        }
        $customerId = $ticket->getCustomerId();
        $customerName = 'Guest';
        if ($customerId) {
            $customerModel = $this->customerModelFactory->create();
            $customer = $customerModel->load($ticket->getCustomerId());
            $customerName = $customer->getFirstname() . " " . $customer->getLastname();
        }
        $formattedDate = $this->dateTime->gmtDate('d.m.Y', $ticket->getCreatedAt());

        $data = [
            'customerName' => $customerName,
            'ticketType' => 'General',
            'subject' => $ticket->getSubject(),
            'createdAt' => $formattedDate,
            'status' => $ticketStatusLabel,
            'isTicketOpened' => $isTicketOpened,
            'isTicketPending' => $isTicketPending,
            'isTicketClosed' => $isTicketClosed,
            'isOrder' => false
        ];

        if ($isTypeOrder) {
            $ticketTypeModel = $this->ticketTypeFactory->create();
            $orderTypeLabel = $ticketTypeModel->load($ticket->getTicketTypeId())->getLabel();
            $orderIncrementId = $this->orderModel->load($ticket->getOrderId())->getIncrementId();
            $data['ticketType'] = $orderTypeLabel;
            $data['orderLink'] = $orderUrl;
            $data['isOrder'] = true;
            $data['orderIncrementId'] = $orderIncrementId;
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
