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
use Leeto\TicketLiveChat\Helper\Chat\ChatHelper;
use Leeto\TicketLiveChat\Model\ChatMessageAttachmentFactory;
use Magento\Framework\Serialize\Serializer\Json;

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
     * @var array
     */
    protected $adminChatConnection = [];

    /**
     * @var array
     */
    protected $lostMessages = [];

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
     * @var ChatHelper
     */
    protected $chatHelper;

    /**
     * @var ChatMessageAttachmentFactory
     */
    protected $chatMessageAttachmentFactory;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @param ChatRepositoryInterface                  $chatRepository
     * @param ChatInterfaceFactory                     $chatInterfaceFactory
     * @param ChatMessageRepositoryInterface           $chatMessageRepository
     * @param ChatMessageInterfaceFactory              $chatMessageInterfaceFactory
     * @param SearchCriteriaBuilder                    $searchCriteriaBuilder
     * @param AttachmentInterfaceFactory               $attachmentInterfaceFactory
     * @param AttachmentRepositoryInterface            $attachmentRepository
     * @param Filesystem                               $filesystem
     * @param UrlInterface                             $_url
     * @param LoggerInterface                          $logger
     * @param Data                                     $helper
     * @param ChatStatusHelper                         $chatStatusHelper
     * @param ChatHelper                               $chatHelper
     * @param ChatMessageAttachmentFactory             $chatMessageAttachmentFactory
     * @param Json                                     $jsonSerializer
     */
    public function __construct(
        ChatRepositoryInterface                  $chatRepository,
        ChatInterfaceFactory                     $chatInterfaceFactory,
        ChatMessageRepositoryInterface           $chatMessageRepository,
        ChatMessageInterfaceFactory              $chatMessageInterfaceFactory,
        SearchCriteriaBuilder                    $searchCriteriaBuilder,
        AttachmentInterfaceFactory               $attachmentInterfaceFactory,
        AttachmentRepositoryInterface            $attachmentRepository,
        Filesystem                               $filesystem,
        UrlInterface                             $_url,
        LoggerInterface                          $logger,
        Data                                     $helper,
        ChatStatusHelper                         $chatStatusHelper,
        ChatHelper                               $chatHelper,
        ChatMessageAttachmentFactory             $chatMessageAttachmentFactory,
        Json                                     $jsonSerializer
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
        $this->chatHelper = $chatHelper;
        $this->chatMessageAttachmentFactory = $chatMessageAttachmentFactory;
        $this->jsonSerializer = $jsonSerializer;
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
                $this->handleNewConnection($from, $data);
            }
            if (isset($data['chatClosed'])) {
                $this->handleChatClosed($data);
            }
            if (isset($data['newMessage'])) {
                $this->handleNewMessage($from, $data);
            }
            if (isset($data['typingEvent'])) {
                $this->handleTyping($data);
            }
            if (isset($data['notifyAdminsUserClick'])) {
                $this->handleNotifyAdminsUserClick($data);
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
     * @param ConnectionInterface $from
     * @param $data
     * @return void
     */
    public function handleNewMessage($from, $data)
    {
        try {
            $result = $this->validateData($data);
            if (!empty($result)) {
                $from->send($this->jsonSerializer->serialize($result));
                return;
            }

            $userId = isset($data['fromId']) && !empty($data['fromId']) ? $data['fromId'] : null;
            $email = isset($data['email']) && !empty($data['email']) ? $data['email'] : null;
            $uuid = isset($data['uuid']) && !empty($data['uuid']) ? $data['uuid'] : null;
            $chatId = isset($data['chatId']) && !empty($data['chatId']) ? $data['chatId'] : null;

            $chat = $this->chatHelper->getChat($userId, $email, $uuid, $chatId);
            $chatId = $chat && $chat->getChatId() ? $chat->getChatId() : null;
        } catch (\Exception $e) {
            $this->logger->error('An error has occurred: ' . $e->getMessage());
        }
        if (!$chatId) {
            // Create a new chat and add the connection to it
            $newChat = $this->chatHelper->createChat($userId, $email, $uuid);
            $chatId = $newChat->getChatId();
            // we are removing the temporary chatId (resourceId) and adding the real one
            $this->updateConnectionChatId($from, $chatId);
        }
        if ($chatId) {
            $newChatMessage = $this->chatMessageInterfaceFactory->create();
            $newChatMessage->setChatId($chatId);
            $newChatMessage->setIsAdmin($data['isAdmin'] ? 1 : null);
            $newChatMessage->setMessage($data['message']);
            $newChatMessage = $this->chatMessageRepository->save($newChatMessage);

            if ($data['type'] === 'file') {
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

                // create a new chat message attachment record in the database
                // to link the chat message with the attachment
                $chatMessageAttachmentModel = $this->chatMessageAttachmentFactory->create();
                $chatMessageAttachmentData = [
                    'message_id' => $newChatMessage->getMessageId(),
                    'attachment_id' => $attachment->getAttachmentId()
                ];
                $chatMessageAttachmentModel->setData($chatMessageAttachmentData)->save();
            }
            if ($newChatMessage) {
                $this->updateChat($chatId);
                $dataToSend = [
                    'newMessage' => true,
                    'chatId' => $chatId,
                    'message' => $data['message'],
                    'type' => $data['type'],
                    'path' => $filePath ?? null,
                    'sender' => $data['isAdmin'] ? 'admin' : 'user',
                    'originalName' => $data['attachment']['name'] ?? null,
                    'status' => 'success',
                ];

                if (!$data['isAdmin']) {
                    if (!count($this->adminChatConnection)) {
                        $this->lostMessages[] = $dataToSend;
                    }
                } else {
                    if (isset($this->clientsChatConnectionMapping[$chatId])) {
                        $client = $this->clientsChatConnectionMapping[$chatId];
                        $client->send(
                            $this->jsonSerializer->serialize($dataToSend)
                        );
                    }
                }
                if (count($this->adminChatConnection)) {
                    foreach ($this->adminChatConnection as $adminConnection) {
                        if (isset($data['resourceId']) && $adminConnection->resourceId == $data['resourceId']) {
                            continue;
                        }
                        $adminConnection->send(
                            $this->jsonSerializer->serialize($dataToSend)
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $data
     * @return array
     */
    public function validateData($data)
    {
        $allowedExtensions = explode(',', $this->chatHelper->getAllowedFileExtensions());
        $allowedMessageTypes = ['text', 'file'];
        $maxFilesSize = $this->chatHelper->getMaximumFilesSize();
        $convertedMaxFilesSize = $maxFilesSize * 1024 * 1024;
        $maxLength = 2000;
        $errors = [];

        if (!isset($data['type']) ||
            empty($data['type'] ||
            !in_array($data['type'], $allowedMessageTypes))
        ) {
            $errors['errorMessages'][] = __('Something went wrong, your message could not be sent.');
            $this->logger->error(__('Message type is missing.'));
            return $errors;
        }

        if ($data['type'] === 'text') {
            if (!isset($data['message']) || empty($data['message'])) {
                $errors['errorMessages'][] = __('Please enter a message.');
            } elseif (strlen($data['message']) >= $maxLength) {
                $errors['errorMessages'][] = __('The message must be less than %1 characters.', $maxLength);
            }
        } elseif ($data['type'] === 'file') {
            if (!isset($data['attachment']) || empty($data['attachment'])) {
                $errors['errorMessages'][] = __('Please select a file.');
            } else {
                if (isset($data['attachment']['name'])) {
                    $fileName = $data['attachment']['name'];
                    $fileNameParts = explode('.', $fileName);
                    $fileType = end($fileNameParts);
                }
                if (isset($data['attachment']['size'])) {
                    $fileSize = $data['attachment']['size'];
                }

                if (!$fileType || !in_array($fileType, $allowedExtensions)) {
                    $errors['errorMessages'][] = __('Invalid file type.');
                }
                if (!$fileSize || $fileSize > $convertedMaxFilesSize) {
                    $errors['errorMessages'][] = __('Maximum file size exceeded.');
                }
            }
        }

        return $errors;
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
            $this->clients->detach($conn);
        } else {
            // sleep(3);
            if (!$this->helper->isBackendAdminLoggedIn()) {
                $this->clients->detach($conn);
                // remove connection from array
                $key = array_search($conn, $this->adminChatConnection);
                unset($this->adminChatConnection[$key]);

                $this->notifyUsersAdminStatus();
            }
        }
    }

    /**
     * Notify the user about the admin status
     * @param ConnectionInterface $conn
     */
    public function notifyUserAdminStatus(ConnectionInterface $conn)
    {
        $adminStatus = $this->getAdminStatus();
        $dataToSend = $this->jsonSerializer->serialize([
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
        $dataToSend = $this->jsonSerializer->serialize([
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
        return count($this->adminChatConnection) ? 'online' : 'offline';
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

    /**
     * @param $chatId
     */
    public function updateConnectionToTemporaryId($chatId)
    {
        if (isset($this->clientsChatConnectionMapping[$chatId])) {
            $client = $this->clientsChatConnectionMapping[$chatId];
            unset($this->clientsChatConnectionMapping[$chatId]);
            $this->addConnectionToChat($client, $client->resourceId);
        }
    }

    /**
     * @param $data
     */
    public function notifyUserClosedChat($data)
    {
        $chatId = $data['chatId'];

        if (isset($this->clientsChatConnectionMapping[$chatId])) {
            $client = $this->clientsChatConnectionMapping[$chatId];
            $client->send($this->jsonSerializer->serialize($data));
        }
    }

    /**
     * @param ConnectionInterface $from
     * @param $data
     * @return void
     */
    public function handleNewConnection($from, $data)
    {
        if (isset($data['role']) && $data['role'] === 'admin') {
            $this->adminChatConnection[] = $from;
            $this->notifyUsersAdminStatus();
            $this->notifyAdminResourceId($from);
            if (!empty($this->lostMessages)) {
                $this->notifyAdminUserMessages($this->lostMessages);
                $this->lostMessages = [];
            }
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
    }

    /**
     * @param $data
     * @return void
     */
    public function handleChatClosed($data)
    {
        if ($data['byAdmin']) {
            $this->notifyUserClosedChat($data);
        }
        $this->updateConnectionToTemporaryId($data['chatId']);
    }

    /**
     * @param $data
     * @return void
     */
    public function handleTyping($data)
    {
        $chatId = $data['chatId'];
        $dataToSend = [
            'typeEvent' => true,
            'typing' => $data['typing'],
            'chatId' => $chatId,
            'isAdmin' => $data['isAdmin']
        ];

        if (!$data['isAdmin']) {
            if (count($this->adminChatConnection)) {
                foreach ($this->adminChatConnection as $adminConnection) {
                    $adminConnection->send(
                        $this->jsonSerializer->serialize($dataToSend)
                    );
                }
            }
        } else {
            if (isset($this->clientsChatConnectionMapping[$chatId])) {
                $client = $this->clientsChatConnectionMapping[$chatId];
                $client->send(
                    $this->jsonSerializer->serialize($dataToSend)
                );
            }
        }
    }

    /**
     * @param array @messages
     * @return void
     */
    public function notifyAdminUserMessages($messages)
    {
        foreach ($this->adminChatConnection as $adminConnection) {
            $adminConnection->send(
                $this->jsonSerializer->serialize([
                    'lostMessages' => true,
                    'messages' => $messages
                ])
            );
        }
    }

    /**
     * @param ConnectionInterface $from
     * @return void
     */
    public function notifyAdminResourceId($from)
    {
        $from->send(
            $this->jsonSerializer->serialize([
                'resourceId' => $from->resourceId
            ])
        );
    }

    /**
     * @param $data
     * @return void
     */
    public function handleNotifyAdminsUserClick($data)
    {
        $dataToSend = [
            'notifyAdminsUserClick' => true,
            'chatId' => $data['chatId'],
        ];
        if (count($this->adminChatConnection)) {
            foreach ($this->adminChatConnection as $adminConnection) {
                if (isset($data['resourceId']) && $adminConnection->resourceId == $data['resourceId']) {
                    continue;
                }
                $adminConnection->send(
                    $this->jsonSerializer->serialize($dataToSend)
                );
            }
        }
    }
}
