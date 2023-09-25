<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface AttachmentSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Attachment list.
     * @return \Leeto\TicketLiveChat\Api\Data\AttachmentInterface[]
     */
    public function getItems();

    /**
     * Set chat_id list.
     * @param \Leeto\TicketLiveChat\Api\Data\AttachmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
