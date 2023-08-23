<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api\Data;

interface TicketStatusSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get TicketStatus list.
     * @return \Leeto\TicketLiveChat\Api\Data\TicketStatusInterface[]
     */
    public function getItems();

    /**
     * Set label list.
     * @param \Leeto\TicketLiveChat\Api\Data\TicketStatusInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
