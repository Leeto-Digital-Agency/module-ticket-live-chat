<?php

/**
 * Copyright © TicketLiveChat All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Setup\Patch\Data;

use Leeto\TicketLiveChat\Setup\TicketSetup;
use Leeto\TicketLiveChat\Setup\TicketSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DefaultTicketEntity implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var TicketSetup
     */
    private $ticketSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param TicketSetupFactory $ticketSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        TicketSetupFactory $ticketSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->ticketSetupFactory = $ticketSetupFactory;
    }

    /**
     * @return DataPatchInterface|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var TicketSetup $customerSetup */
        $ticketSetup = $this->ticketSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $ticketSetup->installEntities();
        
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [
        
        ];
    }
}
