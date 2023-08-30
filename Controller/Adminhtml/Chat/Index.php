<?php

namespace Leeto\TicketLiveChat\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;

class Index extends Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Leeto_TicketLiveChat::leeto_menu');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Live Chat'));
        $this->_view->renderLayout();
    }
}
