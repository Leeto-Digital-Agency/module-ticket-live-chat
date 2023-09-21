<?php

namespace Leeto\TicketLiveChat\Helper\Chat;

use Magento\Framework\App\Helper\AbstractHelper;
use Leeto\TicketLiveChat\Api\ChatStatusRepositoryInterface;
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
     * @param ChatStatusRepositoryInterface $chatStatusRepositoryInterface
     * @param FilterBuilder                 $filterBuilder
     * @param SearchCriteriaBuilder         $searchCriteriaInterface
     * @param ScopeConfigInterface          $scopeConfig
     */
    public function __construct(
        ChatStatusRepositoryInterface $chatStatusRepositoryInterface,
        FilterBuilder                 $filterBuilder,
        SearchCriteriaBuilder         $searchCriteriaInterface,
        ScopeConfigInterface          $scopeConfig
    ) {
        $this->chatStatusRepositoryInterface = $chatStatusRepositoryInterface;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->scopeConfig = $scopeConfig;
    }

    public function getOnGoingStatusId()
    {
        $labelFilter = $this->filterBuilder
            ->setField('label') // Change this to the actual field name in your table
            ->setConditionType('like')
            ->setValue('%ongoing%') // Change this value according to your needs
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
    public function getChatStatusById($statusId)
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
}
