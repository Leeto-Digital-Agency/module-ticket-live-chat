<?php

namespace Leeto\TicketLiveChat\Block\Adminhtml\Chat;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;

class ChatList extends Template
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
     * @param string $url
     * @return string
     */
    public function getFullUrl($url)
    {
        return $this->_urlBuilder->getUrl($url);
    }

    /**
     * @return array
     */
    public function getChatStatuses()
    {
      return $this->chatStatusHelper->getChatStatuses();
    }
}
