<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel\Attachment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'attachment_id';

    protected function _construct()
    {
        $this->_init(
            \Leeto\TicketLiveChat\Model\Attachment::class,
            \Leeto\TicketLiveChat\Model\ResourceModel\Attachment::class
        );
    }
}
