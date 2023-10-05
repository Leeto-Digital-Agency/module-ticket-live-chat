<?php

namespace Leeto\TicketLiveChat\Controller\Ticket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketMessageHelper;
use Magento\Customer\Model\Session;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Magento\Customer\Model\Customer;
use Leeto\TicketLiveChat\Helper\Ticket\FileValidationHelper;

class AddMessage extends Action
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
     * @var Session
     */
    protected $customerSession;

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var FileValidationHelper
     */
    protected $fileValidationHelper;

    /**
     * Construct
     *
     * @param Context               $context
     * @param JsonFactory           $resultJsonFactory
     * @param TicketMessageHelper   $ticketMessageHelper
     * @param Session               $customerSession
     * @param TicketFactory         $ticketFactory
     * @param Customer              $customer
     * @param FileValidationHelper  $fileValidationHelper
     */
    public function __construct(
        Context              $context,
        JsonFactory          $resultJsonFactory,
        TicketMessageHelper  $ticketMessageHelper,
        Session              $customerSession,
        TicketFactory        $ticketFactory,
        Customer             $customer,
        FileValidationHelper $fileValidationHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ticketMessageHelper = $ticketMessageHelper;
        $this->customerSession = $customerSession;
        $this->ticketFactory = $ticketFactory;
        $this->customer = $customer;
        $this->fileValidationHelper = $fileValidationHelper;
        parent::__construct($context);
    }

    /**
     *
     * @return Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $message = $this->getRequest()->getParam('message');
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $ticketModel = $this->ticketFactory->create()->load($ticketId);
        $filesData = $this->getRequest()->getParam('files_data');
        $filesData = json_decode($filesData);

        foreach ($filesData as &$fileData) {
            $fileData[0] = base64_decode($fileData[0]);
        }
        $validation = $this->fileValidationHelper->validateFiles($filesData);
        if (isset($validation['error'])) {
            return $result->setData($validation);
        }
        $user = $this->customerSession->getCustomer();
        $from = '';
        $customer = $this->customer->load($ticketModel->getCustomerId());
        if ($customer->getId() && !$user->getId()) {
            return $result->setData(
                [
                    'error' => true,
                    'message' => 'Please login in order to send messages!',
                    'loginRequired' => true
                ]
            );
        }
        if ($user->getId()) {
            $from = $user->getId();
        } else {
            $from = $ticketModel->getEmail();
        }
        try {
            $data = $this->ticketMessageHelper->addMessage($message, $ticketId, false, $filesData);
        } catch (\Exception $e) {
            $result = $this->resultJsonFactory->create();
            return $result->setData(['error' => $e->getMessage()]);
        }

        return $result->setData($data);
    }
}
