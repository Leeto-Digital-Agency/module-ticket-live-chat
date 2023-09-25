<?php

namespace Leeto\TicketLiveChat\Helper\Ticket;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class NotifyUserHelper extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var EncryptorInterface
     */
    protected $encryptorInterface;

    /**
     * Construct
     *
     * @param Context               $context
     * @param TransportBuilder      $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface        $inlineTranslation
     * @param EncryptorInterface    $encryptorInterface
     */
    public function __construct(
        Context                 $context,
        TransportBuilder        $transportBuilder,
        StoreManagerInterface   $storeManager,
        StateInterface          $inlineTranslation,
        EncryptorInterface      $encryptorInterface
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->encryptorInterface = $encryptorInterface;
        parent::__construct($context);
    }

    public function sendEmail($fromEmail, $fromName, $toEmail, $ticketSubject, $ticketId)
    {
        $templateId = 'ticket_reply_template';
        $fromEmail = $fromEmail;
        $fromName = $fromName;
        $toEmail = $toEmail;
        $ticketIdParam = $this->encryptorInterface->encrypt($ticketId);

        try {
            $this->inlineTranslation->suspend();

            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ];
            $templateVars = [
                'customer_name' => $toEmail,
                'admin_name' => $fromName,
                'ticket_subject' => $ticketSubject,
                'ticket_id' => $ticketIdParam
            ];
            $from['email'] = $fromEmail;
            $from['name'] = $fromName;
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFromByScope($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }
}
