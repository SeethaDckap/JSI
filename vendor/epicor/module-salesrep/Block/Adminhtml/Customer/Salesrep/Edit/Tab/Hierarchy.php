<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab;


class Hierarchy extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{


    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,

        array $data = []
    )
    {
        $this->layout = $context->getLayout();
        parent::__construct(
            $context,
            $data
        );
        $this->setId('hierachyGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Hierarchy';
    }

    public function getTabTitle()
    {
        return 'Hierarchy';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= $this->layout->createBlock('epicor_salesrep/adminhtml_customer_salesrep_edit_tab_hierarchy_parents');

        return $html;
    }

}
