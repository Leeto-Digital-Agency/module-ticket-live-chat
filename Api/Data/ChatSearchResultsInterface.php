<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Chat list.
     * @return \Leeto\TicketLiveChat\Api\Data\ChatInterface[]
     */
    public function getItems();

    /**
     * Set ticket_id list.
     * @param \Leeto\TicketLiveChat\Api\Data\ChatInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
