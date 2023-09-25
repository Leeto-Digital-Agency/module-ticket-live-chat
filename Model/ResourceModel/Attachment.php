<?php

namespace Leeto\TicketLiveChat\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Attachment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('leeto_chat_attachment', 'attachment_id');
    }
}
