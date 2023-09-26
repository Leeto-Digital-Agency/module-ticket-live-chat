<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketMessageHelper;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Leeto\TicketLiveChat\Helper\Ticket\NotifyUserHelper;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Helper\Ticket\FileValidationHelper;

class AddAdminMessage extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TicketMessageHelper
     */
    protected $ticketMessageHelper;

    /**
     * @var AdminSession
     */
    protected $adminSession;

    /**
     * @var NotifyUserHelper
     */
    protected $notifyUserHelper;

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var FileValidationHelper
     */
    protected $fileValidationHelper;

    /**
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param TicketMessageHelper   $ticketMessageHelper
     * @param AdminSession          $adminSession
     * @param NotifyUserHelper      $notifyUserHelper
     * @param TicketFactory         $ticketFactory
     * @param FileValidationHelper  $fileValidationHelper
     */
    public function __construct(
        Context               $context,
        JsonFactory           $resultJsonFactory,
        TicketMessageHelper   $ticketMessageHelper,
        AdminSession          $adminSession,
        NotifyUserHelper      $notifyUserHelper,
        TicketFactory         $ticketFactory,
        FileValidationHelper  $fileValidationHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ticketMessageHelper = $ticketMessageHelper;
        $this->adminSession = $adminSession;
        $this->notifyUserHelper = $notifyUserHelper;
        $this->ticketFactory = $ticketFactory;
        $this->fileValidationHelper = $fileValidationHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $message = $this->getRequest()->getParam('message');
            $ticketId = $this->getRequest()->getParam('ticket_id');
            $filesData = $this->getRequest()->getParam('files_data');
            $filesData = json_decode($filesData);
            foreach ($filesData as &$fileData) {
                $fileData[0] = base64_decode($fileData[0]);
            }
            $validation = $this->fileValidationHelper->validateFiles($filesData);
            if (isset($validation['error'])) {
                return $result->setData($validation);
            }
            $isLatestMessageFromUser = $this->ticketMessageHelper->isLatestMessageFromUser($ticketId);
            $data = $this->ticketMessageHelper->addMessage($message, $ticketId, true, $filesData);
            if (isset($data['error'])) {
                return $result->setData($data);
            }
            if ($isLatestMessageFromUser) {
                $ticket = $this->ticketFactory->create()->load($ticketId);
                $adminUser = $this->adminSession->getUser();
                $this->notifyUserHelper->sendEmail(
                    $adminUser->getEmail(),
                    $adminUser->getUsername(),
                    $ticket->getEmail(),
                    $ticket->getSubject(),
                    $ticketId
                );
            }
        } catch (\Exception $e) {
            return $result->setData(['error' => $e->getMessage()]);
        }

        return $result->setData($data);
    }
}
