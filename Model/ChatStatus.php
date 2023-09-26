<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\ChatStatusInterface;
use Magento\Framework\Model\AbstractModel;

class ChatStatus extends AbstractModel implements ChatStatusInterface
{
    public function _construct()
    {
        $this->_init(\Leeto\TicketLiveChat\Model\ResourceModel\ChatStatus::class);
    }

    /**
     * @return string|null
     */
    public function getStatusId()
    {
        return $this->getData(self::CHATSTATUS_ID);
    }

    /**
     * @param $statusId
     * @return $this
     */
    public function setStatusId($statusId)
    {
        return $this->setData(self::CHATSTATUS_ID, $statusId);
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @param $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }
}
