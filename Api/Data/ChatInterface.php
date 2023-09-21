<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatInterface
{
    public const TICKET_ID = 'ticket_id';
    public const CHAT_ID = 'chat_id';
    public const STATUS_ID = 'status_id';
    public const EMAIL = 'email';
    public const CUSTOMER_ID = 'customer_id';
    public const UUID = 'uuid';

    /**
     * Get chat_id
     * @return string|null
     */
    public function getChatId();

    /**
     * Set chat_id
     * @param string $chatId
     * @return \Leeto\TicketLiveChat\Chat\Api\Data\ChatInterface
     */
    public function setChatId($chatId);

    /**
     * Get ticket_id
     * @return string|null
     */
    public function getTicketId();

    /**
     * Set ticket_id
     * @param string $ticketId
     * @return \Leeto\TicketLiveChat\Chat\Api\Data\ChatInterface
     */
    public function setTicketId($ticketId);

    /**
     * Get status_id
     * @return string|null
     */
    public function getStatusId();

    /**
     * Set status_id
     * @param string $statusId
     * @return \Leeto\TicketLiveChat\Chat\Api\Data\ChatInterface
     */
    public function setStatusId($statusId);

    /**
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $email
     * @return \Leeto\TicketLiveChat\Chat\Api\Data\ChatInterface
     */
    public function setEmail($email);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Leeto\TicketLiveChat\Chat\Api\Data\ChatInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get uuid
     * @return string|null
     */
    public function getUuid();

    /**
     * Set uuid
     * @param string $uuid
     * @return \Leeto\TicketLiveChat\Chat\Api\Data\ChatInterface
     */
    public function setUuid($uuid);
}
