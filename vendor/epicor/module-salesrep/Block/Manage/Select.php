<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Manage;


/**
 * SalesRep page select page grid
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Select extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->eventManager = $context->getEventManager();
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_controller = 'Manage_Select';
        $this->_headerText = __('Account Selector');
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
        $this->setTemplate('Epicor_Common::widget/grid/container.phtml');

    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->scopeConfig->isSetFlag('epicor_salesrep/general/masquerade_search_dashboard', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->eventManager->dispatch('adminhtml_widget_container_html_before', array('block' => $this));
            return parent::_toHtml();
        } else {
            return '';
        }
    }

}
