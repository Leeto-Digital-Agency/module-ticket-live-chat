<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Api\Data;

interface ChatStatusInterface
{
    public const LABEL = 'label';
    public const CHATSTATUS_ID = 'status_id';

    /**
     * Get status_id
     * @return string|null
     */
    public function getStatusId();

    /**
     * Set status_id
     * @param string $statusId
     * @return \Leeto\TicketLiveChat\ChatStatus\Api\Data\ChatStatusInterface
     */
    public function setStatusId($statusId);

    /**
     * Get label
     * @return string|null
     */
    public function getLabel();

    /**
     * Set label
     * @param string $label
     * @return \Leeto\TicketLiveChat\ChatStatus\Api\Data\ChatStatusInterface
     */
    public function setLabel($label);
}
