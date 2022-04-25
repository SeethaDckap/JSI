<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Config\Form\Field;


class Erpaccount extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Form\Element\ErpaccountFactory
     */
    protected $commAdminhtmlFormElementErpaccountFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Comm\Block\Adminhtml\Form\Element\ErpaccountFactory $commAdminhtmlFormElementErpaccountFactory,
        array $data = []
    ) {
        $this->commAdminhtmlFormElementErpaccountFactory = $commAdminhtmlFormElementErpaccountFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * GetElementHtml
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Element.
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $newElement = $this->commAdminhtmlFormElementErpaccountFactory->create(['data' => $element->getData()]);

        $newElement->setForm($element->getForm());
        $html  = $newElement->getElementHtml();
        $html .= '<script type="text/javascript">
            require(["jquery"], function ($) {
                $(document).ready(function () {
                    $(\'#'.$element->getHtmlId().'_inherit\').click(function() {
                        $(\'#customer_create_account_qs_default_erpaccount_inherit\').trigger(\'click\');
                    });
                });
            });
            </script>';
        return $html;

    }//end _getElementHtml()


}//end class
