<?php

namespace Leeto\TicketLiveChat\Api;

interface TicketTypeRepositoryInterface
{
    /**
     * Save TicketType
     * @param \Leeto\TicketLiveChat\Api\Data\TicketTypeInterface $ticketType
     * @return \Leeto\TicketLiveChat\Api\Data\TicketTypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\TicketTypeInterface $ticketType
    );

    /**
     * Retrieve TicketType
     * @param string $typeId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketTypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($typeId);

    /**
     * Retrieve TicketType matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Leeto\TicketLiveChat\Api\Data\TicketTypeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete TicketType
     * @param \Leeto\TicketLiveChat\Api\Data\TicketTypeInterface $ticketType
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\TicketTypeInterface $ticketType
    );

    /**
     * Delete TicketType by ID
     * @param string $typeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($typeId);
}
