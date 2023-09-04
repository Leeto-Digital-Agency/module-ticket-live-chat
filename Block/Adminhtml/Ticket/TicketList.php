<?php

namespace Leeto\TicketLiveChat\Block\Adminhtml\Ticket;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Leeto\TicketLiveChat\Api\TicketRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Eav\Model\Entity\Attribute;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\Ticket\Collection as TicketCollection;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\Customer;
use Leeto\TicketLiveChat\Model\Chat;
use Leeto\TicketLiveChat\Model\ChatMessage;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollection;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;

class TicketList extends Template
{
    /**
     * @var TicketRepositoryInterface
     */
    protected $ticketRepositoryInterface;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    /**
     * @var Attribute
     */
    protected $entityAttribute;

    /**
     * @var TicketFactory
     */
    protected $ticket;

    /**
     * @var TicketCollection
     */
    protected $ticketCollection;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Customer
     */
    protected $customerModel;

    /**
     * @var Chat
     */
    protected $chatModel;

    /**
     * @var ChatMessage
     */
    protected $chatMessageModel;

    /**
     * @var ChatMessageCollection
     */
    protected $chatMessageCollection;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * @param Context                           $context
     * @param array                             $data
     * @param TicketRepositoryInterface         $ticketRepositoryInterface
     * @param SearchCriteriaBuilderFactory      $searchCriteriaBuilderFactory
     * @param Attribute                         $entityAttribute
     * @param TicketFactory                     $ticket
     * @param TicketCollection                  $ticketCollection
     * @param Image                             $imageHelper
     * @param Customer                          $customerModel
     * @param Chat                              $chatModel
     * @param ChatMessage                       $chatMessageModel
     * @param ChatMessageCollection             $chatMessageCollection
     * @param TicketStatusHelper                $ticketStatusHelper
     */
    public function __construct(
        Context                             $context,
        TicketRepositoryInterface           $ticketRepositoryInterface,
        SearchCriteriaBuilderFactory        $searchCriteriaBuilderFactory,
        Attribute                           $entityAttribute,
        TicketFactory                       $ticket,
        TicketCollection                    $ticketCollection,
        Image                               $imageHelper,
        Customer                            $customerModel,
        Chat                                $chatModel,
        ChatMessage                         $chatMessageModel,
        ChatMessageCollection               $chatMessageCollection,
        TicketStatusHelper                  $ticketStatusHelper,
        array                        $data = []
    ) {
        $this->ticketRepositoryInterface = $ticketRepositoryInterface;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->entityAttribute = $entityAttribute;
        $this->ticket = $ticket;
        $this->ticketCollection = $ticketCollection;
        $this->imageHelper = $imageHelper;
        $this->customerModel = $customerModel;
        $this->chatModel = $chatModel;
        $this->chatMessageModel = $chatMessageModel;
        $this->chatMessageCollection = $chatMessageCollection;
        $this->ticketStatusHelper = $ticketStatusHelper;
        parent::__construct($context, $data);
    }

    public function getTickets()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->create();
        $tickets = [];
        foreach ($this->ticketRepositoryInterface->getList($searchCriteria)->getItems() as $ticket) {
            $ticketModel = $this->ticket->create();
            $tickets[] = $ticketModel->load($ticket->getEntityId());
        }

        return $tickets;
    }

    /**
     * @return string
     */
    public function getDefaultImage()
    {
        return $this->imageHelper->getDefaultPlaceholderUrl('image');
    }

    /**
     * @return Customer
     */
    public function getCustomerById($customerId)
    {
        return $this->customerModel->load($customerId);
    }

    /**
     * @return array
     */
    public function getLatestMessageData($ticketId)
    {
        //get chat by ticket id
        $chatId = $this->chatModel->load($ticketId, 'ticket_id')->getId();
        $latestMessage = $this->chatMessageCollection->create()->addFieldToFilter(
            'chat_id',
            $chatId
        )->setOrder('message_id', 'DESC')
        ->getFirstItem();
       
        $latestMessageData = [
            'isAdmin' => $latestMessage->getIsAdmin(),
            'latest_message' => $latestMessage->getMessage() ? $latestMessage->getMessage() : 'Attachment'
        ];
        return $latestMessageData;
    }

    /**
     * @return string
     */
    public function getTicketControllerUrl()
    {
        return $this->_urlBuilder->getUrl('leeto_support/ticket/ticket');
    }

    /**
     * @return string
     */
    public function getMessageControllerUrl()
    {
        return $this->_urlBuilder->getUrl('leeto_support/ticket/message');
    }

    /**
     * @return string
     */
    public function getAddAdminMessageControllerUrl()
    {
        return $this->_urlBuilder->getUrl('leeto_support/ticket/addadminmessage');
    }

    /**
     * @return string
     */
    public function getAddAdminFileControllerUrl()
    {
        return $this->_urlBuilder->getUrl('leeto_support/ticket/addfilemessage');
    }

    /**
     * @return string
     */
    public function getChangeTicketStatusControllerUrl()
    {
        return $this->_urlBuilder->getUrl('leeto_support/ticket/changeticketstatus');
    }

    /**
     * @return array
     */
    public function getTicketStatuses()
    {
        return $this->ticketStatusHelper->getTicketStatuses();
    }
}
