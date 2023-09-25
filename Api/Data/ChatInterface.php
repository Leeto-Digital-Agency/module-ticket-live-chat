<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatInterface
{
    public const TICKET_ID = 'ticket_id';
    public const CHAT_ID = 'chat_id';
    public const STATUS_ID = 'status_id';

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
}
