<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\ChatFactory;
use Leeto\TicketLiveChat\Model\ChatMessageFactory;
use Leeto\TicketLiveChat\Model\AttachmentFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Leeto\TicketLiveChat\Helper\Ticket\TicketTypeHelper;
use Magento\Customer\Model\CustomerFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollection;

class Message extends Action
{
    public const TEXT_TYPE_MESSAGE = "text";
    public const FILE_TYPE_MESSAGE = "file";

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var ChatFactory
     */
    protected $chatFactory;

    /**
     * @var ChatMessageFactory
     */
    protected $chatMessageFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var TicketTypeHelper
     */
    protected $ticketTypeHelper;

    /**
     * @var CustomerFactory
     */
    protected $customerModelFactory;

    /**
     * @var ChatMessageCollection
     */
    protected $chatMessageCollection;

    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param TicketFactory         $ticketFactory
     * @param ChatFactory           $chatFactory
     * @param ChatMessageFactory    $chatMessageFactory
     * @param DateTime              $dateTime
     * @param TicketTypeHelper      $ticketTypeHelper
     * @param CustomerFactory       $customerModelFactory
     * @param ChatMessageCollection $chatMessageCollection
     * @param AttachmentFactory     $attachmentFactory
     */
    public function __construct(
        Context               $context,
        JsonFactory           $resultJsonFactory,
        TicketFactory         $ticketFactory,
        ChatFactory           $chatFactory,
        ChatMessageFactory    $chatMessageFactory,
        DateTime              $dateTime,
        TicketTypeHelper      $ticketTypeHelper,
        CustomerFactory       $customerModelFactory,
        ChatMessageCollection $chatMessageCollection,
        AttachmentFactory     $attachmentFactory
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->chatFactory = $chatFactory;
        $this->chatMessageFactory = $chatMessageFactory;
        $this->dateTime = $dateTime;
        $this->ticketTypeHelper = $ticketTypeHelper;
        $this->customerModelFactory = $customerModelFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->chatMessageCollection = $chatMessageCollection;
        $this->attachmentFactory = $attachmentFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');
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
            $messageData['sender'] = $message->getIsAdmin() ? 'admin' : 'user';

            if ($message->getMessage()) {
                $messageData['message'] = $message->getMessage();
                $messageData['type'] = self::TEXT_TYPE_MESSAGE;
            } elseif ($message->getAttachmentId()) {
                // get attachment
                $attachment = $attachmentModel->load($message->getAttachmentId());
                $messageData['attachmentPath'] = $attachment->getPath();
                $messageData['originalName'] = $attachment->getOriginalName();
                $messageData['type'] = self::FILE_TYPE_MESSAGE;
            }
            $data[] = $messageData;
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
