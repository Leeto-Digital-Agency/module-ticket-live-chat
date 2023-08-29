<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class TicketTypeHelper extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @param ScopeConfigInterface $scopeConfigInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    public function getTicketOrderTypeId()
    {
        return $this->scopeConfigInterface
            ->getValue(
                'support/ticket/order_type_ticket',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}
