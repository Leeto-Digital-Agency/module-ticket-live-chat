<?php

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
