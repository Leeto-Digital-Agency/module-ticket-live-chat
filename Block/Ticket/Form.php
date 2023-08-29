<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Block\Ticket;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Leeto\TicketLiveChat\Model\TicketTypeRepository;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketTypeHelper;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\ManagerInterface;

class Form extends Template
{
    /**
     * @var TicketTypeRepository
     */
    protected $ticketTypeRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var TicketTypeHelper
     */
    protected $ticketTypeHelper;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManagerInterface;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * Constructor
     *
     * @param Context                       $context
     * @param array                         $data
     * @param TicketTypeRepository          $ticketTypeRepository
     * @param SearchCriteriaBuilderFactory  $searchCriteriaInterface
     * @param Session                       $customerSession
     * @param OrderCollectionFactory        $orderCollectionFactory
     * @param TicketTypeHelper              $ticketTypeHelper
     * @param SessionManagerInterface       $sessionManagerInterface
     * @param ManagerInterface              $messageManager
     */
    public function __construct(
        Context                      $context,
        TicketTypeRepository         $ticketTypeRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        Session                      $customerSession,
        OrderCollectionFactory       $orderCollectionFactory,
        TicketTypeHelper             $ticketTypeHelper,
        SessionManagerInterface      $sessionManagerInterface,
        ManagerInterface             $messageManager,
        array                        $data = [],
    ) {
        $this->ticketTypeRepository = $ticketTypeRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->ticketTypeHelper = $ticketTypeHelper;
        $this->sessionManagerInterface = $sessionManagerInterface;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getTicketTypes()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->create();
        
        $ticketTypeList = $this->ticketTypeRepository->getList($searchCriteria);
        $ticketTypes = $ticketTypeList->getItems();

        return $ticketTypes;
    }

    /**
     * @return array
     */
    public function getUserOrders()
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId())
            ->setOrder('created_at', 'desc');

        return $orderCollection->getItems();
    }

    /**
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->customerSession->getCustomer()->getEmail();
    }

    /**
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('support/ticket/create');
    }

    /**
     * @return int
     */
    public function getTicketOrderTypeId()
    {
        return $this->ticketTypeHelper->getTicketOrderTypeId();
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        $formData = $this->sessionManagerInterface->getFormData();
        $this->sessionManagerInterface->unsFormData();
        return $formData;
    }

    /**
     * @return string
     */
    public function getTicketFormDataErrors($field)
    {
        $formErrors = $this->sessionManagerInterface->getFormDataError();
        $this->sessionManagerInterface->setFormDataError([$field], '');
        if (isset($formErrors[$field])) {
            return $formErrors[$field];
        }
    }
}
