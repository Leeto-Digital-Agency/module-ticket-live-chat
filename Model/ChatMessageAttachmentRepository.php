<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\ChatMessageAttachmentRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterface;
use Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentInterfaceFactory;
use Leeto\TicketLiveChat\Api\Data\ChatMessageAttachmentSearchResultsInterfaceFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessageAttachment as ResourceChatMessageAttachment;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessageAttachment\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;

class ChatMessageAttachmentRepository implements ChatMessageAttachmentRepositoryInterface
{
    /**
     * @var ResourceChatMessageAttachment
     */
    protected $resource;

    /**
     * @var ChatMessageAttachmentInterfaceFactory
     */
    protected $chatMessageAttachmentFactory;

    /**
     * @var CollectionFactory
     */
    protected $chatMessageAttachmentCollectionFactory;

    /**
     * @var ChatMessageAttachmentSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param ResourceChatMessageAttachment $resource
     * @param ChatMessageAttachmentInterfaceFactory $chatMessageAttachmentFactory
     * @param CollectionFactory $chatMessageAttachmentCollectionFactory
     * @param ChatMessageAttachmentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceChatMessageAttachment $resource,
        ChatMessageAttachmentInterfaceFactory $chatMessageAttachmentFactory,
        CollectionFactory $chatMessageAttachmentCollectionFactory,
        ChatMessageAttachmentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->chatMessageAttachmentFactory = $chatMessageAttachmentFactory;
        $this->chatMessageAttachmentCollectionFactory = $chatMessageAttachmentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param ChatMessageAttachmentInterface $chatMessageAttachment
     * @return ChatMessageAttachmentInterface
     */
    public function save(
        ChatMessageAttachmentInterface $chatMessageAttachment
    ) {
        try {
            $this->resource->save($chatMessageAttachment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the chatMessageAttachment: %1',
                $exception->getMessage()
            ));
        }
        return $chatMessageAttachment;
    }

    /**
     * @param int $chatMessageAttachmentId
     * @return ChatMessageAttachmentInterface
     */
    public function get($chatMessageAttachmentId)
    {
        $chatMessageAttachment = $this->chatMessageAttachmentFactory->create();
        $this->resource->load($chatMessageAttachment, $chatMessageAttachmentId);
        if (!$chatMessageAttachment->getId()) {
            throw new NoSuchEntityException(
                __('chat_message_attachment with id "%1" does not exist.', $chatMessageAttachmentId)
            );
        }
        return $chatMessageAttachment;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return ChatMessageAttachmentSearchResultsInterfaceFactory
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) {
        $collection = $this->chatMessageAttachmentCollectionFactory->create();
        
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
     * @param ChatMessageAttachmentInterface $chatMessageAttachment
     * @return boolean
     */
    public function delete(
        ChatMessageAttachmentInterface $chatMessageAttachment
    ) {
        try {
            $chatMessageAttachmentModel = $this->chatMessageAttachmentFactory->create();
            $this->resource->load($chatMessageAttachmentModel, $chatMessageAttachment->getChatMessageAttachmentId());
            $this->resource->delete($chatMessageAttachmentModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the chat_message_attachment: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param int $chatMessageAttachmentId
     * @return boolean
     */
    public function deleteById($chatMessageAttachmentId)
    {
        return $this->delete($this->get($chatMessageAttachmentId));
    }
}
