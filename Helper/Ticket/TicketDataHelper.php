<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\AbstractHelper;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\TicketStatusFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;
use Leeto\TicketLiveChat\Helper\Ticket\TicketTypeHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\CustomerFactory;
use Leeto\TicketLiveChat\Model\TicketTypeFactory;
use Magento\Sales\Model\Order;
use Leeto\TicketLiveChat\Model\ResourceModel\Ticket\CollectionFactory as TicketCollectionFactory;
use Magento\Catalog\Helper\Image;
use Leeto\TicketLiveChat\Model\Chat;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollection;
use Magento\Framework\App\Helper\Context;
use Leeto\TicketLiveChat\Helper\Data;

class TicketDataHelper extends AbstractHelper
{
    /**
     * @var string
     */
    public const XML_PATH_USER_AVATAR_IMAGE = 'support/ticket/user_avatar_image';

    /**
     * @var string
     */
    public const AVATAR_IMAGE_PATH = 'avatar';

    /**
     * @var string
     */
    public const XML_PATH_ADMIN_AVATAR_IMAGE = 'support/ticket/admin_avatar_image';

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var TicketStatusFactory
     */
    protected $ticketStatusFactory;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * @var TicketTypeHelper
     */
    protected $ticketTypeHelper;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var DateTime
     */
    protected $dateTime;

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
     * @var TicketCollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Chat
     */
    protected $chatModel;

    /**
     * @var ChatMessageCollection
     */
    protected $chatMessageCollection;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Construct
     *
     * @param Context                 $context
     * @param TicketFactory           $ticketFactory
     * @param TicketStatusFactory     $ticketStatusFactory
     * @param TicketStatusHelper      $ticketStatusHelper
     * @param TicketTypeHelper        $ticketTypeHelper
     * @param UrlInterface            $urlInterface
     * @param DateTime                $dateTime
     * @param CustomerFactory         $customerModelFactory
     * @param TicketTypeFactory       $ticketTypeFactory
     * @param Order                   $orderModel
     * @param TicketCollectionFactory $ticketCollectionFactory
     * @param Image                   $imageHelper
     * @param Chat                    $chatModel
     * @param ChatMessageCollection   $chatMessageCollection
     * @param Data                    $dataHelper
     */
    public function __construct(
        Context                 $context,
        TicketFactory           $ticketFactory,
        TicketStatusFactory     $ticketStatusFactory,
        TicketStatusHelper      $ticketStatusHelper,
        TicketTypeHelper        $ticketTypeHelper,
        UrlInterface            $urlInterface,
        DateTime                $dateTime,
        CustomerFactory         $customerModelFactory,
        TicketTypeFactory       $ticketTypeFactory,
        Order                   $orderModel,
        TicketCollectionFactory $ticketCollectionFactory,
        Image                   $imageHelper,
        Chat                    $chatModel,
        ChatMessageCollection   $chatMessageCollection,
        Data                    $dataHelper
    ) {
        parent::__construct($context);
        $this->ticketFactory = $ticketFactory;
        $this->ticketStatusFactory = $ticketStatusFactory;
        $this->ticketStatusHelper = $ticketStatusHelper;
        $this->ticketTypeHelper = $ticketTypeHelper;
        $this->urlInterface = $urlInterface;
        $this->dateTime = $dateTime;
        $this->customerModelFactory = $customerModelFactory;
        $this->ticketTypeFactory = $ticketTypeFactory;
        $this->orderModel = $orderModel;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->chatModel = $chatModel;
        $this->chatMessageCollection = $chatMessageCollection;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return array
     */
    public function getTicketData($ticketId)
    {
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
            $orderUrl = $this->urlInterface->getUrl('sales/order/view', ['order_id' => $ticket->getOrderId()]);
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
            'statusId' => $ticket->getStatusId(),
            'isTicketOpened' => $isTicketOpened,
            'isTicketPending' => $isTicketPending,
            'isTicketClosed' => $isTicketClosed,
            'isOrder' => false
        ];

        if ($isTicketClosed) {
            $data['message'] = "This ticket is closed!";
        }

        if ($isTypeOrder) {
            $ticketTypeModel = $this->ticketTypeFactory->create();
            $orderTypeLabel = $ticketTypeModel->load($ticket->getTicketTypeId())->getLabel();
            $orderIncrementId = $this->orderModel->load($ticket->getOrderId())->getIncrementId();
            $data['ticketType'] = $orderTypeLabel;
            $data['orderLink'] = $orderUrl;
            $data['isOrder'] = true;
            $data['orderIncrementId'] = $orderIncrementId;
        }

        return $data;
    }

    /**
     * @param int $statusId
     */
    public function getTicketsByStatus($statusId)
    {
        $ticketsCollection = $this->ticketCollectionFactory->create();
        $ticketsCollection->addFieldToFilter('status_id', $statusId)
            ->setOrder('created_at', 'DESC');

        $customerModel = $this->customerModelFactory->create();
        $data = [];
        foreach ($ticketsCollection->getItems() as $ticket) {
            $ticketData = [];
            $ticketModel = $this->ticketFactory->create()->load($ticket->getId());
            $customer = $customerModel->load($ticketModel->getCustomerId());
            $ticketData['ticketId'] = $ticket->getId();
            $ticketData['username'] = 'Guest';
            $ticketData['latestMessage'] = $this->getLatestMessageData($ticket->getId());
            $ticketData['imageSrc'] = $ticketData['latestMessage']['isAdmin'] ?
                $this->getAdminAvatarImagePath() :
                $this->getUserAvatarImagePath();
            if ($ticketModel->getCustomerId() && $customer && $customer->getId()) {
                $ticketData['username'] = $customer->getFirstname() . ' ' . $customer->getLastname();
            }

            $data[] = $ticketData;
        }

        return [
            'data' => $data,
            'totalTickets' => $ticketsCollection->count()
        ];
    }

    /**
     * @param int $ticketId
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
    public function getUserAvatarImagePath()
    {
        if ($this->getScopeValue(self::XML_PATH_USER_AVATAR_IMAGE)) {
            return $this->getMediaBaseUrl() .
                self::AVATAR_IMAGE_PATH . '/' .
                $this->getScopeValue(self::XML_PATH_USER_AVATAR_IMAGE);
        }

        return $this->dataHelper->getDefaultImage();
    }

    /**
     * @return string
     */
    public function getAdminAvatarImagePath()
    {
        if ($this->getScopeValue(self::XML_PATH_ADMIN_AVATAR_IMAGE)) {
            return $this->getMediaBaseUrl() .
                self::AVATAR_IMAGE_PATH . '/' .
                $this->getScopeValue(self::XML_PATH_ADMIN_AVATAR_IMAGE);
        }

        return $this->dataHelper->getDefaultImage();
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function getScopeValue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? null;
    }

    /**
     * @return string
     */
    public function getMediaBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
    }
}
