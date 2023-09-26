<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Chat extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('leeto_chat', 'chat_id');
    }
}
