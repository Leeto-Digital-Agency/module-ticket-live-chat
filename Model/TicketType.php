<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\TicketTypeInterface;
use Magento\Framework\Model\AbstractModel;

class TicketType extends AbstractModel implements TicketTypeInterface
{
    public function _construct()
    {
        $this->_init(\Leeto\TicketLiveChat\Model\ResourceModel\TicketType::class);
    }

    /**
     * @return int|null
     */
    public function getTypeId()
    {
        return $this->getData(self::TICKETTYPE_ID);
    }

    /**
     * @param int $typeId
     * @return TicketTypeInterface
     */
    public function setTypeId($typeId)
    {
        return $this->setData(self::TICKETTYPE_ID, $typeId);
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @param string $label
     * @return TicketTypeInterface
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }
}
