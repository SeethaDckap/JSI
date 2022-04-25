<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Form\Element;


class Erpdefaultcontract extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Backend\Helper\Data $backendHelper,
        $data = []
    ) {
        $this->registry = $registry;
        $this->commonHelper = $commonHelper;
        $this->backendHelper = $backendHelper;
        parent::__construct(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $data
        );
    }


    /**
     * @return string
     */
    public function getElementHtml()
    {
        /* @var $customer Epicor_Comm_Model_Customer */
        $customer = $this->registry->registry('current_customer');
        $attributeId = $this->getHtmlId();
        $contracts = $this->commonHelper->customerListsById($customer->getId(), 'filterContracts');
        $defaultContract = $customer->getEccDefaultContract();
        $selectHtml = '<select name="' . $this->getName() . '" id="' . $attributeId . '"' . $this->serialize($this->getHtmlAttributes()) . ' class="select">';
        $selectHtml .= '<option value="">No Default Contract</option>';
        foreach ($contracts['items'] as $info) {
            $code = $info['id'];
            $selected = ($code == $defaultContract ? "selected=selected" : "");
            $selectHtml .= '<option value="' . $code . '" ' . $selected . '>' . $info['title'] . '</option>';
        }
        $selectHtml .= '</select>';
        $selectHtml .= '<input type="hidden" name="ajax_url" id="ajax_url" value="' . $this->backendHelper->getUrl("adminhtml/epicorcommon_customer/fetchaddress/", array()) . '" />';
        $selectHtml .= '<input type="hidden" name="user_id" id="user_id" value="' . $customer->getId() . '" />';
        $selectHtml .= '<div id="loading-mask" style="display:none">
                            <p class="loader" id="loading_mask_loader">Please wait...</p>
                            </div>';
        $selectHtml .= '
            <script type="text/javascript">
            //<![CDATA[
                Event.observe("' . $attributeId . '", "change", function(event) {
                    el = Event.element(event);
                    var getcontractDefault = ' . $attributeId . '.options[' . $attributeId . '.selectedIndex].value;
                    $("loading-mask").show();
                    contractAddressChange(getcontractDefault); 
                });
                document.observe("dom:loaded", function(){
                    var contract_default = document.getElementById("' . $attributeId . '");
                    var getcontractDefault = ' . $attributeId . '.options[' . $attributeId . '.selectedIndex].value;
                    contractAddressChange(getcontractDefault);
                });
            //]]>
            </script>'
        ;

        $selectHtml .= $this->getAfterElementHtml();

        return $selectHtml;
    }

}
