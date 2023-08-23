<?php

/**
 * Copyright © TicketLiveChat All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface TicketRepositoryInterface
{
    /**
     * Save Ticket
     * @param \Leeto\TicketLiveChat\Api\Data\TicketInterface $ticket
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\TicketInterface $ticket
    );

    /**
     * Retrieve Ticket
     * @param string $entityId
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve Ticket matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Leeto\TicketLiveChat\Api\Data\TicketSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Ticket
     * @param \Leeto\TicketLiveChat\Api\Data\TicketInterface $ticket
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\TicketInterface $ticket
    );

    /**
     * Delete Ticket by ID
     * @param string $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);
}
