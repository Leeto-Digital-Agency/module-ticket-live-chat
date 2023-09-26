<?php

namespace Leeto\TicketLiveChat\Helper\Chat;

use Magento\Framework\App\Helper\AbstractHelper;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Leeto\TicketLiveChat\Api\ChatRepositoryInterface;
use Leeto\TicketLiveChat\Api\TicketRepositoryInterface;
use Leeto\TicketLiveChat\Helper\Chat\ChatStatusHelper;
use Leeto\TicketLiveChat\Api\Data\TicketInterfaceFactory;
use Leeto\TicketLiveChat\Helper\Ticket\TicketTypeHelper;
use Leeto\TicketLiveChat\Helper\Ticket\TicketStatusHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;


class ChatHelper extends AbstractHelper
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ChatRepositoryInterface
     */
    protected $chatRepository;

    /**
     * @var ChatStatusHelper
     */
    protected $chatStatusHelper;

    /**
     * @var TicketInterfaceFactory
     */
    protected $ticketInterfaceFactory;

    /**
     * @var TicketTypeHelper
     */
    protected $ticketTypeHelper;

    /**
     * @var TicketRepositoryInterface
     */
    protected $ticketRepositoryInterface;

    /**
     * @var TicketStatusHelper
     */
    protected $ticketStatusHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @param LoggerInterface              $logger
     * @param ChatRepositoryInterface      $chatRepository
     * @param ChatStatusHelper             $chatStatusHelper
     * @param TicketInterfaceFactory       $ticketInterfaceFactory
     * @param TicketTypeHelper             $ticketTypeHelper
     * @param TicketRepositoryInterface    $ticketRepositoryInterface
     * @param TicketStatusHelper           $ticketStatusHelper
     * @param CustomerRepositoryInterface  $customerRepositoryInterface
     */
    public function __construct(
        LoggerInterface              $logger,
        ChatRepositoryInterface      $chatRepository,
        ChatStatusHelper             $chatStatusHelper,
        TicketInterfaceFactory       $ticketInterfaceFactory,
        TicketTypeHelper             $ticketTypeHelper,
        TicketRepositoryInterface    $ticketRepositoryInterface,
        TicketStatusHelper           $ticketStatusHelper,
        CustomerRepositoryInterface  $customerRepositoryInterface
    ) {
        $this->logger = $logger;
        $this->chatRepository = $chatRepository;
        $this->chatStatusHelper = $chatStatusHelper;
        $this->ticketInterfaceFactory = $ticketInterfaceFactory;
        $this->ticketTypeHelper = $ticketTypeHelper;
        $this->ticketRepositoryInterface = $ticketRepositoryInterface;
        $this->ticketStatusHelper = $ticketStatusHelper;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function createTicket($chatId, $subject, $statusId = false,  $chatStatusLabel = false)
    {
        try {
            if (!$chatId) {
                return [
                    'success' => false,
                    'message' => __('Chat ID is required')
                ];
            }

            $chat = $this->chatRepository->get($chatId);
            if (!$chat || ($chat && !$chat->getChatId())) {
                return [
                    'success' => false,
                    'message' => __('Chat not found')
                ];
            }
            // find status id
            if (!$statusId && !$chatStatusLabel) {
                $ticketStatusId = $this->ticketStatusHelper->getStatusIdByLabel('closed');
            } else if (!$statusId && $chatStatusLabel) {
                $ticketStatusId = $this->ticketStatusHelper->getStatusIdByLabel($chatStatusLabel);
            } else {
                $ticketStatusId = $statusId;
            }

            $ticket = $this->ticketInterfaceFactory->create();
            $ticket->setCustomerId($chat->getCustomerId());
            $ticket->setStatusId($ticketStatusId);
            $ticket->setTypeId($this->ticketTypeHelper->getTicketTypeIdByLabel('general'));
            $ticket->setOrderId(null);
            $ticket->setSubject($subject);
            $ticket->setEmail($chat->getEmail() ?? $this->customerRepositoryInterface
                ->getById($chat->getCustomerId())->getEmail());
            $ticket = $this->ticketRepositoryInterface->save($ticket);
            if ($ticket && $ticket->getEntityId()) {
                $chat->setTicketId($ticket->getEntityId());
                $chat->setStatusId($this->chatStatusHelper->getChatStatusId(ChatStatusHelper::CLOSED_CHAT_STATUS));
                $this->chatRepository->save($chat);
                return [
                    'success' => true,
                    'message' =>  __('Ticket created successfully'),
                    'ticketId' => $ticket->getEntityId()
                ];
            }

            return [
                'success' => false,
                'message' => __('Ticket was not created, something went wrong')
            ];
            
        } catch (NoSuchEntityException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
