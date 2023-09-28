<?php

namespace Leeto\TicketLiveChat\Model\Config\Source\Chat;

use Magento\Framework\Option\ArrayInterface;
use Leeto\TicketLiveChat\Api\ChatStatusRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class Position implements ArrayInterface
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
        return [
            [
                'value' => 'left',
                'label' => 'Left'
            ],
            [
                'value' => 'center',
                'label' => 'Center'
            ],
            [
                'value' => 'right',
                'label' => 'Right'
            ]
        ];
    }
}
