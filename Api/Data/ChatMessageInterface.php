<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatMessageInterface
{
    public const CHAT_ID = 'chat_id';
    public const IS_ADMIN = 'is_admin';
    public const MESSAGE = 'message';
    public const CHATMESSAGE_ID = 'message_id';
    public const ATTACHMENT_ID = 'attachment_id';
    public const IS_READ = 'is_read';
    public const CREATED_AT = 'created_at';

    /**
     * Get message_id
     * @return string|null
     */
    public function getMessageId();

    /**
     * Set message_id
     * @param string $messageId
     * @return \Leeto\TicketLiveChat\ChatMessage\Api\Data\ChatMessageInterface
     */
    public function setMessageId($messageId);

    /**
     * Get chat_id
     * @return string|null
     */
    public function getChatId();

    /**
     * Set chat_id
     * @param string $chatId
     * @return \Leeto\TicketLiveChat\ChatMessage\Api\Data\ChatMessageInterface
     */
    public function setChatId($chatId);

    /**
     * Get is_admin
     * @return string|null
     */
    public function getIsAdmin();

    /**
     * Set is_admin
     * @param string $isAdmin
     * @return \Leeto\TicketLiveChat\ChatMessage\Api\Data\ChatMessageInterface
     */
    public function setIsAdmin($isAdmin);

    /**
     * Get message
     * @return string|null
     */
    public function getMessage();

    /**
     * Set message
     * @param string $message
     * @return \Leeto\TicketLiveChat\ChatMessage\Api\Data\ChatMessageInterface
     */
    public function setMessage($message);

    /**
     * Get attachment_id
     * @return string|null
     */
    public function getAttachmentId();

    /**
     * Set attachment_id
     * @param string $attachmentId
     * @return \Leeto\TicketLiveChat\ChatMessage\Api\Data\ChatMessageInterface
     */
    public function setAttachmentId($attachmentId);

    /**
     * Set is_read
     * @param string $isRead
     * @return \Leeto\TicketLiveChat\ChatMessage\Api\Data\ChatMessageInterface
     */
    public function setIsRead($isRead);

    /**
     * Get is_read
     * @return string|null
     */
    public function getIsRead();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Leeto\TicketLiveChat\ChatMessage\Api\Data\ChatMessageInterface
     */
    public function setCreatedAt($createdAt);
    
    /**
     * Get attachment_id
     * @return string|null
     */
    public function getCreatedAt();
}
