<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface AttachmentInterface
{
    public const CHAT_ID = 'chat_id';
    public const ORIGINAL_NAME = 'original_name';
    public const UNIQUE_NAME = 'unique_name';
    public const ATTACHMENT_ID = 'attachment_id';
    public const PATH = 'path';

    /**
     * Get attachment_id
     * @return string|null
     */
    public function getAttachmentId();

    /**
     * Set attachment_id
     * @param string $attachmentId
     * @return \Leeto\TicketLiveChat\Attachment\Api\Data\AttachmentInterface
     */
    public function setAttachmentId($attachmentId);

    /**
     * Get chat_id
     * @return string|null
     */
    public function getChatId();

    /**
     * Set chat_id
     * @param string $chatId
     * @return \Leeto\TicketLiveChat\Attachment\Api\Data\AttachmentInterface
     */
    public function setChatId($chatId);

    /**
     * Get original_name
     * @return string|null
     */
    public function getOriginalName();

    /**
     * Set original_name
     * @param string $originalName
     * @return \Leeto\TicketLiveChat\Attachment\Api\Data\AttachmentInterface
     */
    public function setOriginalName($originalName);

    /**
     * Get unique_name
     * @return string|null
     */
    public function getUniqueName();

    /**
     * Set unique_name
     * @param string $uniqueName
     * @return \Leeto\TicketLiveChat\Attachment\Api\Data\AttachmentInterface
     */
    public function setUniqueName($uniqueName);

    /**
     * Get path
     * @return string|null
     */
    public function getPath();

    /**
     * Set path
     * @param string $path
     * @return \Leeto\TicketLiveChat\Attachment\Api\Data\AttachmentInterface
     */
    public function setPath($path);
}
