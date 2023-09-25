<?php

namespace Leeto\TicketLiveChat\Api;

interface AttachmentRepositoryInterface
{
    /**
     * Save Attachment
     * @param \Leeto\TicketLiveChat\Api\Data\AttachmentInterface $attachment
     * @return \Leeto\TicketLiveChat\Api\Data\AttachmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\AttachmentInterface $attachment
    );

    /**
     * Retrieve Attachment
     * @param string $attachmentId
     * @return \Leeto\TicketLiveChat\Api\Data\AttachmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($attachmentId);

    /**
     * Retrieve Attachment matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Leeto\TicketLiveChat\Api\Data\AttachmentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Attachment
     * @param \Leeto\TicketLiveChat\Api\Data\AttachmentInterface $attachment
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\AttachmentInterface $attachment
    );

    /**
     * Delete Attachment by ID
     * @param string $attachmentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($attachmentId);
}
