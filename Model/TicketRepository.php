<?php

/**
 * Copyright © TicketLiveChat All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\TicketInterfaceFactory;
use Leeto\TicketLiveChat\Api\Data\TicketSearchResultsInterfaceFactory;
use Leeto\TicketLiveChat\Api\TicketRepositoryInterface;
use Leeto\TicketLiveChat\Model\ResourceModel\Ticket as ResourceTicket;
use Leeto\TicketLiveChat\Model\ResourceModel\Ticket\CollectionFactory as TicketCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class TicketRepository implements TicketRepositoryInterface
{
    protected $resource;

    protected $ticketFactory;

    protected $ticketCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataTicketFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceTicket $resource
     * @param TicketFactory $ticketFactory
     * @param TicketInterfaceFactory $dataTicketFactory
     * @param TicketCollectionFactory $ticketCollectionFactory
     * @param TicketSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceTicket $resource,
        TicketFactory $ticketFactory,
        TicketInterfaceFactory $dataTicketFactory,
        TicketCollectionFactory $ticketCollectionFactory,
        TicketSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->ticketFactory = $ticketFactory;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTicketFactory = $dataTicketFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param \Leeto\TicketLiveChat\Api\Data\TicketInterface $ticket
     * @return \Leeto\TicketLiveChat\Api\Data\TicketInterface
     * @throws CouldNotSaveException
     */
    public function save(
        \Leeto\TicketLiveChat\Api\Data\TicketInterface $ticket
    ) {
        /* if (empty($ticket->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $ticket->setStoreId($storeId);
        } */
        
        $ticketData = $this->extensibleDataObjectConverter->toNestedArray(
            $ticket,
            [],
            \Leeto\TicketLiveChat\Api\Data\TicketInterface::class
        );
        
        $ticketModel = $this->ticketFactory->create()->setData($ticketData);
        
        try {
            $this->resource->save($ticketModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ticket: %1',
                $exception->getMessage()
            ));
        }
        return $ticketModel->getDataModel();
    }

    /**
     * @param $ticketId
     * @return Ticket
     * @throws NoSuchEntityException
     */
    public function get($ticketId)
    {
        $ticket = $this->ticketFactory->create();
        $this->resource->load($ticket, $ticketId);
        if (!$ticket->getId()) {
            throw new NoSuchEntityException(__('Ticket with id "%1" does not exist.', $ticketId));
        }
        return $ticket->getDataModel();
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Leeto\TicketLiveChat\Api\Data\TicketSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ticketCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Leeto\TicketLiveChat\Api\Data\TicketInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param \Leeto\TicketLiveChat\Api\Data\TicketInterface $ticket
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(
        \Leeto\TicketLiveChat\Api\Data\TicketInterface $ticket
    ) {
        try {
            $ticketModel = $this->ticketFactory->create();
            $this->resource->load($ticketModel, $ticket->getEntityId());
            $this->resource->delete($ticketModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Ticket: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $ticketId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($ticketId)
    {
        return $this->delete($this->get($ticketId));
    }
}
