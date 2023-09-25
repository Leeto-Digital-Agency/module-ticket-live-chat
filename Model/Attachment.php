<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\AttachmentInterface;
use Magento\Framework\Model\AbstractModel;

class Attachment extends AbstractModel implements AttachmentInterface
{
    public function _construct()
    {
        $this->_init(\Leeto\TicketLiveChat\Model\ResourceModel\Attachment::class);
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
     * @return $this
     */
    public function setAttachmentId($attachmentId)
    {
        return $this->setData(self::ATTACHMENT_ID, $attachmentId);
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
     * @return $this
     */
    public function setChatId($chatId)
    {
        return $this->setData(self::CHAT_ID, $chatId);
    }

    /**
     * @return string|null
     */
    public function getOriginalName()
    {
        return $this->getData(self::ORIGINAL_NAME);
    }

    /**
     * @param $originalName
     * @return $this
     */
    public function setOriginalName($originalName)
    {
        return $this->setData(self::ORIGINAL_NAME, $originalName);
    }

    /**
     * @return string|null
     */
    public function getUniqueName()
    {
        return $this->getData(self::UNIQUE_NAME);
    }

    /**
     * @param $uniqueName
     * @return $this
     */
    public function setUniqueName($uniqueName)
    {
        return $this->setData(self::UNIQUE_NAME, $uniqueName);
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        return $this->setData(self::PATH, $path);
    }
}
