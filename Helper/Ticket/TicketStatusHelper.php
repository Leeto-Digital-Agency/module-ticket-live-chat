<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\AbstractHelper;
use Leeto\TicketLiveChat\Api\TicketStatusRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Leeto\TicketLiveChat\Model\TicketStatusFactory;
use Leeto\TicketLiveChat\Model\TicketFactory;
use Leeto\TicketLiveChat\Model\ChatFactory;
use Leeto\TicketLiveChat\Model\ChatMessageFactory;

class TicketStatusHelper extends AbstractHelper
{
    /**
     * @var TicketStatusRepositoryInterface
     */
    protected $ticketStatusRepositoryInterface;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaInterface;

    /**
     * @var TicketStatusFactory
     */
    protected $ticketStatusFactory;

    /**
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * @var ChatFactory
     */
    protected $chatFactory;

    /**
     * @var ChatMessageFactory
     */
    protected $chatMessageFactory;

    /**
     * @param TicketStatusRepositoryInterface   $ticketStatusRepositoryInterface
     * @param FilterBuilder                     $filterBuilder
     * @param SearchCriteriaBuilder             $searchCriteriaInterface
     * @param TicketStatusFactory               $ticketStatusFactory
     * @param TicketFactory                     $ticketFactory
     * @param ChatFactory                       $chatFactory
     * @param ChatMessageFactory                $chatMessageFactory
     */
    public function __construct(
        TicketStatusRepositoryInterface $ticketStatusRepositoryInterface,
        FilterBuilder                   $filterBuilder,
        SearchCriteriaBuilder           $searchCriteriaInterface,
        TicketStatusFactory             $ticketStatusFactory,
        TicketFactory                   $ticketFactory,
        ChatFactory                     $chatFactory,
        ChatMessageFactory              $chatMessageFactory
    ) {
        $this->ticketStatusRepositoryInterface = $ticketStatusRepositoryInterface;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->ticketStatusFactory = $ticketStatusFactory;
        $this->ticketFactory = $ticketFactory;
        $this->chatFactory = $chatFactory;
        $this->chatMessageFactory = $chatMessageFactory;
    }

    /**
     * @return int
     */
    public function getStatusIdByLabel($label)
    {
        $labelFilter = $this->filterBuilder
            ->setField('label')
            ->setConditionType('like')
            ->setValue('%' . $label . '%')
            ->create();
        $searchCriteria = $this->searchCriteriaInterface
            ->addFilters([$labelFilter])
            ->create();

        return $this->ticketStatusRepositoryInterface->getList($searchCriteria)->getItems()[0]->getStatusId();
    }

    /**
     * @return int
     */
    public function getStatusLabelById($id)
    {
        $labelFilter = $this->filterBuilder
            ->setField('status_id')
            ->setConditionType('eq')
            ->setValue($id)
            ->create();
        $searchCriteria = $this->searchCriteriaInterface
            ->addFilters([$labelFilter])
            ->create();

        return $this->ticketStatusRepositoryInterface->getList($searchCriteria)->getItems()[0]->getLabel();
    }

    /**
     * @return array
     */
    public function getTicketStatuses()
    {
        $searchCriteria = $this->searchCriteriaInterface->create();
        $tickets = [];
        foreach ($this->ticketStatusRepositoryInterface->getList($searchCriteria)->getItems() as $status) {
            $tickets[] = [
                "value" => $status->getStatusId(),
                "label" => $status->getLabel()
            ];
        }
        
        return $tickets;
    }

    /**
     * @return array
     */
    public function changeTicketStatus($newStatusId, $ticketId)
    {
        $ticketStatusModel = $this->ticketStatusFactory->create();
        $ticketStatus = $ticketStatusModel->load($newStatusId);
        if (!$ticketStatus->getId()) {
            $errorMessage = "Status doesn't seem to exist!";
            return ['error' => true, 'message' => $errorMessage];
        }
        $ticketModel = $this->ticketFactory->create();
        $ticket = $ticketModel->load($ticketId);
        $ticket->setStatusId($newStatusId);
        $ticket->save();

        return ['success' => true];
    }

    public function addStatusChangeMessage($statusId, $ticketId)
    {
        $ticketModel = $this->ticketFactory->create();
        $ticket = $ticketModel->load($ticketId);

        $chatModel = $this->chatFactory->create();
        $chat = $chatModel->load($ticket->getId(), 'ticket_id');

        $statusLabel = $this->getStatusLabelById($statusId);

        $messageData = [
            'chat_id' => $chat->getId(),
            'message' => 'Ticket status set to ' . $statusLabel . ' by admin',
            'is_admin' => true,
            'is_alert' => true
        ];

        try {
            $newMessage = $this->chatMessageFactory->create();
            $newMessage->setData($messageData)->save();
        } catch (\Throwable $th) {
            throw $th;
        }

        return ['success' => true];
    }
}
