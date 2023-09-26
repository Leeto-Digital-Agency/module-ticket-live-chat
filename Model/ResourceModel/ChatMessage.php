<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ChatMessage extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('leeto_chat_message', 'message_id');
    }
}
