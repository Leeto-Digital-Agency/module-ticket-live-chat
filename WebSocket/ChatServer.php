<?php

namespace Leeto\TicketLiveChat\WebSocket;

use Psr\Log\LoggerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Leeto\TicketLiveChat\Api\ChatRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\ChatInterfaceFactory;
use Leeto\TicketLiveChat\Api\ChatMessageRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\ChatMessageInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Leeto\TicketLiveChat\Api\Data\AttachmentInterfaceFactory;
use Leeto\TicketLiveChat\Api\AttachmentRepositoryInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Leeto\TicketLiveChat\Helper\Data;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;

class ChatServer implements MessageComponentInterface
{
    /**
     * @var $clients \SplObjectStorage
     */
    protected $clients;

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
     * @var AttachmentInterfaceFactory
     */
    protected $attachmentInterfaceFactory;

    /**
     * @var AttachmentRepositoryInterface
     */
    protected $attachmentRepository;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $clientsChatConnectionMapping = [];

    /**
     * @var ConnectionInterface
     */
    protected $adminChatConnection = null;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ChatStatusHelper
     */
    protected $chatStatusHelper;
    
    /**
     * @param ChatRepositoryInterface        $chatRepository
     * @param ChatInterfaceFactory           $chatInterfaceFactory
     * @param ChatMessageRepositoryInterface $chatMessageRepository
     * @param ChatMessageInterfaceFactory    $chatMessageInterfaceFactory
     * @param SearchCriteriaBuilder          $searchCriteriaBuilder
     * @param AttachmentInterfaceFactory     $attachmentInterfaceFactory
     * @param AttachmentRepositoryInterface  $attachmentRepository
     * @param Filesystem                     $filesystem
     * @param UrlInterface                   $_url
     * @param LoggerInterface                $logger
     * @param Data                           $helper
     * @param ChatStatusHelper               $chatStatusHelper
     */
    public function __construct(
        ChatRepositoryInterface        $chatRepository,
        ChatInterfaceFactory           $chatInterfaceFactory,
        ChatMessageRepositoryInterface $chatMessageRepository,
        ChatMessageInterfaceFactory    $chatMessageInterfaceFactory,
        SearchCriteriaBuilder          $searchCriteriaBuilder,
        AttachmentInterfaceFactory     $attachmentInterfaceFactory,
        AttachmentRepositoryInterface  $attachmentRepository,
        Filesystem                     $filesystem,
        UrlInterface                   $_url,
        LoggerInterface                $logger,
        Data                           $helper,
        ChatStatusHelper               $chatStatusHelper
    ) {
        $this->clients = new \SplObjectStorage();
        $this->chatRepository = $chatRepository;
        $this->chatInterfaceFactory = $chatInterfaceFactory;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatMessageInterfaceFactory = $chatMessageInterfaceFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attachmentInterfaceFactory = $attachmentInterfaceFactory;
        $this->attachmentRepository = $attachmentRepository;
        $this->filesystem = $filesystem;
        $this->_url = $_url;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->chatStatusHelper = $chatStatusHelper;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection: {$conn->resourceId}\n";
    }

