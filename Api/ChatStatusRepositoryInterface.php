<?php

namespace Leeto\TicketLiveChat\Api;

interface ChatStatusRepositoryInterface
{
    /**
     * Save ChatStatus
     * @param \Leeto\TicketLiveChat\Api\Data\ChatStatusInterface $chatStatus
     * @return \Leeto\TicketLiveChat\Api\Data\ChatStatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\ChatStatusInterface $chatStatus
    );

    /**
     * Retrieve ChatStatus
     * @param string $statusId
     * @return \Leeto\TicketLiveChat\Api\Data\ChatStatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($statusId);

    /**
     * Retrieve ChatStatus matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Leeto\TicketLiveChat\Api\Data\ChatStatusSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ChatStatus
     * @param \Leeto\TicketLiveChat\Api\Data\ChatStatusInterface $chatStatus
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\ChatStatusInterface $chatStatus
    );

    /**
     * Delete ChatStatus by ID
     * @param string $statusId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($statusId);
}
