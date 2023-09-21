<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Leeto\TicketLiveChat\Model\ResourceModel\ChatMessage\CollectionFactory as ChatMessageCollectionFactory;
use Psr\Log\LoggerInterface;

class UpdateUnreadMessages extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ChatMessageCollectionFactory
     */
    protected $chatMessageCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context                         $context
     * @param JsonFactory                     $resultJsonFactory
     * @param ChatMessageCollectionFactory    $chatMessageCollectionFactory
     * @param LoggerInterface                 $logger
     */
    public function __construct(
        Context                      $context,
        JsonFactory                  $resultJsonFactory,
        ChatMessageCollectionFactory $chatMessageCollectionFactory,
        LoggerInterface              $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->chatMessageCollectionFactory = $chatMessageCollectionFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $chatId = $this->getRequest()->getParam('chatId');
            $messages = $this->chatMessageCollectionFactory->create()
                ->addFieldToFilter('chat_id', $chatId)
                ->addFieldToFilter('is_admin', ['null' => true])
                ->addFieldToFilter('is_read', 0);

            $messages->setDataToAll('is_read', 1);
            $messages->save();
            return $result->setData(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return $result->setData(['succes' => false, 'error' => $e->getMessage()]);
        }
    }
}
