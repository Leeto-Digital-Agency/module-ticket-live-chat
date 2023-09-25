<?php

namespace Leeto\TicketLiveChat\Model;

use Leeto\TicketLiveChat\Api\AttachmentRepositoryInterface;
use Leeto\TicketLiveChat\Api\Data\AttachmentInterface;
use Leeto\TicketLiveChat\Api\Data\AttachmentInterfaceFactory;
use Leeto\TicketLiveChat\Api\Data\AttachmentSearchResultsInterfaceFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\Attachment as ResourceAttachment;
use Leeto\TicketLiveChat\Model\ResourceModel\Attachment\CollectionFactory as AttachmentCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AttachmentRepository implements AttachmentRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var AttachmentCollectionFactory
     */
    protected $attachmentCollectionFactory;

    /**
     * @var Attachment
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceAttachment
     */
    protected $resource;

    /**
     * @var AttachmentInterfaceFactory
     */
    protected $attachmentFactory;

    /**
     * @param ResourceAttachment $resource
     * @param AttachmentInterfaceFactory $attachmentFactory
     * @param AttachmentCollectionFactory $attachmentCollectionFactory
     * @param AttachmentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceAttachment $resource,
        AttachmentInterfaceFactory $attachmentFactory,
        AttachmentCollectionFactory $attachmentCollectionFactory,
        AttachmentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentCollectionFactory = $attachmentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param $attachment
     * @return AttachmentInterface
     * @throws CouldNotSaveException
     */
    public function save(AttachmentInterface $attachment)
    {
        try {
            $this->resource->save($attachment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the attachment: %1',
                $exception->getMessage()
            ));
        }
        return $attachment;
    }

    /**
     * @param $attachmentId
     * @return AttachmentInterface
     * @throws NoSuchEntityException
     */
    public function get($attachmentId)
    {
        $attachment = $this->attachmentFactory->create();
        $this->resource->load($attachment, $attachmentId);
        if (!$attachment->getId()) {
            throw new NoSuchEntityException(__('Attachment with id "%1" does not exist.', $attachmentId));
        }
        return $attachment;
    }

    /**
     * @param $criteria
     * @return AttachmentSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->attachmentCollectionFactory->create();
        
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
     * @param $attachment
     * @throws CouldNotDeleteException
     * @return true
     */
    public function delete(AttachmentInterface $attachment)
    {
        try {
            $attachmentModel = $this->attachmentFactory->create();
            $this->resource->load($attachmentModel, $attachment->getAttachmentId());
            $this->resource->delete($attachmentModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Attachment: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $attachmentId
     * @throws CouldNotDeleteException
     * @return true
     */
    public function deleteById($attachmentId)
    {
        return $this->delete($this->get($attachmentId));
    }
}
