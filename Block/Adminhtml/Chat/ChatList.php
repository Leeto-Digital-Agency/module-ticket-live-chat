<?php

namespace Leeto\TicketLiveChat\Block\Adminhtml\Chat;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;
use Leeto\TicketLiveChat\Helper\Chat\ChatHelper;

class ChatList extends Template
{
    /**
     * @param Context $context
     * @param ChatStatusHelper $chatStatusHelper
     * @param ChatHelper $chatHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ChatStatusHelper $chatStatusHelper,
        ChatHelper $chatHelper,
        array $data = []
    ) {
        $this->chatStatusHelper = $chatStatusHelper;
        $this->chatHelper = $chatHelper;
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

    /**
     * @return string
     */
    public function getAllowedFileExtensions()
    {
        return $this->chatHelper->getAllowedFileExtensions();
    }

    /**
     * @return string
     */
    public function getMaximumFilesSize()
    {
        return $this->chatHelper->getMaximumFilesSize();
    }
}
