<?php

/**
 * Copyright © TicketLiveChat All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Model\ResourceModel;

class Ticket extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setType('leeto_ticket_entity');
    }
}
