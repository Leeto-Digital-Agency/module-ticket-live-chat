<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel\Ticket;

class Collection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Leeto\TicketLiveChat\Model\Ticket::class,
            \Leeto\TicketLiveChat\Model\ResourceModel\Ticket::class
        );
    }
}
