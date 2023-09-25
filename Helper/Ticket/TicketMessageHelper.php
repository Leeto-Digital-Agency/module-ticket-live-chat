<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\AbstractHelper;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\ChatFactory;
use Leeto\TicketLiveChat\Model\AttachmentFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollection;
use Leeto\TicketLiveChat\Model\ChatMessageFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Leeto\TicketLiveChat\Api\TicketRepositoryInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Leeto\TicketLiveChat\Model\ChatMessageAttachmentFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessageAttachment\CollectionFactory as ChatMessageAttachmentCollection;
use Magento\Catalog\Helper\Image;

class TicketMessageHelper extends AbstractHelper
{
    public const TEXT_TYPE_MESSAGE = "text";
    public const FILE_TYPE_MESSAGE = "file";

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var ChatFactory
     */
    protected $chatFactory;

    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @var ChatMessageCollection
     */
    protected $chatMessageCollection;

    /**
     * @var ChatMessageFactory
     */
    protected $chatMessageFactory;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    /**
     * @var TicketRepositoryInterface
     */
    protected $ticketRepositoryInterface;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var ChatMessageAttachmentFactory
     */
    protected $chatMessageAttachmentFactory;

    /**
     * @var ChatMessageAttachmentCollection
     */
    protected $chatMessageAttachmentCollection;

    /**
     * @var Image
     */
    protected $image;

    /**
     * Construct
     *
     * @param TicketFactory                            $ticketFactory
     * @param ChatFactory                              $chatFactory
     * @param AttachmentFactory                        $attachmentFactory
     * @param ChatMessageCollection                    $chatMessageCollection
     * @param ChatMessageFactory                       $chatMessageFactory
     * @param TicketStatusHelper                       $ticketStatusHelper
     * @param SortOrderBuilder                         $sortOrderBuilder
     * @param SearchCriteriaBuilderFactory             $searchCriteriaBuilderFactory
     * @param TicketRepositoryInterface                $ticketRepositoryInterface
     * @param Filesystem                               $filesystem
     * @param UrlInterface                             $urlInterface
     * @param ChatMessageAttachmentFactory             $chatMessageAttachmentFactory
     * @param ChatMessageAttachmentCollection          $chatMessageAttachmentCollection
     * @param Image                                    $image
     */
    public function __construct(
        TicketFactory                          $ticketFactory,
        ChatFactory                            $chatFactory,
        AttachmentFactory                      $attachmentFactory,
        ChatMessageCollection                  $chatMessageCollection,
        ChatMessageFactory                     $chatMessageFactory,
        TicketStatusHelper                     $ticketStatusHelper,
        SortOrderBuilder                       $sortOrderBuilder,
        SearchCriteriaBuilderFactory           $searchCriteriaBuilderFactory,
        TicketRepositoryInterface              $ticketRepositoryInterface,
        Filesystem                             $filesystem,
        UrlInterface                           $urlInterface,
        ChatMessageAttachmentFactory           $chatMessageAttachmentFactory,
        ChatMessageAttachmentCollection        $chatMessageAttachmentCollection,
        Image                                  $image
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->chatFactory = $chatFactory;
        $this->attachmentFactory = $attachmentFactory;
        $this->chatMessageCollection  = $chatMessageCollection;
        $this->chatMessageFactory = $chatMessageFactory;
        $this->ticketStatusHelper = $ticketStatusHelper;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->ticketRepositoryInterface = $ticketRepositoryInterface;
        $this->filesystem = $filesystem;
        $this->urlInterface = $urlInterface;
        $this->chatMessageAttachmentFactory = $chatMessageAttachmentFactory;
        $this->chatMessageAttachmentCollection = $chatMessageAttachmentCollection;
        $this->image = $image;
    }

    /**
     * @return array
     */
    public function getTicketMessages($ticketId)
    {
        $ticketModel = $this->ticketFactory->create();
        $ticket = $ticketModel->load($ticketId);

        $chatModel = $this->chatFactory->create();
        $chat = $chatModel->load($ticket->getId(), 'ticket_id');

        $attachmentModel = $this->attachmentFactory->create();

        $messages = $this->chatMessageCollection->create()->addFieldToFilter(
            'chat_id',
            $chat->getId()
        )->setOrder('message_id', 'ASC')
        ->getItems();
        $data = [];
        foreach ($messages as $message) {
            $messageData = [];
            $collection = $this->chatMessageAttachmentCollection->create();
            $records = $collection->addFieldToFilter(
                'message_id',
                $message->getId()
            )->getItems();
            if (!$message->getIsAdmin() && !$message->getFromId() && !$message->getEmail()) {
                $messageData['sender'] = null;
                $messageData['alertMessage'] = $message->getMessage() ? $message->getMessage() : '';
                $data[] = $messageData;
                continue;
            }
            $messageData['sender'] = $message->getIsAdmin() ? 'admin' : 'user';
            $messageData['files'] = $this->getMessageAttachments($records);
            $messageData['message'] = $message->getMessage() ? $message->getMessage() : '';
            $messageData['defaultImage'] = $this->image->getDefaultPlaceholderUrl('image');
            $messageData['userEmail'] = $message->getEmail();
            $messageData['subject'] = $ticket->getSubject();
            $data[] = $messageData;
        }
        return $data;
    }

    /**
     * @param array $chatMessageAttachmentItems
     *
     * @return array
     */
    public function getMessageAttachments($chatMessageAttachmentItems)
    {
        $filesData = [];
        foreach ($chatMessageAttachmentItems as $item) {
            $attachmentModel = $this->attachmentFactory->create();
            $attachment = $attachmentModel->load($item->getAttachmentId());
            $filesData[] = [
                "original_name" => $attachment->getOriginalName(),
                "path" => $attachment->getPath(),
                'type' => $this->getFileType($attachment->getOriginalName())
            ];
        }
        return $filesData;
    }

