<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface TicketSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Ticket list.
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface[]
     */
    public function getItems();

    /**
     * Set title list.
     * @param \Leeto\TicketLiveChat\Api\Data\TicketInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
