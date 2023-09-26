<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\ChatInterface;
use Magento\Framework\Model\AbstractModel;

class Chat extends AbstractModel implements ChatInterface
{
    public function _construct()
    {
        $this->_init(\Leeto\TicketLiveChat\Model\ResourceModel\Chat::class);
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
    public function getTicketId()
    {
        return $this->getData(self::TICKET_ID);
    }

    /**
     * @param $ticketId
     * @return $this
     */
    public function setTicketId($ticketId)
    {
        return $this->setData(self::TICKET_ID, $ticketId);
    }

    /**
     * @return string|null
     */
    public function getStatusId()
    {
        return $this->getData(self::STATUS_ID);
    }

    /**
     * @param $statusId
     * @return $this
     */
    public function setStatusId($statusId)
    {
        return $this->setData(self::STATUS_ID, $statusId);
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @return string|null
     */
    public function getUuid()
    {
        return $this->getData(self::UUID);
    }

    /**
     * @param $uuid
     * @return $this
     */
    public function setUuid($uuid)
    {
        return $this->setData(self::UUID, $uuid);
    }
}
