<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatMessageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get ChatMessage list.
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageInterface[]
     */
    public function getItems();

    /**
     * Set chat_id list.
     * @param \Leeto\TicketLiveChat\Api\Data\ChatMessageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
