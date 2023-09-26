<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatStatusSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get ChatStatus list.
     * @return \Leeto\TicketLiveChat\Api\Data\ChatStatusInterface[]
     */
    public function getItems();

    /**
     * Set label list.
     * @param \Leeto\TicketLiveChat\Api\Data\ChatStatusInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
