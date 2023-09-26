<?php

namespace Leeto\TicketLiveChat\Controller\Ticket;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Leeto\TicketLiveChat\Model\TicketFactory;

class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptorInterface;

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * Construct
     *
     * @param Context            $context
     * @param PageFactory        $resultPageFactory
     * @param EncryptorInterface $encryptorInterface
     * @param TicketFactory      $ticketFactory
     */
    public function __construct(
        Context             $context,
        PageFactory         $resultPageFactory,
        EncryptorInterface  $encryptorInterface,
        TicketFactory       $ticketFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->encryptorInterface = $encryptorInterface;
        $this->ticketFactory = $ticketFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $encryptedTicketId = $this->getRequest()->getParam('ticket_id');
        $decryptedTicketId = $this->encryptorInterface->decrypt($encryptedTicketId);

        $ticketModel = $this->ticketFactory->create();
        $ticket = $ticketModel->load($decryptedTicketId);

        if (!$ticket->getId()) {
            $this->messageManager->addErrorMessage(__("Ticket does not exist!"));
            $this->_redirect('/');
        }
        return $this->resultPageFactory->create();
    }
}