    /**
     * @param ConnectionInterface $from
     * @param $data
     */
    public function onMessage(ConnectionInterface $from, $data)
    {
        try {
            $data = json_decode($data, true);
            // Add the connection to the chat if it's a new connection
            if (isset($data['newConnection'])) {
                if (isset($data['role']) && $data['role'] === 'admin') {
                    $this->adminChatConnection = $from;
                    $this->notifyUsersAdminStatus();
                } else {
                    if (isset($data['chatId']) && $data['chatId']) {
                        $this->addConnectionToChat($from, $data['chatId']);
                    } else {
                        // It will be a temporary chat Id until the user sends a message
                        // It serves to send notifications about the admin status to the user
                        $this->addConnectionToChat($from, $from->resourceId);
                    }

                    $this->notifyUserAdminStatus($from);
                }
                return;
            }
            
            $chat = $this->chatInterfaceFactory->create();
            $chatId = null;
            // Load chat message by fromId, chatId or email (logged in user, admin or guest)
            if (isset($data['fromId']) && $data['fromId']) {
                $chat->load($data['fromId'], 'customer_id');
            } elseif (isset($data['chatId']) && $data['chatId']) {
                $chat->load($data['chatId'], 'chat_id');
            } elseif (isset($data['uuid']) && $data['uuid']) {
                $chat->load($data['uuid'], 'uuid');
            }
    
            if ($chat && $chat->getChatId()) {
                $chatId = $chat->getChatId();
            } else {
                try {
                    // Create a new chat and add the connection to it
                    $newChat = $this->chatInterfaceFactory->create();
                    $newChat->setStatusId(
                        $this->chatStatusHelper
                            ->getChatStatusId(ChatStatusHelper::ACTIVE_CHAT_STATUS)
                    );
                    $newChat->setCustomerId(empty($data['fromId']) ? null : $data['fromId']);
                    $newChat->setEmail($data['email'] ?? null);
                    $newChat->setUuid($data['uuid'] ?? null);
                    $this->chatRepository->save($newChat);

                    // we are removing the temporary chatId (resourceId) and adding the real one
                    $chatId = $newChat->getChatId();
                    $this->updateConnectionChatId($from, $chatId);
                } catch (\Exception $e) {
                    $this->logger->error('An error has occurred: ' . $e->getMessage());
                }
            }

            if ($chatId) {
                $newChatMessage = $this->chatMessageInterfaceFactory->create();
                $newChatMessage->setChatId($chatId);
                $newChatMessage->setIsAdmin($data['isAdmin'] ? 1 : null);
        
                if ($data['type'] === 'text') {
                    $newChatMessage->setMessage($data['message']);
                } elseif ($data['type'] === 'file') {
                    // Decode the base64-encoded file data
                    $base64FileData = $data['attachment']['data'];
                    $decodedFileData = base64_decode($base64FileData);
    
                    // Save the decoded file data to a file
                    $uniqueFileName = uniqid() . '_' . $data['attachment']['name'];
                    $mediaDirectory = $this->filesystem
                        ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                    $customAttachmentsPath = 'custom_attachments/' . $uniqueFileName;
                    $mediaDirectory->writeFile($customAttachmentsPath, $decodedFileData);
                    $mediaUrl = $this->_url
                    ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
                    $filePath = $mediaUrl . $customAttachmentsPath;
    
                    // Create a new attachment record in the database
                    $attachment = $this->attachmentInterfaceFactory->create();
                    $attachment->setChatId($chatId);
                    $attachment->setOriginalName($data['attachment']['name']);
                    $attachment->setUniqueName($uniqueFileName);
                    $attachment->setPath($filePath);
                    $this->attachmentRepository->save($attachment);
    
                    $newChatMessage->setAttachmentId($attachment->getAttachmentId());
                }
                $result = $this->chatMessageRepository->save($newChatMessage);
    
                if ($result) {
                    $this->updateChat($chatId);
                    $dataToSend = json_encode([
                        'chatId' => $chatId,
                        'message' => $data['message'],
                        'type' => $data['type'],
                        'path' => $filePath ?? null,
                        'originalName' => $data['attachment']['name'] ?? null,
                        'status' => 'success',
                    ]);
    
                    if (!$data['isAdmin']) {
                        if ($this->adminChatConnection) {
                            $this->adminChatConnection->send($dataToSend);
                        } else {
                            // TO DO
                        }
                    } else {
                        if (isset($this->clientsChatConnectionMapping[$chatId])) {
                            $client = $this->clientsChatConnectionMapping[$chatId];
                            $client->send($dataToSend);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('An error has occurred: ' . $e->getMessage());
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->removeConnectionFromChat($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->logger->error('An error has occurred: ' . $e->getMessage());
        $conn->close();
    }

    /**
     *
     * @param ConnectionInterface $conn
     * @param $chatId
     */
    public function addConnectionToChat(ConnectionInterface $conn, $chatId)
    {
        if (!isset($this->clientsChatConnectionMapping[$chatId])) {
            $this->clientsChatConnectionMapping[$chatId] = $conn;
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function removeConnectionFromChat(ConnectionInterface $conn)
    {
        $chatId = array_search($conn, $this->clientsChatConnectionMapping);

        if ($chatId) {
            unset($this->clientsChatConnectionMapping[$chatId]);
        } else {
            $this->adminChatConnection = null;
            $this->notifyUsersAdminStatus();
        }
    }

    /**
     * Notify the user about the admin status
     * @param ConnectionInterface $conn
     */
    public function notifyUserAdminStatus(ConnectionInterface $conn)
    {
        $adminStatus = $this->getAdminStatus();
        $dataToSend = json_encode([
            'adminStatus' => $adminStatus,
            'message' => 'success',
        ]);

        $conn->send($dataToSend);
    }

    /**
     * Notify all users about the admin status
     */
    public function notifyUsersAdminStatus()
    {
        $adminStatus = $this->getAdminStatus();
        $dataToSend = json_encode([
            'adminStatus' => $adminStatus,
            'message' => 'success',
        ]);

        foreach ($this->clientsChatConnectionMapping as $client) {
            $client->send($dataToSend);
        }
    }

    /**
     * @return string
     */
    public function getAdminStatus()
    {
        return $this->adminChatConnection ? 'online' : 'offline';
    }

    /**
     * @param ConnectionInterface $conn
     * @param $chatId
     */
    public function updateConnectionChatId(ConnectionInterface $conn, $chatId)
    {
        if (isset($this->clientsChatConnectionMapping[$conn->resourceId])) {
            unset($this->clientsChatConnectionMapping[$conn->resourceId]);
            $this->clientsChatConnectionMapping[$chatId] = $conn;
        }
    }

    /**
     * @param $chatId
     */
    public function updateChat($chatId)
    {
        try {
            $chat = $this->chatInterfaceFactory->create()
                ->load($chatId);
    
            $chat->setUpdatedAt(date('Y-m-d H:i:s'));
            $this->chatRepository->save($chat);
        } catch (\Exception $e) {
            $this->logger->error('An error has occurred: ' . $e->getMessage());
        }
    }
}
