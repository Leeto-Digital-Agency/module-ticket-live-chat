<?php

namespace Leeto\TicketLiveChat\Block\Adminhtml\Chat;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class ChatList extends Template
{
    /**
     * @var string $url
     * @return string
     */
    public function getFullUrl($url)
    {
        return $this->_urlBuilder->getUrl($url);
    }
}
