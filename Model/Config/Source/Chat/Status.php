<?php

namespace Leeto\TicketLiveChat\Model\Config\Source\Chat;

use Magento\Framework\Option\ArrayInterface;
use Leeto\TicketLiveChat\Api\ChatStatusRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class Status implements ArrayInterface
{
    /**
     * @var ChatStatusRepositoryInterface
     */
    protected $chatStatusRepositoryInterface;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    public function __construct(
        ChatStatusRepositoryInterface $chatStatusRepositoryInterface,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->chatStatusRepositoryInterface = $chatStatusRepositoryInterface;
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
        $searchResults = $this->chatStatusRepositoryInterface->getList($searchCriteria);
        
        foreach ($searchResults->getItems() as $item) {
            $options[] = [
                'value' => $item->getStatusId(),
                'label' => $item->getLabel()
            ];
        }

        return $options;
    }
}
