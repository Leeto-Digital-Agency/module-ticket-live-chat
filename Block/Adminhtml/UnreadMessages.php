<?php

namespace Leeto\TicketLiveChat\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;

class UnreadMessages extends Template
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        ChatStatusHelper $chatStatusHelper,
        array $data = []
    ) {
        $this->chatStatusHelper = $chatStatusHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string 
     */
    public function getTicketUnreadMessagesUrl()
    {
        return $this->_urlBuilder->getUrl('leeto_support/ticket/unreadmessages');
    }
}
