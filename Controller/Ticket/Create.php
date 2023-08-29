<?php

namespace Leeto\TicketLiveChat\Controller\Ticket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Leeto\TicketLiveChat\Helper\Ticket\TicketTypeHelper;

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
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var TicketTypeHelper
     */
    protected $ticketTypeHelper;

    /**
     * @param Context                 $context
     * @param SessionManagerInterface $sessionManager
     * @param PageFactory             $resultPageFactory
     * @param UploaderFactory         $uploaderFactory
     * @param Filesystem              $filesystem
     * @param TicketTypeHelper        $ticketTypeHelper
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $sessionManager,
        PageFactory $resultPageFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        TicketTypeHelper $ticketTypeHelper
    ) {
        parent::__construct($context);
        $this->sessionManager = $sessionManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->ticketTypeHelper = $ticketTypeHelper;
    }

    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPostValue();
            $orderTypeId = $this->ticketTypeHelper->getTicketOrderTypeId();

            $errors = [];
            if (empty($data['ticket_type'])) {
                $errors['ticket_type'] = __('Ticket Type is required.');
            }
            if (isset($data['increment_id']) 
                && ($data['ticket_type'] == $orderTypeId) 
                && empty($data['increment_id'])) 
            {
                $errors['increment_id'] = __('Please provide an order.');
            }
            if (empty($data['subject'])) {
                $errors['subject'] = __('Subject is required.');
            }
            if (empty($data['description'])) {
                $errors['description'] = 'Description is required.';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = __('Invalid email format.');
            }
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $maxFileSize = 15 * 1024 * 1024; // 15 MB in bytes
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

                foreach ($_FILES['attachments']['tmp_name'] as $i => $tmpName) {
                    if ($_FILES['attachments']['size'][$i] > $maxFileSize) {
                        $errors['attachments'] = 'File size should not exceed 15 MB.';
                    }

                    $fileExtension = pathinfo($_FILES['attachments']['name'][$i], PATHINFO_EXTENSION);
                    if (!in_array($fileExtension, $allowedExtensions)) {
                        $errors['attachments'] = 'Only JPG, JPEG, PNG, PDF, DOC, and DOCX files are allowed.';
                    }
                }
            }
            if (!empty($errors)) {
                foreach ($errors as $field => $error) {
                    $this->messageManager->addErrorMessage($error);
                }
                $this->sessionManager->setFormData($data);
                $this->sessionManager->setFormDataError($errors);
                return $this->_redirect('*/*/');
            }

            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->prepend(__('Ticket Created Successfully'));
            return $resultPage;
        }

        return $this->resultPageFactory->create();
    }
}
