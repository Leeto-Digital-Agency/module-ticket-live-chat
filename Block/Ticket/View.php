<?php

namespace Leeto\TicketLiveChat\Block\Ticket;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Magento\Catalog\Helper\Image;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollection;
use Leeto\TicketLiveChat\Model\Chat;
use Magento\Framework\Encryption\EncryptorInterface;
use Leeto\TicketLiveChat\Helper\Ticket\TicketMessageHelper;
use Leeto\TicketLiveChat\Helper\Ticket\FileValidationHelper;
use Leeto\TicketLiveChat\Helper\Ticket\TicketDataHelper;

class View extends Template
{
    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var ChatMessageCollection
     */
    protected $chatMessageCollection;

    /**
     * @var Chat
     */
    protected $chatModel;

    /**
     * @var EncryptorInterface
     */
    protected $encryptorInterface;

    /**
     * @var TicketMessageHelper
     */
    protected $ticketMessageHelper;

    /**
     * @var FileValidationHelper
     */
    protected $fileValidationHelper;

    /**
     * @var TicketDataHelper
     */
    protected $ticketDataHelper;

    /**
     * Construct
     *
     * @param Context                   $context
     * @param TicketFactory             $ticketFactory
     * @param Image                     $imageHelper
     * @param ChatMessageCollection     $chatMessageCollection
     * @param Chat                      $chatModel
     * @param EncryptorInterface        $encryptorInterface
     * @param TicketMessageHelper       $ticketMessageHelper
     * @param FileValidationHelper      $fileValidationHelper
     * @param TicketDataHelper          $ticketDataHelper
     * @param array                     $data
     */
    public function __construct(
        Context                     $context,
        TicketFactory               $ticketFactory,
        Image                       $imageHelper,
        ChatMessageCollection       $chatMessageCollection,
        Chat                        $chatModel,
        EncryptorInterface          $encryptorInterface,
        TicketMessageHelper         $ticketMessageHelper,
        FileValidationHelper        $fileValidationHelper,
        TicketDataHelper            $ticketDataHelper,
        array                       $data = []
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->imageHelper = $imageHelper;
        $this->chatMessageCollection = $chatMessageCollection;
        $this->chatModel = $chatModel;
        $this->encryptorInterface = $encryptorInterface;
        $this->ticketMessageHelper = $ticketMessageHelper;
        $this->fileValidationHelper = $fileValidationHelper;
        $this->ticketDataHelper = $ticketDataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getTicket()
    {
        $ticketEncryptedId = $this->getRequest()->getParam('ticket_id');
        $ticketDecryptedId = $this->encryptorInterface->decrypt($ticketEncryptedId);
        $ticketModel = $this->ticketFactory->create();
        return $ticketModel->load($ticketDecryptedId);
    }

    /**
     * @return string
     */
    public function getDefaultImage()
    {
        return $this->imageHelper->getDefaultPlaceholderUrl('image');
    }

    /**
     * @return Ticket
     */
    public function getLatestMessageData($ticketId)
    {
        $chatId = $this->chatModel->load($ticketId, 'ticket_id')->getId();
        $latestMessage = $this->chatMessageCollection->create()->addFieldToFilter(
            'chat_id',
            $chatId
        )->setOrder('message_id', 'DESC')
        ->getFirstItem();
       
        $latestMessageData = [
            'isAdmin' => $latestMessage->getIsAdmin(),
            'latest_message' => $latestMessage->getMessage() ? $latestMessage->getMessage() : 'Attachment'
        ];
        return $latestMessageData;
    }

    /**
     * @return array
     */
    public function getTicketMessages()
    {
        $ticketEncryptedId = $this->getRequest()->getParam('ticket_id');
        $ticketDecryptedId = $this->encryptorInterface->decrypt($ticketEncryptedId);

        return $this->ticketMessageHelper->getTicketMessages($ticketDecryptedId);
    }

    /**
     * @return string
     */
    public function getTicketControllerUrl()
    {
        return $this->_urlBuilder->getUrl('support/ticket/ticket');
    }

    /**
     * @return string
     */
    public function getMessageControllerUrl()
    {
        return $this->_urlBuilder->getUrl('support/ticket/message');
    }

    /**
     * @return string
     */
    public function getAddMessageControllerUrl()
    {
        return $this->_urlBuilder->getUrl('support/ticket/addmessage');
    }

    /**
     * @return string
     */
    public function getOpenTicketStatusUrl()
    {
        return $this->_urlBuilder->getUrl('support/ticket/openticket');
    }

    /**
     * @return string
     */
    public function getAllowedFileExtensions()
    {
        return $this->fileValidationHelper->getAllowedFileExtensions();
    }

    /**
     * @return string
     */
    public function getMaximumFilesSize()
    {
        return $this->fileValidationHelper->getMaximumFilesSize();
    }

    /**
     * @return string
     */
    public function getMaximumFilesToUpload()
    {
        return $this->fileValidationHelper->getMaximumFilesToUpload();
    }

    /**
     * @return string
     */
    public function getUserImage()
    {
        return $this->ticketDataHelper->getUserAvatarImagePath();
    }

    /**
     * @return string
     */
    public function getAdminImage()
    {
        return $this->ticketDataHelper->getAdminAvatarImagePath();
    }
}
