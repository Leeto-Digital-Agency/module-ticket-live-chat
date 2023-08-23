<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

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
