<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TicketType extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('leeto_ticket_type', 'type_id');
    }
}
