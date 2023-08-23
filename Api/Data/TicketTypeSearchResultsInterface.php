<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api\Data;

interface TicketTypeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get TicketType list.
     * @return \Leeto\TicketLiveChat\Api\Data\TicketTypeInterface[]
     */
    public function getItems();

    /**
     * Set label list.
     * @param \Leeto\TicketLiveChat\Api\Data\TicketTypeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