    /**
     * @param string $originalName
     *
     * @return string
     */
    public function getFileType($originalName)
    {
        $fileNameParts = explode('.', $originalName);
        $fileExtension = end($fileNameParts);
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    
        if (in_array($fileExtension, $imageExtensions)) {
            return 'image';
        }

        return 'file';
    }

    /**
     * @return array
     */
    public function addMessage($message, $ticketId, $fromUser = null, $fromAdmin = null, $filesData = [])
    {
        $ticketModel = $this->ticketFactory->create();
        $ticket = $ticketModel->load($ticketId);
        $isTicketClosed = $this->ticketStatusHelper->getStatusIdByLabel('closed') == $ticket->getStatusId();
        if ($isTicketClosed) {
            if (!$fromUser) {
                return [
                    'error' => 'true',
                    'message' => 'This ticket is closed.'
                ];
            }
        }
        $chatModel = $this->chatFactory->create();
        $chat = $chatModel->load($ticket->getId(), 'ticket_id');

        $chatMessageModel = $this->chatMessageFactory->create();
        $chatMessage = $chatMessageModel->load($chat->getId(), 'chat_id');

        $messageData = [
            'chat_id' => $chat->getId(),
            'email' => $chatMessage->getEmail(),
            'message' => $message,
            'attachment_id' => null
        ];
        if ($fromUser) {
            $messageData['from_id'] = $fromUser;
            $messageData['is_admin'] = null;
        } elseif ($fromAdmin) {
            $messageData['from_id'] = null;
            $messageData['is_admin'] = true;
        }
        try {
            $newMessage = $this->chatMessageFactory->create();
            $newMessage->setData($messageData)->save();

            if (!empty($filesData)) {
                foreach ($filesData as $fileData) {
                    $uniqueFileName = uniqid() . '_' . $fileData[1];
                    $mediaDirectory = $this->filesystem
                        ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                    $customAttachmentsPath = 'custom_attachments/' . $uniqueFileName;
                    $mediaDirectory->writeFile($customAttachmentsPath, $fileData[0]);
                    $mediaUrl = $this->urlInterface
                        ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
                    $filePath = $mediaUrl . $customAttachmentsPath;

                    $attachmentData = [
                        'chat_id' => $chat->getId(),
                        'original_name' => $fileData[1],
                        'unique_name' => $uniqueFileName,
                        'path' => $filePath
                    ];

                    $attachmentModel = $this->attachmentFactory->create();
                    $attachmentModel->setData($attachmentData)->save();

                    $chatMessageAttachmentModel = $this->chatMessageAttachmentFactory->create();
                    $chatMessageAttachmentData = [
                        'message_id' => $newMessage->getId(),
                        'attachment_id' => $attachmentModel->getId()
                    ];
                    $chatMessageAttachmentModel->setData($chatMessageAttachmentData)->save();
                }
            }
        } catch (\Exception $e) {
            return [
                'error' => 'true',
                'message' => 'Something went wrong.Message couldn\'t be sent.'
            ];
        }

        return ['success' => true];
    }

    /**
     * @return boolean
     */
    public function isLatestMessageFromUser($ticketId)
    {
        $ticketModel = $this->ticketFactory->create();
        $ticket = $ticketModel->load($ticketId);

        $chatModel = $this->chatFactory->create();
        $chatId = $chatModel->load($ticket->getId(), 'ticket_id')->getId();

        $latestMessage = $this->chatMessageCollection->create()->addFieldToFilter(
            'chat_id',
            $chatId
        )->setOrder('message_id', 'DESC')
        ->getFirstItem();

        return $latestMessage->getFromId() != null;
    }

    /**
     * @return int
     */
    public function getTotalUnreadMessagesFromTickets()
    {
        $sortOrder = $this->sortOrderBuilder->setField('created_at')->setDirection('DESC')->create();
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->setSortOrders([$sortOrder])->create();
        $tickets = [];
        $totalUnreadMessagesFromTickets = 0;
        foreach ($this->ticketRepositoryInterface->getList($searchCriteria)->getItems() as $ticket) {
            $chatModel = $this->chatFactory->create();
            $chatId = $chatModel->load($ticket->getEntityId(), 'ticket_id')->getId();
            $latestMessage = $this->chatMessageCollection->create()->addFieldToFilter(
                'chat_id',
                $chatId
            )->setOrder('message_id', 'DESC')
            ->getFirstItem();
            if (!$latestMessage->getIsAdmin()) {
                $totalUnreadMessagesFromTickets++;
            }
        }
        return $totalUnreadMessagesFromTickets;
    }

    /**
     * @param int
     */
    public function addTicketReopenedAlertMessage($ticketId)
    {
        $ticketModel = $this->ticketFactory->create();
        $ticket = $ticketModel->load($ticketId);

        $chatModel = $this->chatFactory->create();
        $chat = $chatModel->load($ticket->getId(), 'ticket_id');

        $chatMessageModel = $this->chatMessageFactory->create();
        $chatMessage = $chatMessageModel->load($chat->getId(), 'chat_id');

        $messageData = [
            'chat_id' => $chat->getId(),
            'message' => 'Ticket reopened by user'
        ];

        try {
            $newMessage = $this->chatMessageFactory->create();
            $newMessage->setData($messageData)->save();
        } catch (\Throwable $th) {
            throw $th;
        }

        return ['success' => true];
    }
}
