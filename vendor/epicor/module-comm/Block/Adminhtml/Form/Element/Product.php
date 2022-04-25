<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Element;

use Magento\Framework\Escaper;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;

class Product extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    protected $_labelvalue;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;
    
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;
    
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Epicor\Comm\Model\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        $data = []
    ) {
        $this->_escaper = $escaper;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_renderer = \Magento\Framework\Data\Form::getElementRenderer();
        $this->setType('text');
        $this->setExtType('textfield');
        $this->addClass('product-field');
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->backendHelper = $backendHelper;
    }

    private function getLabelValue($product_id) {
        if (!$this->_labelvalue) {
            $product = $this->catalogProductFactory->create()->load($product_id);
            if ($product->getId())
                $this->_labelvalue = $product->getName();
            else
                $this->_labelvalue = "No Product Selected";
        }
        return $this->_labelvalue;
    }
    
    /**
     * Output the input field and assign calendar instance to it.
     * In order to output the date:
     * - the value must be instantiated (Zend_Date)
     * - output format must be set (compatible with Zend_Date)
     *
     * @return string
     */
    public function getElementHtml() {
        $this->addClass('input-text');

        $html = sprintf('<form><input type="hidden" name="%s" id="%s" value="%s" %s />'.
             '<span id="%s_name" class="erpaccount_label">%s</span>'.
             '<button class="form-button" id="%s_trig" onclick="productSelector.openpopup(\'%s\'); return false;">%s</button>'.
             '<button class="form-button" id="%s_remove" onclick="productSelector.removeProduct(\'%s\'); return false;">%s</button></form>', 
            $this->getName(), 
            $this->getHtmlId(), 
            $this->getValue(), 
            $this->serialize($this->getHtmlAttributes()), 
            $this->getHtmlId(), 
            $this->getLabelValue($this->getValue()), 
            $this->getHtmlId(), 
            $this->getHtmlId(), 
            'Select',
            $this->getHtmlId(), 
            $this->getHtmlId(), 
            'Remove'
        );

        $html .= sprintf('
            <script type="text/javascript">
            //<![CDATA[
                productGridUrl = "%s";
            //]]>
            </script>', $this->backendHelper->getUrl("adminhtml/epicorcomm_customer_erpaccount/listskuproducts")
        );

        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
