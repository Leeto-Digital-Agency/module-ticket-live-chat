<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatMessageAttachmentSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get chat_message_attachment list.
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface[]
     */
    public function getItems();

    /**
     * Set test list.
     * @param \Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
