<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\ChatMessageInterface;
use Magento\Framework\Model\AbstractModel;

class ChatMessage extends AbstractModel implements ChatMessageInterface
{
    public function _construct()
    {
        $this->_init(\Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage::class);
    }

    /**
     * @return string|null
     */
    public function getMessageId()
    {
        return $this->getData(self::CHATMESSAGE_ID);
    }

    /**
     * @param $messageId
     * @return Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     */
    public function setMessageId($messageId)
    {
        return $this->setData(self::CHATMESSAGE_ID, $messageId);
    }

    /**
     * @return string|null
     */
    public function getChatId()
    {
        return $this->getData(self::CHAT_ID);
    }

    /**
     * @param $chatId
     * @return Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     */
    public function setChatId($chatId)
    {
        return $this->setData(self::CHAT_ID, $chatId);
    }

    /**
     * @return string|null
     */
    public function getIsAdmin()
    {
        return $this->getData(self::IS_ADMIN);
    }

    /**
     * @param $isAdmin
     * @return Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     */
    public function setIsAdmin($isAdmin)
    {
        return $this->setData(self::IS_ADMIN, $isAdmin);
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @return string|null
     */
    public function getAttachmentId()
    {
        return $this->getData(self::ATTACHMENT_ID);
    }

    /**
     * @param $attachmentId
     * @return Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     */
    public function setAttachmentId($attachmentId)
    {
        return $this->setData(self::ATTACHMENT_ID, $attachmentId);
    }

    /**
     * @return string|null
     */
    public function getIsRead()
    {
        return $this->getData(self::IS_READ);
    }

    /**
     * @param $isRead
     * @return Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     */
    public function setIsRead($isRead)
    {
        return $this->setData(self::IS_READ, $isRead);
    }

    /**
     * @return string|null
     */
    public function getIsAlert()
    {
        return $this->getData(self::IS_ALERT);
    }

    /**
     * @param $isAlert
     * @return Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     */
    public function setIsAlert($isAlert)
    {
        return $this->setData(self::IS_ALERT, $isAlert);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
