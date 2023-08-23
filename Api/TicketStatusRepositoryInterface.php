<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface TicketStatusRepositoryInterface
{
    /**
     * Save TicketStatus
     * @param \Leeto\TicketLiveChat\Api\Data\TicketStatusInterface $ticketStatus
     * @return \Leeto\TicketLiveChat\Api\Data\TicketStatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\TicketStatusInterface $ticketStatus
    );

    /**
     * Retrieve TicketStatus
     * @param string $statusId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketStatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($statusId);

    /**
     * Retrieve TicketStatus matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Leeto\TicketLiveChat\Api\Data\TicketStatusSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete TicketStatus
     * @param \Leeto\TicketLiveChat\Api\Data\TicketStatusInterface $ticketStatus
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\TicketStatusInterface $ticketStatus
    );

    /**
     * Delete TicketStatus by ID
     * @param string $statusId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($statusId);
}
