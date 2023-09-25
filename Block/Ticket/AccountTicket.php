<?php

namespace Leeto\TicketLiveChat\Block\Ticket;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Leeto\TicketLiveChat\Model\ResourceModel\Ticket\CollectionFactory as TicketCollectionFactory;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;

class AccountTicket extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var TicketCollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptorInterface;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * Construct
     *
     * @param Context                   $context
     * @param Session                   $customerSession
     * @param TicketCollectionFactory   $ticketCollectionFactory
     * @param TicketFactory             $ticketFactory
     * @param EncryptorInterface        $encryptorInterface
     * @param TicketStatusHelper        $ticketStatusHelper
     * @param array                     $data
     */
    public function __construct(
        Context                     $context,
        Session                     $customerSession,
        TicketCollectionFactory     $ticketCollectionFactory,
        TicketFactory               $ticketFactory,
        EncryptorInterface          $encryptorInterface,
        TicketStatusHelper          $ticketStatusHelper,
        array                       $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->ticketFactory = $ticketFactory;
        $this->encryptorInterface = $encryptorInterface;
        $this->ticketStatusHelper = $ticketStatusHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getUserTickets($getCollectionOnly = false)
    {
        $loggedInUser = $this->customerSession->getCustomer();
        $data = [];
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;
        $collection = $this->ticketCollectionFactory->create();
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->addFieldToFilter(
            'email',
            $loggedInUser->getEmail()
        )->setOrder('created_at', 'DESC');
        if ($getCollectionOnly) {
            return $collection;
        } else {
            $userTickets = $collection->getItems();
        }
        foreach ($userTickets as $ticket) {
            $ticketModel = $this->ticketFactory->create()->load($ticket->getId());
            $data[] = $ticketModel;
        }
        return $data;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getUserTickets(true)) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'tickets.list.pager'
            )->setAvailableLimit(
                [
                    5 => 5,
                    10 => 10,
                    15 => 15,
                    20 => 20
                ]
            )->setShowPerPage(true)
            ->setCollection(
                $this->getUserTickets(true)
            );
            $this->setChild('pager', $pager);
            $this->getUserTickets(true)->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getViewTicketUrl($ticketId)
    {
        $encryptedTicketId = $this->encryptorInterface->encrypt($ticketId);
        return $this->_urlBuilder->getUrl('support/ticket/view', ['ticket_id' => $encryptedTicketId]);
    }

    /**
     * @return string
     */
    public function getEmptyOrdersMessage()
    {
        return __('You have not created any tickets yet.');
    }

    /**
     * @param int $statusId
     * @return string
     */
    public function getStatusLabelById($statusId)
    {
        return $this->ticketStatusHelper->getStatusLabelById($statusId);
    }
}
