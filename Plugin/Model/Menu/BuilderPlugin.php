<?php

namespace Leeto\TicketLiveChat\Plugin\Model\Menu;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\ItemFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketMessageHelper;

class BuilderPlugin
{
    public const TICKET_MENU = 'Leeto_TicketLiveChat::leeto_menu';

    /**
     * @var ItemFactory
     */
    private $menuItemFactory;

    /**
     * @var TicketMessageHelper
     */
    private $ticketMessageHelper;

    /**
     * BuilderPlugin constructor.
     *
     * @param ItemFactory           $menuItemFactory
     * @param TicketMessageHelper   $ticketMessageHelper
     */
    public function __construct(
        ItemFactory           $menuItemFactory,
        TicketMessageHelper   $ticketMessageHelper
    ) {
        $this->menuItemFactory = $menuItemFactory;
        $this->ticketMessageHelper = $ticketMessageHelper;
    }

    /**
     * @param Builder $subject
     * @param Menu $menu
     * @return Menu
     */
    public function afterGetResult(Builder $subject, Menu $menu)
    {
        $totalUnreadMessagesFromTickets = $this->ticketMessageHelper->getTotalUnreadMessagesFromTickets();
        $item = $this->menuItemFactory->create([
            'data' => [
                'parent_id' => self::TICKET_MENU,
                'id' => 'Leeto_TicketLiveChat::leeto_tickets',
                'title' => 'Tickets (' . $totalUnreadMessagesFromTickets  . ' Unread Tickets)',
                'resource' => 'Magento_Backend::content',
                'action' => 'leeto_support/ticket/index'
            ]
        ]);
        $menu->add($item, self::TICKET_MENU);

        return $menu;
    }
}
