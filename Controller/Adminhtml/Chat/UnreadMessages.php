<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Chat\ChatHelper;

class UnreadMessages extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ChatHelper
     */
    protected $chatHelper;

    /**
     * @param Context             $context
     * @param JsonFactory         $resultJsonFactory
     * @param ChatHelper          $chatHelper
     */
    public function __construct(
        Context             $context,
        JsonFactory         $resultJsonFactory,
        ChatHelper          $chatHelper
    ) {
        $this->chatHelper = $chatHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $condition = $this->chatHelper->getUnreadMessagesChatCondition();
        $data = ['unreadMessages' => $condition];
        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
