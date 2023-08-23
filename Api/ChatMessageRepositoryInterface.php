<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ChatMessageRepositoryInterface
{
    /**
     * Save ChatMessage
     * @param \Leeto\TicketLiveChat\Api\Data\ChatMessageInterface $chatMessage
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\ChatMessageInterface $chatMessage
    );

    /**
     * Retrieve ChatMessage
     * @param string $messageId
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($messageId);

    /**
     * Retrieve ChatMessage matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ChatMessage
     * @param \Leeto\TicketLiveChat\Api\Data\ChatMessageInterface $chatMessage
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\ChatMessageInterface $chatMessage
    );

    /**
     * Delete ChatMessage by ID
     * @param string $messageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($messageId);
}
