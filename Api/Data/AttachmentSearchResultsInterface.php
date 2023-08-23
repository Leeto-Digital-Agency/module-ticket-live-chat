<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

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
