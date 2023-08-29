<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\AbstractHelper;
use Leeto\TicketLiveChat\Api\TicketStatusRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

class TicketStatusHelper extends AbstractHelper
{
    /**
     * @var TicketStatusRepositoryInterface
     */
    protected $ticketStatusRepositoryInterface;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaInterface;

    /**
     * @param TicketStatusRepositoryInterface   $ticketStatusRepositoryInterface
     * @param FilterBuilder                     $filterBuilder
     * @param SearchCriteriaBuilder           $searchCriteriaInterface
     */
    public function __construct(
        TicketStatusRepositoryInterface $ticketStatusRepositoryInterface,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaInterface
    ) {
        $this->ticketStatusRepositoryInterface = $ticketStatusRepositoryInterface;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
    }

    public function getOpenedStatusId()
    {
        $labelFilter = $this->filterBuilder
            ->setField('label') // Change this to the actual field name in your table
            ->setConditionType('like')
            ->setValue('%opened%') // Change this value according to your needs
            ->create();
        $searchCriteria = $this->searchCriteriaInterface
            ->addFilters([$labelFilter])
            ->create();

        return $this->ticketStatusRepositoryInterface->getList($searchCriteria)->getItems()[0]->getStatusId();
    }
}
