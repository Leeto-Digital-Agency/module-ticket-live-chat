<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\ChatFactory;
use Leeto\TicketLiveChat\Model\ChatMessageFactory;
use Leeto\TicketLiveChat\Model\AttachmentFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketTypeHelper;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollection;

class AddAdminMessage extends Action
{
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
     * @var TicketTypeHelper
     */
    protected $ticketTypeHelper;

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
     * @param TicketTypeHelper      $ticketTypeHelper
     * @param ChatMessageCollection $chatMessageCollection
     * @param AttachmentFactory     $attachmentFactory
     */
    public function __construct(
        Context               $context,
        JsonFactory           $resultJsonFactory,
        TicketFactory         $ticketFactory,
        ChatFactory           $chatFactory,
        ChatMessageFactory    $chatMessageFactory,
        TicketTypeHelper      $ticketTypeHelper,
        ChatMessageCollection $chatMessageCollection,
        AttachmentFactory     $attachmentFactory
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->chatFactory = $chatFactory;
        $this->chatMessageFactory = $chatMessageFactory;
        $this->ticketTypeHelper = $ticketTypeHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->chatMessageCollection = $chatMessageCollection;
        $this->attachmentFactory = $attachmentFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $message = $this->getRequest()->getParam('message');
            $ticketId = $this->getRequest()->getParam('ticket_id');
            $ticketModel = $this->ticketFactory->create();
            $ticket = $ticketModel->load($ticketId);

            $chatModel = $this->chatFactory->create();
            $chat = $chatModel->load($ticket->getId(), 'ticket_id');

            $chatMessageModel = $this->chatMessageFactory->create();
            $chatMessage = $chatMessageModel->load($chat->getId(), 'chat_id');

            $messageData = [
                'chat_id' => $chat->getId(),
                'from_id' => null,
                'is_admin' => true,
                'email' => $chatMessage->getEmail(),
                'message' => $message,
                'attachment_id' => null
            ];
            $newMessage = $this->chatMessageFactory->create();
            $newMessage->setData($messageData)->save();
        } catch (\Exception $e) {
            $result = $this->resultJsonFactory->create();
            return $result->setData(['error' => $e->getMessage()]);
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true]);
    }
}
