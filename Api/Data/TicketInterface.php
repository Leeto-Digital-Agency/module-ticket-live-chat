<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api\Data;

interface TicketInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const ENTITY_ID = 'entity_id';
    public const CUSTOMER_ID = 'customer_id';
    public const SUBJECT = 'subject';
    public const EMAIL = 'email';
    public const CLOSED_AT = 'closed_at';
    public const STATUS_ID = 'status_id';
    public const TICKET_TYPE_ID = 'ticket_type_id';
    public const ORDER_ID = 'order_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setEntityId($entityId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get subject
     * @return string|null
     */
    public function getSubject();

    /**
     * Set subject
     * @param string $subject
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setSubject($subject);

    /**
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $email
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setEmail($email);

    /**
     * Get closed_at
     * @return string|null
     */
    public function getClosedAt();

    /**
     * Set closed_at
     * @param string $closedAt
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setClosedAt($closedAt);

    /**
     * Get status_id
     * @return string|null
     */
    public function getStatusId();

    /**
     * Set status_id
     * @param string $statusId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setStatusId($statusId);

    /**
     * Get ticket_type_id
     * @return string|null
     */
    public function getTypeId();

    /**
     * Set ticket_type_id
     * @param string $typeId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setTypeId($typeId);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     */
    public function setOrderId($orderId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Leeto\TicketLiveChat\Api\Data\TicketExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Leeto\TicketLiveChat\Api\Data\TicketExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Leeto\TicketLiveChat\Api\Data\TicketExtensionInterface $extensionAttributes
    );
}
