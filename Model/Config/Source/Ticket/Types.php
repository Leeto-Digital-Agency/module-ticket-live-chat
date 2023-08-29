<?php

namespace Leeto\TicketLiveChat\Model\Config\Source\Ticket;

use Magento\Framework\Option\ArrayInterface;
use Leeto\TicketLiveChat\Api\TicketTypeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class Types implements ArrayInterface
{
    /**
     * @var TicketTypeRepositoryInterface
     */
    protected $ticketRepositoryInterface;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    public function __construct(
        TicketTypeRepositoryInterface $ticketRepositoryInterface,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->ticketRepositoryInterface = $ticketRepositoryInterface;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResults = $this->ticketRepositoryInterface->getList($searchCriteria);
        
        foreach ($searchResults->getItems() as $item) {
            $options[] = [
                'value' => $item->getTypeId(),
                'label' => $item->getLabel()
            ];
        }

        return $options;
    }
}
