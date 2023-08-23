<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\TicketTypeInterface;
use Leeto\TicketLiveChat\Api\Data\TicketTypeInterfaceFactory;
use Leeto\TicketLiveChat\Api\Data\TicketTypeSearchResultsInterfaceFactory;
use Leeto\TicketLiveChat\Api\TicketTypeRepositoryInterface;
use Leeto\TicketLiveChat\Model\ResourceModel\TicketType as ResourceTicketType;
use Leeto\TicketLiveChat\Model\ResourceModel\TicketType\CollectionFactory as TicketTypeCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TicketTypeRepository implements TicketTypeRepositoryInterface
{
    /**
     * @var ResourceTicketType
     */
    protected $resource;

    /**
     * @var TicketTypeInterfaceFactory
     */
    protected $ticketTypeFactory;

    /**
     * @var TicketTypeCollectionFactory
     */
    protected $ticketTypeCollectionFactory;

    /**
     * @var TicketType
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param ResourceTicketType $resource
     * @param TicketTypeInterfaceFactory $ticketTypeFactory
     * @param TicketTypeCollectionFactory $ticketTypeCollectionFactory
     * @param TicketTypeSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceTicketType $resource,
        TicketTypeInterfaceFactory $ticketTypeFactory,
        TicketTypeCollectionFactory $ticketTypeCollectionFactory,
        TicketTypeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->ticketTypeFactory = $ticketTypeFactory;
        $this->ticketTypeCollectionFactory = $ticketTypeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param TicketTypeInterface $ticketType
     * @return TicketTypeInterface
     * @throws CouldNotSaveException
     */
    public function save(TicketTypeInterface $ticketType)
    {
        try {
            $this->resource->save($ticketType);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ticketType: %1',
                $exception->getMessage()
            ));
        }
        return $ticketType;
    }

    /**
     * @param int $typeId
     * @return TicketType
     * @throws NoSuchEntityException
     */
    public function get($typeId)
    {
        $ticketType = $this->ticketTypeFactory->create();
        $this->resource->load($ticketType, $typeId);
        if (!$ticketType->getId()) {
            throw new NoSuchEntityException(__('TicketType with id "%1" does not exist.', $typeId));
        }
        return $ticketType;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Leeto\TicketLiveChat\Api\Data\TicketTypeSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ticketTypeCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param TicketTypeInterface $ticketType
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(TicketTypeInterface $ticketType)
    {
        try {
            $ticketTypeModel = $this->ticketTypeFactory->create();
            $this->resource->load($ticketTypeModel, $ticketType->getTypeId());
            $this->resource->delete($ticketTypeModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the TicketType: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param int $typeId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($typeId)
    {
        return $this->delete($this->get($typeId));
    }
}
