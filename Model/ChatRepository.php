<?php

/**
 * Copyright © Leeto All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\ChatRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\ChatInterface;
use Leeto\TicketLiveChat\Api\Data\ChatInterfaceFactory;
use Leeto\TicketLiveChat\Api\Data\ChatSearchResultsInterfaceFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\Chat as ResourceChat;
use Leeto\TicketLiveChat\Model\ResourceModel\Chat\CollectionFactory as ChatCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ChatRepository implements ChatRepositoryInterface
{
    /**
     * @var ChatCollectionFactory
     */
    protected $chatCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceChat
     */
    protected $resource;

    /**
     * @var ChatInterfaceFactory
     */
    protected $chatFactory;

    /**
     * @var Chat
     */
    protected $searchResultsFactory;

    /**
     * @param ResourceChat $resource
     * @param ChatInterfaceFactory $chatFactory
     * @param ChatCollectionFactory $chatCollectionFactory
     * @param ChatSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceChat $resource,
        ChatInterfaceFactory $chatFactory,
        ChatCollectionFactory $chatCollectionFactory,
        ChatSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->chatFactory = $chatFactory;
        $this->chatCollectionFactory = $chatCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param ChatInterface $chat
     * @return ChatInterface
     * @throws CouldNotSaveException
     */
    public function save(ChatInterface $chat)
    {
        try {
            $this->resource->save($chat);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the chat: %1',
                $exception->getMessage()
            ));
        }
        return $chat;
    }

    /**
     * @param $chatId
     * @return Chat
     * @throws NoSuchEntityException
     */
    public function get($chatId)
    {
        $chat = $this->chatFactory->create();
        $this->resource->load($chat, $chatId);
        if (!$chat->getId()) {
            throw new NoSuchEntityException(__('Chat with id "%1" does not exist.', $chatId));
        }
        return $chat;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Leeto\TicketLiveChat\Api\Data\ChatSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->chatCollectionFactory->create();
        
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
     * @param ChatInterface $chat
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ChatInterface $chat)
    {
        try {
            $chatModel = $this->chatFactory->create();
            $this->resource->load($chatModel, $chat->getChatId());
            $this->resource->delete($chatModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Chat: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $chatId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($chatId)
    {
        return $this->delete($this->get($chatId));
    }
}
