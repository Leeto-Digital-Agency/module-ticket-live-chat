<?php

namespace Leeto\TicketLiveChat\Api;

interface ChatMessageAttachmentRepositoryInterface
{
    /**
     * Save chat_message_attachment
     * @param \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface $chatMessageAttachment
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface $chatMessageAttachment
    );

    /**
     * Retrieve chat_message_attachment
     * @param string $chatMessageAttachmentId
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($chatMessageAttachmentId);

    /**
     * Retrieve chat_message_attachment matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete chat_message_attachment
     * @param \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface $chatMessageAttachment
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface $chatMessageAttachment
    );

    /**
     * Delete chat_message_attachment by ID
     * @param string $chatMessageAttachmentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($chatMessageAttachmentId);
}
