<?php

namespace Leeto\TicketLiveChat\Api;

interface ChatRepositoryInterface
{
    /**
     * Save Chat
     * @param \Leeto\TicketLiveChat\Api\Data\ChatInterface $chat
     * @return \Leeto\TicketLiveChat\Api\Data\ChatInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\ChatInterface $chat
    );

    /**
     * Retrieve Chat
     * @param string $chatId
     * @return \Leeto\TicketLiveChat\Api\Data\ChatInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($chatId);

    /**
     * Retrieve Chat matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Leeto\TicketLiveChat\Api\Data\ChatSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Chat
     * @param \Leeto\TicketLiveChat\Api\Data\ChatInterface $chat
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\ChatInterface $chat
    );

    /**
     * Delete Chat by ID
     * @param string $chatId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($chatId);
}
