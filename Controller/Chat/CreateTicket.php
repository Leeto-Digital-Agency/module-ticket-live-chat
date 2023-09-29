<?php

namespace Leeto\TicketLiveChat\Controller\Chat;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Chat\ChatHelper;

class CreateTicket extends Action
{
    /**
     * @var string
     */
    public const TICKET_SUBJECT_FROM_CUSTOMER_CHAT = 'Ticket from chat';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ChatHelper
     */
    protected $chatHelper;

    /**
     * @param Context                      $context
     * @param JsonFactory                  $resultJsonFactory
     */
    public function __construct(
        Context                      $context,
        JsonFactory                  $resultJsonFactory,
        ChatHelper                   $chatHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->chatHelper = $chatHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $chatId = $this->getRequest()->getParam('chatId');
        $chatStatusButtonId = $this->getRequest()->getParam('ticketStatus');
        $chatStatusLabel = $chatStatusButtonId && intval($chatStatusButtonId) === 1 ? 'opened' : 'closed';
        $data = $this->chatHelper->createTicket(
            $chatId,
            self::TICKET_SUBJECT_FROM_CUSTOMER_CHAT,
            false,
            $chatStatusLabel
        );
        
        return $result->setData($data);
    }
}
