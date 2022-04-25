<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab;

class Status extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
                $context, $data
        );
        
        $this->_title = 'Status';
        $this->setTemplate('epicor_comm/sales/returns/view/status.phtml');
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function getReturn() {
        return $this->registry->registry('return');
    }

    public function getErpSyncStatus() {
        $return = $this->getReturn();
        $status = '';
        if ($return->getErpSyncStatus() == 'N') {
            $status = __('Not Sent to ERP');
        } else if ($return->getErpSyncStatus() == 'E') {
            $status = __('Error Sending to ERP');
        } else if ($return->getErpSyncStatus() == 'S') {
            $status = __('Sent to ERP Successfully');
        }

        return $status;
    }

    public function canShowTab() {
        return true;
    }

    public function getTabLabel() {
        return $this->_title;
    }

    public function getTabTitle() {
        return $this->_title;
    }

    public function isHidden() {
        return false;
    }

}
