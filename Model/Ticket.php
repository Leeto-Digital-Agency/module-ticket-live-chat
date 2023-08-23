<?php

/**
 * Copyright © TicketLiveChat All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\TicketInterface;
use Leeto\TicketLiveChat\Api\Data\TicketInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Ticket extends \Magento\Framework\Model\AbstractModel
{
    public const ENTITY = 'leeto_ticket_entity';

    protected $ticketDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'leeto_ticket_entity';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param TicketInterfaceFactory $ticketDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Leeto\TicketLiveChat\Model\ResourceModel\Ticket $resource
     * @param \Leeto\TicketLiveChat\Model\ResourceModel\Ticket\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        TicketInterfaceFactory $ticketDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Leeto\TicketLiveChat\Model\ResourceModel\Ticket $resource,
        \Leeto\TicketLiveChat\Model\ResourceModel\Ticket\Collection $resourceCollection,
        array $data = []
    ) {
        $this->ticketDataFactory = $ticketDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve ticket model with ticket data
     * @return TicketInterface
     */
    public function getDataModel()
    {
        $ticketData = $this->getData();
        
        $ticketDataObject = $this->ticketDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $ticketDataObject,
            $ticketData,
            TicketInterface::class
        );

        return $ticketDataObject;
    }
}
