<?php

namespace Leeto\TicketLiveChat\Block\Ticket;

use Magento\Framework\View\Element\Template;

class Create extends Template
{
    public function getReturnUrl()
    {
        return $this->getUrl('/');
    }
}
