<?php

namespace Leeto\TicketLiveChat\Model\Data;

use Leeto\TicketLiveChat\Api\Data\TicketInterface;

class Ticket extends \Magento\Framework\Api\AbstractExtensibleObject implements TicketInterface
{
    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param $entityId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param $customerId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get subject
     * @return string|null
     */
    public function getSubject()
    {
        return $this->_get(self::SUBJECT);
    }

    /**
     * Set subject
     * @param $subject
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setSubject($subject)
    {
        return $this->setData(self::SUBJECT, $subject);
    }

    /**
     * Get email
     * @return string|null
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Set email
     * @param $email
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get closed_at
     * @return string|null
     */
    public function getClosedAt()
    {
        return $this->_get(self::CLOSED_AT);
    }

    /**
     * Set closed_at
     * @param $closedAt
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setClosedAt($closedAt)
    {
        return $this->setData(self::CLOSED_AT, $closedAt);
    }

    /**
     * Get status_id
     * @return string|null
     */
    public function getStatusId()
    {
        return $this->_get(self::STATUS_ID);
    }

    /**
     * Set status_id
     * @param $statusId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setStatusId($statusId)
    {
        return $this->setData(self::STATUS_ID, $statusId);
    }

    /**
     * Get type_id
     * @return string|null
     */
    public function getTypeId()
    {
        return $this->_get(self::TICKET_TYPE_ID);
    }

    /**
     * Set type_id
     * @param $typeId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setTypeId($typeId)
    {
        return $this->setData(self::TICKET_TYPE_ID, $typeId);
    }

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param $orderId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Leeto\TicketLiveChat\Api\Data\TicketExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Leeto\TicketLiveChat\Api\Data\TicketExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Leeto\TicketLiveChat\Api\Data\TicketExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
