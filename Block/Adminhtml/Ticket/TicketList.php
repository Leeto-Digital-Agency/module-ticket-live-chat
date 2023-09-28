<?php

namespace Leeto\TicketLiveChat\Block\Adminhtml\Ticket;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Leeto\TicketLiveChat\Api\TicketRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\Customer;
use Leeto\TicketLiveChat\Model\Chat;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollection;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;
use Magento\Framework\Api\SortOrderBuilder;
use Leeto\TicketLiveChat\Helper\Ticket\FileValidationHelper;
use Leeto\TicketLiveChat\Helper\Ticket\TicketDataHelper;

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
     * @var TicketFactory
     */
    protected $ticket;

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
     * @var ChatMessageCollection
     */
    protected $chatMessageCollection;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var FileValidationHelper
     */
    protected $fileValidationHelper;

    /**
     * @var TicketDataHelper
     */
    protected $ticketDataHelper;

    /**
     * @param Context                           $context
     * @param array                             $data
     * @param TicketRepositoryInterface         $ticketRepositoryInterface
     * @param SearchCriteriaBuilderFactory      $searchCriteriaBuilderFactory
     * @param TicketFactory                     $ticket
     * @param Image                             $imageHelper
     * @param Customer                          $customerModel
     * @param Chat                              $chatModel
     * @param ChatMessageCollection             $chatMessageCollection
     * @param TicketStatusHelper                $ticketStatusHelper
     * @param SortOrderBuilder                  $sortOrderBuilder
     * @param FileValidationHelper              $fileValidationHelper
     * @param TicketDataHelper                  $ticketDataHelper
     */
    public function __construct(
        Context                             $context,
        TicketRepositoryInterface           $ticketRepositoryInterface,
        SearchCriteriaBuilderFactory        $searchCriteriaBuilderFactory,
        TicketFactory                       $ticket,
        Image                               $imageHelper,
        Customer                            $customerModel,
        Chat                                $chatModel,
        ChatMessageCollection               $chatMessageCollection,
        TicketStatusHelper                  $ticketStatusHelper,
        SortOrderBuilder                    $sortOrderBuilder,
        FileValidationHelper                $fileValidationHelper,
        TicketDataHelper                    $ticketDataHelper,
        array                               $data = []
    ) {
        $this->ticketRepositoryInterface = $ticketRepositoryInterface;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->ticket = $ticket;
        $this->imageHelper = $imageHelper;
        $this->customerModel = $customerModel;
        $this->chatModel = $chatModel;
        $this->chatMessageCollection = $chatMessageCollection;
        $this->ticketStatusHelper = $ticketStatusHelper;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->fileValidationHelper = $fileValidationHelper;
        $this->ticketDataHelper = $ticketDataHelper;
        parent::__construct($context, $data);
    }

    public function getTickets()
    {
        $sortOrder = $this->sortOrderBuilder->setField('created_at')->setDirection('DESC')->create();
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->setSortOrders([$sortOrder])->create();
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
    public function getChangeTicketStatusControllerUrl()
    {
        return $this->_urlBuilder->getUrl('leeto_support/ticket/changeticketstatus');
    }

    /**
     * @return string
     */
    public function getShowTicketsByStatusUrl()
    {
        return $this->_urlBuilder->getUrl('leeto_support/ticket/showticketsbystatus');
    }

    /**
     * @return array
     */
    public function getTicketStatuses()
    {
        return $this->ticketStatusHelper->getTicketStatuses();
    }

    /**
     * @return string
     */
    public function getAllowedFileExtensions()
    {
        return $this->fileValidationHelper->getAllowedFileExtensions();
    }

    /**
     * @return string
     */
    public function getMaximumFilesSize()
    {
        return $this->fileValidationHelper->getMaximumFilesSize();
    }

    /**
     * @return string
     */
    public function getMaximumFilesToUpload()
    {
        return $this->fileValidationHelper->getMaximumFilesToUpload();
    }

    /**
     * @return string
     */
    public function getAdminAvatarImage()
    {
        return $this->ticketDataHelper->getAdminAvatarImagePath();
    }
}
