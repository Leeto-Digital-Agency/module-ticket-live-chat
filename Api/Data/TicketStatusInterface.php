<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface TicketStatusInterface
{
    public const LABEL = 'label';
    public const TICKETSTATUS_ID = 'status_id';

    /**
     * Get status_id
     * @return string|null
     */
    public function getStatusId();

    /**
     * Set status_id
     * @param string $statusId
     * @return \Leeto\TicketLiveChat\TicketStatus\Api\Data\TicketStatusInterface
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
     * @return \Leeto\TicketLiveChat\TicketStatus\Api\Data\TicketStatusInterface
     */
    public function setLabel($label);
}
