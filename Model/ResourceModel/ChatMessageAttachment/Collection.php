<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel\ChatMessageAttachment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    
    protected function _construct()
    {
        $this->_init(
            \Leeto\TicketLiveChat\Model\ChatMessageAttachment::class,
            \Leeto\TicketLiveChat\Model\ResourceModel\ChatMessageAttachment::class
        );
    }
}
