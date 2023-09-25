<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel\TicketType;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'type_id';

    protected function _construct()
    {
        $this->_init(
            \Leeto\TicketLiveChat\Model\TicketType::class,
            \Leeto\TicketLiveChat\Model\ResourceModel\TicketType::class
        );
    }
}
