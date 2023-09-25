<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface;
use Magento\Framework\Model\AbstractModel;

class ChatMessageAttachment extends AbstractModel implements ChatMessageAttachmentInterface
{
    /**
     * Construct
     */
    public function _construct()
    {
        $this->_init(\Leeto\TicketLiveChat\Model\ResourceModel\ChatMessageAttachment::class);
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param $entityId
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return int
     */
    public function getMessageId()
    {
        return $this->getData(self::MESSAGE_ID);
    }
    
    /**
     * @param $messageId
     */
    public function setMessageId($messageId)
    {
        return $this->setData(self::MESSAGE_ID, $messageId);
    }

    /**
     * @return int
     */
    public function getAttachmentId()
    {
        return $this->getData(self::ATTACHMENT_ID);
    }

    /**
     * @param $attachmentId
     */
    public function setAttachmentId($attachmentId)
    {
        return $this->setData(self::ATTACHMENT_ID, $attachmentId);
    }
}
