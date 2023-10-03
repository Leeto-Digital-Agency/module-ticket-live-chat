<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Leeto\TicketLiveChat\Api\ChatRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\ChatInterfaceFactory;
use Leeto\TicketLiveChat\Api\ChatMessageRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\ChatMessageInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Leeto\TicketLiveChat\Api\AttachmentRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Leeto\TicketLiveChat\Helper\Data;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;
use Magento\Framework\Api\SortOrderBuilder;
use Leeto\TicketLiveChat\Model\ChatMessageAttachmentFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetUser extends Action
{
    protected $resultFactory;

    /**
     * @var ChatRepositoryInterface
     */
    protected $chatRepository;

    /**
     * @var ChatInterfaceFactory
     */
    protected $chatInterfaceFactory;

    /**
     * @var ChatMessageRepositoryInterface
     */
    protected $chatMessageRepository;

    /**
     * @var ChatMessageInterfaceFactory
     */
    protected $chatMessageInterfaceFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttachmentRepositoryInterface
     */
    protected $attachmentRepository;

    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var ChatStatusHelper
     */
    protected $chatStatusHelper;

    /**
     * @var ChatMessageAttachmentFactory
     */
    protected $chatMessageAttachmentFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context                        $context
     * @param ResultFactory                  $resultFactory
     * @param ChatRepositoryInterface        $chatRepository
     * @param ChatInterfaceFactory           $chatInterfaceFactory
     * @param ChatMessageRepositoryInterface $chatMessageRepository
     * @param ChatMessageInterfaceFactory    $chatMessageInterfaceFactory
     * @param SearchCriteriaBuilder          $searchCriteriaBuilder
     * @param AttachmentRepositoryInterface  $attachmentRepository
     * @param JsonFactory                    $jsonResultFactory
     * @param CustomerRepositoryInterface    $customerRepositoryInterface
     * @param Data                           $helper
     * @param SortOrderBuilder               $sortOrderBuilder
     * @param ChatStatusHelper               $chatStatusHelper
     * @param ChatMessageAttachmentFactory   $chatMessageAttachmentFactory
     * @param DateTime                       $dateTime
     * @param LoggerInterface                $logger
     */
    public function __construct(
        Context                        $context,
        ResultFactory                  $resultFactory,
        ChatRepositoryInterface        $chatRepository,
        ChatInterfaceFactory           $chatInterfaceFactory,
        ChatMessageRepositoryInterface $chatMessageRepository,
        ChatMessageInterfaceFactory    $chatMessageInterfaceFactory,
        SearchCriteriaBuilder          $searchCriteriaBuilder,
        AttachmentRepositoryInterface  $attachmentRepository,
        JsonFactory                    $jsonResultFactory,
        CustomerRepositoryInterface    $customerRepositoryInterface,
        Data                           $helper,
        SortOrderBuilder               $sortOrderBuilder,
        ChatStatusHelper               $chatStatusHelper,
        ChatMessageAttachmentFactory   $chatMessageAttachmentFactory,
        DateTime                       $dateTime,
        LoggerInterface                $logger
    ) {
        $this->resultFactory = $resultFactory;
        $this->chatRepository = $chatRepository;
        $this->chatInterfaceFactory = $chatInterfaceFactory;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatMessageInterfaceFactory = $chatMessageInterfaceFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attachmentRepository = $attachmentRepository;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->helper = $helper;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->chatStatusHelper = $chatStatusHelper;
        $this->chatMessageAttachmentFactory = $chatMessageAttachmentFactory;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonResultFactory->create();

        try {
            $chatId = $this->getRequest()->getParam('chatId');
            if (!$chatId) {
                throw new NoSuchEntityException(
                    __('Chat id is not set')
                );
            }
    
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('status_id', $this->chatStatusHelper
                    ->getChatStatusId(ChatStatusHelper::ACTIVE_CHAT_STATUS))
                ->addFilter('chat_id', $chatId, 'eq')
                ->setPageSize(1)
                ->create();
            $chat = $this->chatRepository->getList($searchCriteria)->getItems();
            if (count($chat)) {
                $chat = $chat[0];
            }
    
            // Get all chat messages for this chat and order them by date
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('chat_id', $chat->getChatId())
                ->addSortOrder($this->sortOrderBuilder->setField('created_at')->setDirection('ASC')->create())
                ->create();
            $chatMessages = $this->chatMessageRepository->getList($searchCriteria)->getItems();
    
            // Format chat messages
            $messages = [];
            $unreadMessages = 0;
            foreach ($chatMessages as $chatMessage) {
                $messageType = 'text';
                $attachment = $this->chatMessageAttachmentFactory->create()
                    ->load($chatMessage->getMessageId(), 'message_id');
    
                if ($attachment && $attachment->getId()) {
                    $attachmentId = $attachment->getAttachmentId();
                    $messageType = 'file';
                }
                $message = [
                    'sender' => $chatMessage->getIsAdmin() ? 'admin' : 'user',
                    'type' => $messageType,
                    'isRead' => $chatMessage->getIsRead() ? true : false
                ];
                if ($messageType == 'file') {
                    $attachment = $this->attachmentRepository->get($attachmentId);
                    $message['path'] = $attachment->getPath();
                    $message['originalName'] = $attachment->getOriginalName();
                } else {
                    $message['text'] = $chatMessage->getMessage();
                }
                if (!$chatMessage->getIsAdmin() && !$chatMessage->getIsRead()) {
                    $unreadMessages++;
                }
    
                $messages[] = $message;
            }
            // Format user data
            if (count($chatMessages)) {
                $customer = null;
    
                if ($chat->getCustomerId()) {
                    $customer = $this->customerRepositoryInterface->getById($chat->getCustomerId());
                }
                $user = [
                    'id' => $chat->getChatId(),
                    'name' => $customer ? $customer->getFirstname() . ' ' . $customer->getLastname() : 'Guest',
                    'email' => $customer ? $customer->getEmail() : $chat->getEmail(),
                    'isGuest' => $customer ? false : true,
                    'customerUrl' => $customer ? $this->_url
                        ->getUrl('customer/index/edit', ['id' => $customer->getId()]) : null,
                    'unreadMessages' => $unreadMessages,
                    'messages' => $messages,
                    'chat' => [
                        'statusClass' => ChatStatusHelper::ACTIVE_CHAT_STATUS,
                        'statusId' => $chat->getStatusId(),
                        'createdAt' => $this->dateTime->gmtDate('d.m.Y', $chat->getCreatedAt())
                    ]
                ];
            }
    
            $response = ['user' => $user];
            $result->setData($response);
            return $result;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result->setHttpResponseCode(400);
            $result->setData(['error' => $e->getMessage()]);
        }
    }
}
