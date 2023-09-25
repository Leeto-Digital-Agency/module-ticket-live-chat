<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\TicketStatusInterface;
use Magento\Framework\Model\AbstractModel;

class TicketStatus extends AbstractModel implements TicketStatusInterface
{
    public function _construct()
    {
        $this->_init(\Leeto\TicketLiveChat\Model\ResourceModel\TicketStatus::class);
    }

    /**
     * @return int|null
     */
    public function getStatusId()
    {
        return $this->getData(self::TICKETSTATUS_ID);
    }

    /**
     * @param int $statusId
     * @return TicketStatusInterface
     */
    public function setStatusId($statusId)
    {
        return $this->setData(self::TICKETSTATUS_ID, $statusId);
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
     * @return TicketStatusInterface
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }
}
