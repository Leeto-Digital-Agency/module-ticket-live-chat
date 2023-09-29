<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Leeto\TicketLiveChat\Api\TicketTypeRepositoryInterface;

class TicketTypeHelper extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaInterface;

    /**
     * @var TicketTypeRepositoryInterface
     */
    protected $ticketTypeRepositoryInterface;

    /**
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaInterface
     * @param TicketTypeRepositoryInterface $ticketTypeRepositoryInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaInterface,
        TicketTypeRepositoryInterface $ticketTypeRepositoryInterface
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->ticketTypeRepositoryInterface = $ticketTypeRepositoryInterface;
    }

    public function getTicketOrderTypeId()
    {
        return $this->scopeConfigInterface
            ->getValue(
                'support/ticket/order_type_ticket',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * @return int|null
     */
    public function getTicketTypeIdByLabel($label)
    {
        $labelFilter = $this->filterBuilder
            ->setField('label')
            ->setConditionType('like')
            ->setValue('%' . $label . '%')
            ->create();
        $searchCriteria = $this->searchCriteriaInterface
            ->addFilters([$labelFilter])
            ->create();

        return $this->ticketTypeRepositoryInterface->getList($searchCriteria)->getItems()[0]->getTypeId();
    }
}
