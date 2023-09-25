<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatMessageAttachmentInterface
{
    public const ENTITY_ID = 'entity_id';
    public const MESSAGE_ID = 'message_id';
    public const ATTACHMENT_ID = 'attachment_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Leeto\TicketLiveChat\ChatMessageAttachment\Api\Data\ChatMessageAttachmentInterface
     */
    public function setEntityId($entityId);

    /**
     * Get message_id
     * @return string|null
     */
    public function getMessageId();

    /**
     * Set message_id
     * @param string $messageId
     * @return \Leeto\TicketLiveChat\ChatMessageAttachment\Api\Data\ChatMessageAttachmentInterface
     */
    public function setMessageId($messageId);

    /**
     * Get attachment_id
     * @return string|null
     */
    public function getAttachmentId();

    /**
     * Set attachment_id
     * @param string $attachmentId
     * @return \Leeto\TicketLiveChat\ChatMessageAttachment\Api\Data\ChatMessageAttachmentInterface
     */
    public function setAttachmentId($attachmentId);
}
