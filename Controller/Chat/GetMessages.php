<?php

namespace Leeto\TicketLiveChat\Controller\Chat;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Leeto\TicketLiveChat\Api\ChatMessageRepositoryInterface;
use Leeto\TicketLiveChat\Api\ChatRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Api\AttachmentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;

class GetMessages extends Action
{
    /**
     * @var string
     */
    public const XML_PATH_ACIVE_CHAT_STATUS = 'live_chat/settings/ongoing_chat_status';

    /**
     * @var ChatMessageRepositoryInterface
     */
    private $chatMessageRepository;

    /**
     * @var ChatRepositoryInterface
     */
    private $chatRepository;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttachmentRepositoryInterface
     */
    private $attachmentRepository;

    /**
     * @var ChatStatusHelper
     */
    private $chatStatusHelper;

    /**
     * @param Context                        $context
     * @param ChatMessageRepositoryInterface $chatMessageRepository
     * @param ChatRepositoryInterface        $chatRepository
     * @param JsonFactory                    $jsonResultFactory
     * @param SearchCriteriaBuilder          $searchCriteriaBuilder
     * @param AttachmentRepositoryInterface  $attachmentRepository
     * @param ChatStatusHelper               $chatStatusHelper
     */
    public function __construct(
        Context                        $context,
        ChatMessageRepositoryInterface $chatMessageRepository,
        ChatRepositoryInterface        $chatRepository,
        JsonFactory                    $jsonResultFactory,
        SearchCriteriaBuilder          $searchCriteriaBuilder,
        AttachmentRepositoryInterface  $attachmentRepository,
        ChatStatusHelper               $chatStatusHelper
    ) {
        parent::__construct($context);
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatRepository = $chatRepository;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attachmentRepository = $attachmentRepository;
        $this->chatStatusHelper = $chatStatusHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $chatMessages = [];
        $result = $this->jsonResultFactory->create();
        $email = $this->getRequest()->getParam('email');
        $userId = $this->getRequest()->getParam('userId');
        $uuid = $this->getRequest()->getParam('uuid');

        if (!$userId && !$email && !$uuid) {
            $result->setData([
                'messages' => $chatMessages,
                'error' => 'No user id or email provided'
            ]);
            
            return $result;
        }
        // Get chat id and check if chat is active, if not return empty array
        $chatRepositoryItem = $this->getChatRepositoryItem($userId, $email, $uuid);
        if (is_array($chatRepositoryItem)) {
            $result->setData([
                'isEmailTaken' => true,
                'error' => $chatRepositoryItem['error']
            ]);

            return $result;
        }
        if (!$chatRepositoryItem || ($chatRepositoryItem && !$chatRepositoryItem->getChatId())) {
            $result->setData([
                'messages' => $chatMessages,
                'error' => 'No active chat found'
            ]);

            return $result;
        }

        // Get chat messages and format them
        $messages = [];
        $unreadMessagesCount = 0;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('chat_id', $chatRepositoryItem->getChatId(), 'eq')
            ->create();
        $chatMessages = $this->chatMessageRepository->getList($searchCriteria)->getItems();

        foreach ($chatMessages as $chatMessage) {
            $messageType = $chatMessage->getAttachmentId() ? 'file' : 'text';

            $message = [
                'chatId' => $chatMessage->getChatId(),
                'sender' => $chatMessage->getIsAdmin() ? 'support' : 'user',
                'type' => $messageType,
            ];
            if ($messageType == 'file') {
                $attachment = $this->attachmentRepository->get($chatMessage->getAttachmentId());
                $message['path'] = $attachment->getPath();
                $message['originalName'] = $attachment->getOriginalName();
            } else {
                $message['text'] = $chatMessage->getMessage();
            }
            if ($chatMessage->getIsAdmin() && !$chatMessage->getIsRead()) {
                $unreadMessagesCount++;
            }

            $messages[] = $message;
        }
        $result->setData([
            'messages' => $messages,
            'unreadMessagesCount' => $unreadMessagesCount,
            'error' => null
        ]);
        
        return $result;
    }

    /**
     * @param $userId
     * @param $email
     * @param $uuid
     * @return array|null|\Leeto\TicketLiveChat\Api\Data\ChatInterface
     */
    public function getChatRepositoryItem($userId, $email, $uuid)
    {
        $chatRepositoryItem = null;
        $activeChatStatusId = $this->chatStatusHelper
            ->getChatStatusId(ChatStatusHelper::ACTIVE_CHAT_STATUS);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status_id', $activeChatStatusId, 'eq')
            ->setPageSize(1);

        if ($userId) {
            $searchCriteria = $searchCriteria
                ->addFilter('customer_id', $userId, 'eq');
        } elseif ($uuid && $email) {
            $searchCriteria = $searchCriteria
                ->addFilter('email', $email, 'eq');
        } elseif ($uuid && !$email) {
            $searchCriteria = $searchCriteria
                ->addFilter('uuid', $uuid, 'eq');
        }

        $chatRepositoryItem = $this->chatRepository->getList($searchCriteria->create())->getItems();
        if (count($chatRepositoryItem)) {
            $chatRepositoryItem = $chatRepositoryItem[0];

            if ($chatRepositoryItem->getUuid() !== $uuid) {
                return [
                    'error' => __('This email is already in use')
                ];
            }
            return $chatRepositoryItem;
        }

        return null;
    }
}
