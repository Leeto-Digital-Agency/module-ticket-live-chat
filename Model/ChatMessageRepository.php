<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\ChatMessageRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\ChatMessageInterface;
use Leeto\TicketLiveChat\Api\Data\ChatMessageInterfaceFactory;
use Leeto\TicketLiveChat\Api\Data\ChatMessageSearchResultsInterfaceFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage as ResourceChatMessage;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ChatMessageRepository implements ChatMessageRepositoryInterface
{
    /**
     * @var ResourceChatMessage
     */
    protected $resource;

    /**
     * @var ChatMessageInterfaceFactory
     */
    protected $chatMessageFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ChatMessage
     */
    protected $searchResultsFactory;

    /**
     * @var ChatMessageCollectionFactory
     */
    protected $chatMessageCollectionFactory;

    /**
     * @param ResourceChatMessage $resource
     * @param ChatMessageInterfaceFactory $chatMessageFactory
     * @param ChatMessageCollectionFactory $chatMessageCollectionFactory
     * @param ChatMessageSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceChatMessage $resource,
        ChatMessageInterfaceFactory $chatMessageFactory,
        ChatMessageCollectionFactory $chatMessageCollectionFactory,
        ChatMessageSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->chatMessageFactory = $chatMessageFactory;
        $this->chatMessageCollectionFactory = $chatMessageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param ChatMessageInterface $chatMessage
     * @return ChatMessageInterface
     * @throws CouldNotSaveException
     */
    public function save(ChatMessageInterface $chatMessage)
    {
        try {
            $this->resource->save($chatMessage);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the chatMessage: %1',
                $exception->getMessage()
            ));
        }
        return $chatMessage;
    }

    /**
     * @param $messageId
     * @return ChatMessage
     * @throws NoSuchEntityException
     */
    public function get($messageId)
    {
        $chatMessage = $this->chatMessageFactory->create();
        $this->resource->load($chatMessage, $messageId);
        if (!$chatMessage->getId()) {
            throw new NoSuchEntityException(__('ChatMessage with id "%1" does not exist.', $messageId));
        }
        return $chatMessage;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Leeto\TicketLiveChat\Api\Data\ChatMessageSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->chatMessageCollectionFactory->create();
        
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
     * @param ChatMessageInterface $chatMessage
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ChatMessageInterface $chatMessage)
    {
        try {
            $chatMessageModel = $this->chatMessageFactory->create();
            $this->resource->load($chatMessageModel, $chatMessage->getMessageId());
            $this->resource->delete($chatMessageModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ChatMessage: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $messageId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function deleteById($messageId)
    {
        return $this->delete($this->get($messageId));
    }
}
