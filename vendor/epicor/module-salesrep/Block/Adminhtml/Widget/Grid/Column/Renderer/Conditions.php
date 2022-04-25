<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Widget\Grid\Column\Renderer;


/**
 * Sales Rep Pricing Rule Conditions Renderer
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Conditions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_SalesRep_Model_Pricing_Rule */
        return ($row->getConditions()) ? '<div class="conditions_html" id="conditions_' . $row->getId() . '">' . $row->getConditions()->setJsFormObject('rule_conditions_fieldset')->asHtmlRecursive() . '</div>' : '';
    }

}
