<?php

namespace Leeto\TicketLiveChat\Controller\Ticket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketDataHelper;
use Magento\Customer\Model\Session;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Magento\Customer\Model\Customer;

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
     * @var Session
     */
    protected $session;

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * Construct
     *
     * @param Context           $context
     * @param JsonFactory       $resultJsonFactory
     * @param TicketDataHelper  $ticketDataHelper
     * @param Session           $session
     * @param TicketFactory     $ticketFactory
     * @param Customer          $customer
     */
    public function __construct(
        Context             $context,
        JsonFactory         $resultJsonFactory,
        TicketDataHelper    $ticketDataHelper,
        Session             $session,
        TicketFactory       $ticketFactory,
        Customer            $customer
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ticketDataHelper = $ticketDataHelper;
        $this->session = $session;
        $this->ticketFactory = $ticketFactory;
        $this->customer = $customer;
        parent::__construct($context);
    }

    /**
     *
     * @return Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $ticketModel = $this->ticketFactory->create()->load($ticketId);
        $user = $this->session->getCustomer();
        $customer = $this->customer->load($ticketModel->getCustomerId());
        
        $data = $this->ticketDataHelper->getTicketData($ticketId);
        $data['loggedInRequired'] = false;
        if ($customer->getId() && !$user->getId()) {
            $data['loggedInRequired'] = true;
            $data['message'] = 'Please login in order to send messages!';
        }

        return $result->setData($data);
    }
}
