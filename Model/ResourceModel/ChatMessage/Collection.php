<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'message_id';

    protected function _construct()
    {
        $this->_init(
            \Leeto\TicketLiveChat\Model\ChatMessage::class,
            \Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage::class
        );
    }
}
