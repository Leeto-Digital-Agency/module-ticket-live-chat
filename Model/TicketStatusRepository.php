<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\Data\TicketStatusInterface;
use Leeto\TicketLiveChat\Api\Data\TicketStatusInterfaceFactory;
use Leeto\TicketLiveChat\Api\Data\TicketStatusSearchResultsInterfaceFactory;
use Leeto\TicketLiveChat\Api\TicketStatusRepositoryInterface;
use Leeto\TicketLiveChat\Model\ResourceModel\TicketStatus as ResourceTicketStatus;
use Leeto\TicketLiveChat\Model\ResourceModel\TicketStatus\CollectionFactory as TicketStatusCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TicketStatusRepository implements TicketStatusRepositoryInterface
{
    /**
     * @var ResourceTicketStatus
     */
    protected $resource;

    /**
     * @var TicketStatusInterfaceFactory
     */
    protected $ticketStatusFactory;

    /**
     * @var TicketStatusCollectionFactory
     */
    protected $ticketStatusCollectionFactory;

    /**
     * @var TicketStatus
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param ResourceTicketStatus $resource
     * @param TicketStatusInterfaceFactory $ticketStatusFactory
     * @param TicketStatusCollectionFactory $ticketStatusCollectionFactory
     * @param TicketStatusSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceTicketStatus $resource,
        TicketStatusInterfaceFactory $ticketStatusFactory,
        TicketStatusCollectionFactory $ticketStatusCollectionFactory,
        TicketStatusSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->ticketStatusFactory = $ticketStatusFactory;
        $this->ticketStatusCollectionFactory = $ticketStatusCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param TicketStatusInterface $ticketStatus
     * @return TicketStatusInterface
     * @throws CouldNotSaveException
     */
    public function save(TicketStatusInterface $ticketStatus)
    {
        try {
            $this->resource->save($ticketStatus);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ticketStatus: %1',
                $exception->getMessage()
            ));
        }
        return $ticketStatus;
    }

    /**
     * @param int $statusId
     * @return TicketStatus
     * @throws NoSuchEntityException
     */
    public function get($statusId)
    {
        $ticketStatus = $this->ticketStatusFactory->create();
        $this->resource->load($ticketStatus, $statusId);
        if (!$ticketStatus->getId()) {
            throw new NoSuchEntityException(__('TicketStatus with id "%1" does not exist.', $statusId));
        }
        return $ticketStatus;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Leeto\TicketLiveChat\Api\Data\TicketStatusSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ticketStatusCollectionFactory->create();
        
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
     * @param TicketStatusInterface $ticketStatus
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(TicketStatusInterface $ticketStatus)
    {
        try {
            $ticketStatusModel = $this->ticketStatusFactory->create();
            $this->resource->load($ticketStatusModel, $ticketStatus->getStatusId());
            $this->resource->delete($ticketStatusModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the TicketStatus: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param int $statusId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($statusId)
    {
        return $this->delete($this->get($statusId));
    }
}
