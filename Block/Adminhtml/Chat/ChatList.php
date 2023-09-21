<?php

namespace Leeto\TicketLiveChat\Block\Adminhtml\Chat;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class ChatList extends Template
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getFullUrl($url)
    {
        return $this->_urlBuilder->getUrl($url);
    }
}
