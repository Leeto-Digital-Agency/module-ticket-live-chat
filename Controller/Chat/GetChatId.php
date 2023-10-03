<?php

namespace Leeto\TicketLiveChat\Controller\Chat;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Leeto\TicketLiveChat\Api\ChatRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;

class GetChatId extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param Context                      $context
     * @param JsonFactory                  $resultJsonFactory
     * @param LoggerInterface              $logger
     * @param ChatRepositoryInterface      $chatRepository
     * @param SearchCriteriaBuilder        $searchCriteriaBuilder
     * @param ChatStatusHelper             $chatStatusHelper
     */

    public function __construct(
        Context                      $context,
        JsonFactory                  $resultJsonFactory,
        LoggerInterface              $logger,
        ChatRepositoryInterface      $chatRepository,
        SearchCriteriaBuilder        $searchCriteriaBuilder,
        ChatStatusHelper             $chatStatusHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->chatRepository = $chatRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->chatStatusHelper = $chatStatusHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $email = $this->getRequest()->getParam('email');
            $uuid = $this->getRequest()->getParam('uuid');
            $customerId = $this->getRequest()->getParam('userId');

            $email = isset($email) && !empty($email) ? $email : null;
            $uuid = isset($uuid) && !empty($uuid) ? $uuid : null;
            $customerId = isset($customerId) && !empty($customerId) ? $customerId : null;

            if (!$email && !$uuid && !$customerId) {
                throw new NoSuchEntityException(
                    __('No user id or email provided')
                );
            }
            $chatId = $this->getChatId($customerId, $email, $uuid);

            return $result->setData([
                'success' => true,
                'chatId' => $chatId,
            ]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return $result->setData([
                'succes' => false,
                'chatId' => null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param $userId
     * @param $email
     * @param $uuid
     * @return int|null
     */
    public function getChatId($userId, $email, $uuid)
    {
        $chatRepositoryItem = null;
        $activeChatStatusId = $this->chatStatusHelper
            ->getChatStatusId(ChatStatusHelper::ACTIVE_CHAT_STATUS);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status_id', $activeChatStatusId, 'eq')
            ->setPageSize(1);

        if ($userId) {
            $searchCriteria = $searchCriteria
                ->addFilter('customer_id', $userId, 'eq');
        } elseif ($uuid && $email) {
            $searchCriteria = $searchCriteria
                ->addFilter('email', $email, 'eq');
        } elseif ($uuid && !$email) {
            $searchCriteria = $searchCriteria
                ->addFilter('uuid', $uuid, 'eq');
        }
        $chatRepositoryItem = $this->chatRepository->getList($searchCriteria->create())->getItems();
        if (count($chatRepositoryItem)) {
            $chatRepositoryItem = $chatRepositoryItem[0];
            if ($userId || $chatRepositoryItem->getUuid() === $uuid) {
                return $chatRepositoryItem->getChatId();
            }
        }
        return null;
    }
}
