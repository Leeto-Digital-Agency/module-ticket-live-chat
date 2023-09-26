<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel\Chat;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'chat_id';

    protected function _construct()
    {
        $this->_init(
            \Leeto\TicketLiveChat\Model\Chat::class,
            \Leeto\TicketLiveChat\Model\ResourceModel\Chat::class
        );
    }
}
