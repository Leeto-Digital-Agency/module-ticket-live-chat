<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\ChatStatusRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\ChatStatusInterface;
use Leeto\TicketLiveChat\Api\Data\ChatStatusInterfaceFactory;
use Leeto\TicketLiveChat\Api\Data\ChatStatusSearchResultsInterfaceFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatStatus as ResourceChatStatus;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatStatus\CollectionFactory as ChatStatusCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ChatStatusRepository implements ChatStatusRepositoryInterface
{
    /**
     * @var ResourceChatStatus
     */
    protected $resource;

    /**
     * @var ChatStatusInterfaceFactory
     */
    protected $chatStatusFactory;

    /**
     * @var ChatStatusCollectionFactory
     */
    protected $chatStatusCollectionFactory;

    /**
     * @var ChatStatus
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param ResourceChatStatus $resource
     * @param ChatStatusInterfaceFactory $chatStatusFactory
     * @param ChatStatusCollectionFactory $chatStatusCollectionFactory
     * @param ChatStatusSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceChatStatus $resource,
        ChatStatusInterfaceFactory $chatStatusFactory,
        ChatStatusCollectionFactory $chatStatusCollectionFactory,
        ChatStatusSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->chatStatusFactory = $chatStatusFactory;
        $this->chatStatusCollectionFactory = $chatStatusCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param ChatStatusInterface $chatStatus
     * @return ChatStatusInterface
     * @throws CouldNotSaveException
     */
    public function save(ChatStatusInterface $chatStatus)
    {
        try {
            $this->resource->save($chatStatus);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the chatStatus: %1',
                $exception->getMessage()
            ));
        }
        return $chatStatus;
    }

    /**
     * @param $statusId
     * @return ChatStatus
     * @throws NoSuchEntityException
     */
    public function get($statusId)
    {
        $chatStatus = $this->chatStatusFactory->create();
        $this->resource->load($chatStatus, $statusId);
        if (!$chatStatus->getId()) {
            throw new NoSuchEntityException(__('ChatStatus with id "%1" does not exist.', $statusId));
        }
        return $chatStatus;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Leeto\TicketLiveChat\Api\Data\ChatStatusSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->chatStatusCollectionFactory->create();
        
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
     * @param ChatStatusInterface $chatStatus
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ChatStatusInterface $chatStatus)
    {
        try {
            $chatStatusModel = $this->chatStatusFactory->create();
            $this->resource->load($chatStatusModel, $chatStatus->getStatusId());
            $this->resource->delete($chatStatusModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ChatStatus: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $statusId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($statusId)
    {
        return $this->delete($this->get($statusId));
    }
}
