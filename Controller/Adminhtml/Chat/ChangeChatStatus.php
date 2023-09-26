<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;
use Leeto\TicketLiveChat\Helper\Chat\ChatHelper;

class ChangeChatStatus extends Action
{
    /**
     * @var string
     */
    public const TICKET_SUBJECT_FROM_ADMIN_CHAT = 'Ticket from admin';

    /**
     * @var string
     */
    public const TICKET_STATUS_CREATED_FROM_ADMIN_CHAT = 'pending';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ChatStatusHelper
     */
    protected $chatStatusHelper;

    /**
     * @var ChatHelper
     */
    protected $chatHelper;

    /**
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param ChatStatusHelper      $chatStatusHelper
     * @param ChatHelper            $chatHelper
     */
    public function __construct(
        Context               $context,
        JsonFactory           $resultJsonFactory,
        ChatStatusHelper      $chatStatusHelper,
        ChatHelper            $chatHelper
    ) {
        $this->chatStatusHelper = $chatStatusHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->chatHelper = $chatHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $newChatStatusId = $this->getRequest()->getParam('statusValue');
            $chatId = $this->getRequest()->getParam('chatId');
            $data = $this->chatStatusHelper->changeChatStatus($newChatStatusId, $chatId);
            if (isset($data['error'])) {
                return $result->setData($data);
            }
            // Create ticket from this chat with status pending by default
            $data = $this->chatHelper->createTicket(
                $chatId, self::TICKET_SUBJECT_FROM_ADMIN_CHAT, false, self::TICKET_STATUS_CREATED_FROM_ADMIN_CHAT
            );
            return $result->setData($data);
        } catch (\Exception $e) {
            return $result->setData(['error' => $e->getMessage()]);
        }
    }
}