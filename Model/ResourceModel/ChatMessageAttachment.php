<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ChatMessageAttachment extends AbstractDb
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('leeto_chat_message_attachment', 'entity_id');
    }
}
