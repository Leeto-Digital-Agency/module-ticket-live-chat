<?php

namespace Leeto\TicketLiveChat\Block\Ticket;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Create extends Template
{
    /**
     * Construct
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array   $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getReturnUrl()
    {
        return $this->getUrl('/');
    }
}
