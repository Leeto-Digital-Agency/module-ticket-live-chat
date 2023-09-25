<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel\ChatStatus;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'status_id';

    protected function _construct()
    {
        $this->_init(
            \Leeto\TicketLiveChat\Model\ChatStatus::class,
            \Leeto\TicketLiveChat\Model\ResourceModel\ChatStatus::class
        );
    }
}
