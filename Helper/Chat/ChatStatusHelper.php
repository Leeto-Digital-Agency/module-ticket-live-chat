<?php

namespace Leeto\TicketLiveChat\Helper\Chat;

use Magento\Framework\App\Helper\AbstractHelper;
use Leeto\TicketLiveChat\Api\ChatStatusRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ChatStatusHelper extends AbstractHelper
{
    /**
     * @var ChatStatusRepositoryInterface
     */
    protected $chatStatusRepositoryInterface;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaInterface;

    /**
     * @param ChatStatusRepositoryInterface   $chatStatusRepositoryInterface
     * @param FilterBuilder                     $filterBuilder
     * @param SearchCriteriaBuilder           $searchCriteriaInterface
     */
    public function __construct(
        ChatStatusRepositoryInterface $chatStatusRepositoryInterface,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaInterface
    ) {
        $this->chatStatusRepositoryInterface = $chatStatusRepositoryInterface;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
    }

    public function getOnGoingStatusId()
    {
        $labelFilter = $this->filterBuilder
            ->setField('label') // Change this to the actual field name in your table
            ->setConditionType('like')
            ->setValue('%ongoing%') // Change this value according to your needs
            ->create();
        $searchCriteria = $this->searchCriteriaInterface
            ->addFilters([$labelFilter])
            ->create();

        return $this->chatStatusRepositoryInterface->getList($searchCriteria)->getItems()[0]->getStatusId();
    }
}
