<?php

namespace Leeto\TicketLiveChat\Helper\Chat;

use Magento\Framework\App\Helper\AbstractHelper;
use Leeto\TicketLiveChat\Api\ChatStatusRepositoryInterface;
use Leeto\TicketLiveChat\Api\ChatRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ChatStatusHelper extends AbstractHelper
{
    /**
     * @var string
     */
    public const XML_PATH_ACIVE_CHAT_STATUS = 'live_chat/settings/ongoing_chat_status';

    /**
     * @var string
     */
    public const XML_PATH_CLOSED_CHAT_STATUS = 'live_chat/settings/closed_chat_status';

    /**
     * @var string
     */
    public const ACTIVE_CHAT_STATUS = 'active';

    /**
     * @var string
     */
    public const CLOSED_CHAT_STATUS = 'closed';

    /**
     * @var ChatStatusRepositoryInterface
     */
    protected $chatStatusRepositoryInterface;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaInterface;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ChatRepositoryInterface $chatRepositoryInterface
     */
    protected $chatRepositoryInterface;

    /**
     * @param ChatStatusRepositoryInterface $chatStatusRepositoryInterface
     * @param FilterBuilder                 $filterBuilder
     * @param SearchCriteriaBuilder         $searchCriteriaInterface
     * @param ScopeConfigInterface          $scopeConfig
     * @param ChatRepositoryInterface       $chatRepositoryInterface
     */
    public function __construct(
        ChatStatusRepositoryInterface $chatStatusRepositoryInterface,
        FilterBuilder                 $filterBuilder,
        SearchCriteriaBuilder         $searchCriteriaInterface,
        ScopeConfigInterface          $scopeConfig,
        ChatRepositoryInterface       $chatRepositoryInterface
    ) {
        $this->chatStatusRepositoryInterface = $chatStatusRepositoryInterface;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->scopeConfig = $scopeConfig;
        $this->chatRepositoryInterface = $chatRepositoryInterface;
    }

    public function getOnGoingStatusId()
    {
        $labelFilter = $this->filterBuilder
            ->setField('label')
            ->setConditionType('like')
            ->setValue('%ongoing%')
            ->create();
        $searchCriteria = $this->searchCriteriaInterface
            ->addFilters([$labelFilter])
            ->create();

        return $this->chatStatusRepositoryInterface->getList($searchCriteria)->getItems()[0]->getStatusId();
    }

    /**
     * @param string $status
     * @return int|null
     */
    public function getChatStatusId($status)
    {
        $statusId = null;
        $statusArray = [
            self::ACTIVE_CHAT_STATUS => self::XML_PATH_ACIVE_CHAT_STATUS,
            self::CLOSED_CHAT_STATUS => self::XML_PATH_CLOSED_CHAT_STATUS
        ];

        if (array_key_exists($status, $statusArray)) {
            $statusId = $this->scopeConfig
                ->getValue($statusArray[$status], \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        
        return $statusId;
    }

    /**
     * @param int $statusId
     * @return string|null
     */
    public function getChatStatusLabelById($statusId)
    {
        $status = null;
        $statusArray = [
            $this->getChatStatusId(self::ACTIVE_CHAT_STATUS) => self::ACTIVE_CHAT_STATUS,
            $this->getChatStatusId(self::CLOSED_CHAT_STATUS) => self::CLOSED_CHAT_STATUS
        ];

        if (array_key_exists($statusId, $statusArray)) {
            $status = $statusArray[$statusId];
        }

        return $status;
    }
    
    /**
     * @return array
     */
    public function getChatStatuses()
    {
        $searchCriteria = $this->searchCriteriaInterface->create();
        $chatStatuses = [];
        foreach ($this->chatStatusRepositoryInterface->getList($searchCriteria)->getItems() as $status) {
            $chatStatuses[] = [
                "value" => $status->getStatusId(),
                "label" => $status->getLabel()
            ];
        }
        
        return $chatStatuses;
    }

    public function changeChatStatus($newStatusId, $chatId)
    {
        try {
            $chatStatus = $this->chatStatusRepositoryInterface->get($newStatusId);
            if (!$chatStatus->getStatusId()) {
                $errorMessage = "Status doesn't seem to exist!";
                return [
                    'error' => true,
                    'message' => $errorMessage
                ];
            }
            $chat = $this->chatRepositoryInterface->get($chatId);
            $chat->setStatusId($newStatusId);
            $this->chatRepositoryInterface->save($chat);
            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }


    }
}
