<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab;


class Pricingrules extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    public function _construct()
    {
        $this->setId('pricingruleGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Pricing Rules';
    }

    public function getTabTitle()
    {
        return 'Pricing Rules';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $layout = $this->getLayout();

        $html .= $layout->createBlock('Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab\Pricingrules\Form')->toHtml();
        //$html .= '<script type="text/javascript">
        //            pricingRules = new Epicor_SalesRep_Pricing.pricingRules(\'pricing_rule_form\',\'pricing_rules_table\',\'pricing_rules\');
        //            Validation.add(\'validate-date\', \'Please enter a valid date (YYYY-MM-DD format).\', function(v) {
        //                return Validation.get(\'IsEmpty\').test(v) || /^(\d{4})-(\d{1,2})-(\d{1,2})$/.test(v);
        //            })
//
         //       </script>';

        $html .= $layout->createBlock('Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab\Pricingrules\Grid')->setLayout($layout)->toHtml();

        return $html;
    }

}
