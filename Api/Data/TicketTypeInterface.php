<?php

namespace Leeto\TicketLiveChat\Api\Data;

interface TicketTypeInterface
{
    public const LABEL = 'label';
    public const TICKETTYPE_ID = 'type_id';

    /**
     * Get type_id
     * @return string|null
     */
    public function getTypeId();

    /**
     * Set type_id
     * @param string $typeId
     * @return \Leeto\TicketLiveChat\TicketType\Api\Data\TicketTypeInterface
     */
    public function setTypeId($typeId);

    /**
     * Get label
     * @return string|null
     */
    public function getLabel();

    /**
     * Set label
     * @param string $label
     * @return \Leeto\TicketLiveChat\TicketType\Api\Data\TicketTypeInterface
     */
    public function setLabel($label);
}
