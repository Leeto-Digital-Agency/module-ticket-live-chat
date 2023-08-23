<?php

/**
 * Copyright © TicketLiveChat All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Setup;

use Magento\Eav\Setup\EavSetup;

class TicketSetup extends EavSetup
{
    public function getDefaultEntities()
    {
        return [
            \Leeto\TicketLiveChat\Model\Ticket::ENTITY => [
                'entity_model' => \Leeto\TicketLiveChat\Model\ResourceModel\Ticket::class,
                'table' => 'leeto_ticket_entity',
                'attributes' => [
                    'customer_id' => [
                        'type' => 'int',
                        'label' => 'Customer Id',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'group' => 'General',
                    ],
                    'status_id' => [
                        'type' => 'int',
                        'label' => 'Status Id',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'group' => 'General',
                    ],
                    'ticket_type_id' => [
                        'type' => 'int',
                        'label' => 'Ticket Type Id',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'group' => 'General',
                    ],
                    'order_id' => [
                        'type' => 'int',
                        'label' => 'Order Id',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'group' => 'General',
                    ],
                    'subject' => [
                        'type' => 'varchar',
                        'label' => 'Status Id',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'group' => 'General',
                    ],
                    'email' => [
                        'type' => 'varchar',
                        'label' => 'Email',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 50,
                        'group' => 'General',
                    ]
                ]
            ]
        ];
    }
}
