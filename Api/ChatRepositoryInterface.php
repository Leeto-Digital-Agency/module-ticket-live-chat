<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

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
