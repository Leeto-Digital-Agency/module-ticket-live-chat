<?php

namespace Leeto\TicketLiveChat\Controller\Ticket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Filesystem;
use Leeto\TicketLiveChat\Helper\Ticket\TicketTypeHelper;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\ChatFactory;
use Leeto\TicketLiveChat\Model\ChatMessageFactory as MessageFactory;
use Leeto\TicketLiveChat\Model\AttachmentFactory;
use Magento\Customer\Model\Session;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;
use Magento\Sales\Model\Order;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;
use Leeto\TicketLiveChat\Model\ChatMessageAttachmentFactory;
use Leeto\TicketLiveChat\Helper\Ticket\FileValidationHelper;

class Create extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var TicketTypeHelper
     */
    protected $ticketTypeHelper;
    
    /**
     * @var TicketFactory
     */
    protected $ticketFactory;
    
    /**
     * @var ChatFactory
     */
    protected $chatFactory;
    
    /**
     * @var MessageFactory
     */
    protected $messageFactory;
    
    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * @var Order
     */
    protected $orderModel;

    /**
     * @var ChatStatusHelper
     */
    protected $chatStatusHelper;

    /**
     * @var ChatMessageAttachmentFactory
     */
    protected $chatMessageAttachmentFactory;

    /**
     * @var FileValidationHelper
     */
    protected $fileValidationHelper;

    /**
     * @param Context                       $context
     * @param SessionManagerInterface       $sessionManager
     * @param PageFactory                   $resultPageFactory
     * @param Filesystem                    $filesystem
     * @param TicketTypeHelper              $ticketTypeHelper
     * @param TicketFactory                 $ticketFactory
     * @param ChatFactory                   $chatFactory
     * @param MessageFactory                $messageFactory
     * @param AttachmentFactory             $attachmentFactory
     * @param Session                       $customerSession
     * @param TicketStatusHelper            $ticketStatusHelper
     * @param Order                         $orderModel
     * @param ChatStatusHelper              $chatStatusHelper
     * @param ChatMessageAttachmentFactory  $chatMessageAttachmentFactory
     * @param FileValidationHelper          $fileValidationHelper
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $sessionManager,
        PageFactory $resultPageFactory,
        Filesystem $filesystem,
        TicketTypeHelper $ticketTypeHelper,
        TicketFactory $ticketFactory,
        ChatFactory $chatFactory,
        MessageFactory $messageFactory,
        AttachmentFactory $attachmentFactory,
        Session $customerSession,
        TicketStatusHelper $ticketStatusHelper,
        Order $orderModel,
        ChatStatusHelper $chatStatusHelper,
        ChatMessageAttachmentFactory $chatMessageAttachmentFactory,
        FileValidationHelper         $fileValidationHelper
    ) {
        parent::__construct($context);
        $this->sessionManager = $sessionManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->filesystem = $filesystem;
        $this->ticketTypeHelper = $ticketTypeHelper;
        $this->ticketFactory = $ticketFactory;
        $this->chatFactory = $chatFactory;
        $this->messageFactory = $messageFactory;
        $this->attachmentFactory = $attachmentFactory;
        $this->customerSession = $customerSession;
        $this->ticketStatusHelper = $ticketStatusHelper;
        $this->orderModel = $orderModel;
        $this->chatStatusHelper = $chatStatusHelper;
        $this->chatMessageAttachmentFactory = $chatMessageAttachmentFactory;
        $this->fileValidationHelper = $fileValidationHelper;
    }

    public function execute()
    {
        try {
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPostValue();
                $orderTypeId = $this->ticketTypeHelper->getTicketOrderTypeId();
                $files = $this->getRequest()->getFiles('attachments');

                $errors = [];
                if (empty($data['ticket_type'])) {
                    $errors['ticket_type'] = __('Ticket Type is required.');
                }
                if (isset($data['increment_id'])
                    && ($data['ticket_type'] == $orderTypeId)
                    && empty($data['increment_id'])
                ) {
                    $errors['increment_id'] = __('Please provide an order.');
                }
                if (empty($data['subject'])) {
                    $errors['subject'] = __('Subject is required.');
                }
                if (empty($data['description'])) {
                    $errors['description'] = __('Description is required.');
                }
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = __('Invalid email format.');
                }
                $fileValidate = $this->validateUploadedFiles($files);
                if ($fileValidate) {
                    $errors['attachments'] = $fileValidate;
                }

                if (!empty($errors)) {
                    foreach ($errors as $field => $error) {
                        $this->messageManager->addErrorMessage($error);
                    }
                    $this->sessionManager->setFormData($data);
                    $this->sessionManager->setFormDataError($errors);
                    return $this->_redirect('*/*/');
                }

                $customerId = $this->customerSession->getCustomerId();
                // Create Ticket
                $ticketData = [
                    'subject' => $data['subject'],
                    'customer_id' => $customerId ? $customerId : null,
                    'ticket_type_id' => $data['ticket_type'],
                    'status_id' => $this->ticketStatusHelper->getStatusIdByLabel('opened'),
                    'email' => $data['email']
                ];
                // check if order exist if not throw error
                if ($data['ticket_type'] == $orderTypeId) {
                    $orderIncrementId = $data['increment_id'];
                    $order = $this->orderModel->loadByIncrementId($orderIncrementId);
                    if (!$order->getId() ||
                        ($order->getCustomerId() && !$customerId) ||
                        ($order->getCustomerId() != $customerId)
                    ) {
                        $this->messageManager->addErrorMessage(__("Please provide a valid order."));
                        $this->sessionManager->setFormData($data);
                        $this->sessionManager->setFormDataError($errors);
                        return $this->_redirect('*/*/');
                    }
                    $ticketData['order_id'] = $order->getId();
                }
                $ticket = $this->ticketFactory->create();
                $ticket->setData($ticketData)->save();

                // Create Chat
                $chatData = [
                    'ticket_id' => $ticket->getId(),
                    'status_id' => $this->chatStatusHelper->getChatStatusId(ChatStatusHelper::CLOSED_CHAT_STATUS),
                    'email' => $data['email']
                ];
                $chat = $this->chatFactory->create();
                $chat->setData($chatData)->save();

                $chatMessageId = $this->createAndSaveMessage(
                    $chat->getId(),
                    $data['description'],
                );
                // Create Chat Message
                if ($files[0]['name']) {
                    foreach ($files as $attachmentInfo) {
                        // Generate a unique name for the file
                        $uniqueFileName = uniqid() . '_' . $attachmentInfo['name'];
                        
                        // Save the file in the appropriate directory within your Magento installation
                        $mediaDirectory = $this->filesystem
                            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                        $filePath = 'custom_attachments/' . $uniqueFileName;
                        $mediaDirectory->writeFile($filePath, file_get_contents($attachmentInfo['tmp_name']));
    
                        $mediaUrl = $this->_url
                            ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
                        $fileUrl = $mediaUrl . $filePath;

                        // Create Attachment
                        $attachmentData = [
                            'chat_id' => $chat->getId(),
                            'original_name' => $attachmentInfo['name'],
                            'unique_name' => $uniqueFileName,
                            'path' => $fileUrl,
                        ];
                        $attachment = $this->attachmentFactory->create();
                        $attachment->setData($attachmentData)->save();

                        $chatMessageAttachmentModel = $this->chatMessageAttachmentFactory->create();
                        $chatMessageAttachmentData = [
                            'message_id' => $chatMessageId,
                            'attachment_id' => $attachment->getId()
                        ];
                        $chatMessageAttachmentModel->setData($chatMessageAttachmentData)->save();
                    }
                }

                $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $resultPage->getConfig()->getTitle()->prepend(__('Ticket Created Successfully'));
                return $resultPage;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while creating the ticket.'));
            $this->_redirect('*/*/create');
        }
        return $this->resultPageFactory->create();
    }

    /**
     * Validate Uploaded Files
     *
     * @param array $files
     * @return string
     */
    protected function validateUploadedFiles(array $files)
    {
        $allowedExtensions = explode(',', $this->fileValidationHelper->getAllowedFileExtensions());
        $maxFileSize = $this->fileValidationHelper->getMaximumFilesSize();
        $convertedMaxFileSize = $maxFileSize * 1024 * 1024;
        $maxFileCount = $this->fileValidationHelper->getMaximumFilesToUpload();

        $errorMessage = '';
        $totalFileSize = 0;
        $fileCount = 0;
        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions) && $extension) {
                $errorMessage = __('Invalid file extension.');
            }

            $totalFileSize += $file['size'];
            $fileCount++;

            if ($file['size'] > $convertedMaxFileSize) {
                $errorMessage = __('File size exceeds the limit.');
            }

            if ($fileCount > $maxFileCount) {
                $errorMessage = __('Maximum file count exceeded.');
            }
        }
        if ($totalFileSize > $convertedMaxFileSize) {
            $errorMessage = __('Total file size exceeds the limit.');
        }

        return $errorMessage;
    }

    /**
     * Create and save messages
     */
    public function createAndSaveMessage($chatId, $message = null, $attachmentId = null)
    {
        $messageData = [
            'chat_id' => $chatId,
            'is_admin' => false,
            'message' => $message,
            'attachment_id' => $attachmentId
        ];
        $message = $this->messageFactory->create();
        $message->setData($messageData)->save();
        return $message->getId();
    }
}
