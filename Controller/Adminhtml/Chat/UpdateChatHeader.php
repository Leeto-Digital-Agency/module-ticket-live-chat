<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\CustomerFactory;
use Magento\Sales\Model\Order;
use Leeto\TicketLiveChat\Api\ChatRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;
use Leeto\TicketLiveChat\Api\Data\ChatStatusInterfaceFactory;

class UpdateChatHeader extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CustomerFactory
     */
    protected $customerModelFactory;

    /**
     * @var Order
     */
    protected $orderModel;

    /**
     * @var ChatRepositoryInterface
     */
    protected $chatRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ChatStatusHelper
     */
    protected $chatStatusHelper;

    /**
     * @var ChatStatusInterfaceFactory
     */
    protected $chatStatusFactory;

    /**
     * @param Context                       $context
     * @param JsonFactory                   $resultJsonFactory
     * @param TicketFactory                 $ticketFactory
     * @param TicketStatusFactory           $ticketStatusFactory
     * @param DateTime                      $dateTime
     * @param TicketTypeHelper              $ticketTypeHelper
     * @param CustomerFactory               $customerModelFactory
     * @param TicketTypeFactory             $ticketTypeFactory
     * @param Order                         $orderModel
     * @param TicketStatusHelper            $ticketStatusHelper
     * @param ChatRepositoryInterface       $chatRepository
     * @param SearchCriteriaBuilder         $searchCriteriaBuilder
     * @param ChatStatusHelper              $chatStatusHelper
     * @param ChatStatusInterfaceFactory    $chatStatusFactory
     */
    public function __construct(
        Context                     $context,
        JsonFactory                 $resultJsonFactory,
        DateTime                    $dateTime,
        CustomerFactory             $customerModelFactory,
        Order                       $orderModel,
        ChatRepositoryInterface     $chatRepository,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        ChatStatusHelper            $chatStatusHelper,
        ChatStatusInterfaceFactory  $chatStatusFactory
    ) {
        $this->dateTime = $dateTime;
        $this->customerModelFactory = $customerModelFactory;
        $this->orderModel = $orderModel;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->chatRepository = $chatRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->chatStatusHelper = $chatStatusHelper;
        $this->chatStatusFactory = $chatStatusFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $chatId = $this->getRequest()->getParam('chatId');

            $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('chat_id', $chatId, 'eq')
            ->setPageSize(1)
            ->create();
            $chatRepository = $this->chatRepository->getList($searchCriteria)->getItems()[0];
            $chatStatus = $this->chatStatusFactory->create()
                ->load($chatRepository->getStatusId(), 'status_id');

            $status = $this->chatStatusHelper->getChatStatusById($chatStatus->getStatusId());
            $statusLabel = $chatStatus->getLabel();
            $formattedDate = $this->dateTime->gmtDate('d.m.Y', $chatRepository->getCreatedAt());

            $data = [
                'createdAt' => $formattedDate,
                'status' => $status,
                'statusLabel' => $statusLabel
            ];

            $result = $this->resultJsonFactory->create();
            return $result->setData($data);
        } catch (\Exception $e) {
            $result = $this->resultJsonFactory->create();
            return $result->setData(['error' => $e->getMessage()]);
        }
    }
}
