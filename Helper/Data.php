<?php

namespace Leeto\TicketLiveChat\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Backend\Model\Url;
use Magento\Backend\Model\Auth\Session as BackendSession;

class Data extends AbstractHelper
{
    /**
     * @var string
     */
    public const XML_PATH_USER_AVATAR_IMAGE = 'live_chat/settings/user_avatar_image';

    /**
     * @var string
     */
    public const AVATAR_IMAGE_PATH = 'avatar';

    /**
     * @var string
     */
    public const XML_PATH_SUPPORT_AVATAR_IMAGE = 'live_chat/settings/support_avatar_image';

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var Url
     */
    protected $backendUrlManager;

    /**
     * @var BackendSession
     */
    protected $backendSession;
    
   /**
    * @param Context                     $context
    * @param Session                     $customerSession
    * @param Image                       $imageHelper
    * @param CustomerRepositoryInterface $customerRepositoryInterface
    * @param Url                         $backendUrlManager
    * @param BackendSession              $backendSession
    */
    public function __construct(
        Context                     $context,
        Session                     $customerSession,
        Image                       $imageHelper,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Url                         $backendUrlManager,
        BackendSession              $backendSession
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->imageHelper = $imageHelper;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->backendUrlManager = $backendUrlManager;
        $this->backendSession = $backendSession;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }
    
    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getLoggedInUser()
    {
        if ($this->isLoggedIn()) {
            return $this->customerSession->getCustomer();
        }
        
        return null;
    }

    public function getCustomerById($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    /**
     * @return string
     */
    public function getUserAvatarImagePath()
    {
        if ($this->getScopeValue(self::XML_PATH_USER_AVATAR_IMAGE)) {
            return $this->getMediaBaseUrl() .
                self::AVATAR_IMAGE_PATH . '/' .
                $this->getScopeValue(self::XML_PATH_USER_AVATAR_IMAGE);
        }

        return $this->getDefaultImage();
    }

    /**
     * @return string
     */
    public function getSupportAvatarImagePath()
    {
        if ($this->getScopeValue(self::XML_PATH_SUPPORT_AVATAR_IMAGE)) {
            return $this->getMediaBaseUrl() .
                self::AVATAR_IMAGE_PATH . '/' .
                $this->getScopeValue(self::XML_PATH_SUPPORT_AVATAR_IMAGE);
        }

        return $this->getDefaultImage();
    }

    /**
     * @return string
     */
    public function getDefaultImage()
    {
        return $this->imageHelper->getDefaultPlaceholderUrl('image');
    }

    /**
     * @return string
     */
    public function getMediaBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function getScopeValue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? null;
    }
    
    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getBackendUrl($route, $params = [])
    {
        return $this->backendUrlManager->getUrl($route, $params);
    }

    /**
     * @return string
     */
    public function getWebBaseUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_WEB]);
    }

    /**
     * @return string
     */
    public function getWebsocketPort()
    {
        return $this->getScopeValue('live_chat/settings/websocket_port');
    }

    /**
     * @return string
     */
    public function getChatPosition()
    {
        return $this->getScopeValue('live_chat/settings/live_chat_position');
    }
    
    /**
     * @return string
     */
    public function getWebsocketHost()
    {
        $baseUrl = substr($this->getWebBaseUrl(), strpos($this->getWebBaseUrl(), "//") + 2, -1);
        $port = $this->getWebsocketPort();
        return 'ws://' . $baseUrl . ':' . $port;
    }

    /**
     * @return bool
     */
    public function isBackendAdminLoggedIn()
    {
        return $this->backendSession->getUser() && $this->backendSession->getUser()->getId();
    }
}
