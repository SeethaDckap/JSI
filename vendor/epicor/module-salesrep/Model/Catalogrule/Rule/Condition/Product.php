<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\Catalogrule\Rule\Condition;


/**
 * Model Override for Rule Condition Product, needed for pricing rule display on frontend in salesrep management
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 * 
 * 
 */
class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;
   
    /**
     *  @var \Magento\Framework\Url
    */
    protected $urlHelper;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Url $urlHelper,    
        array $data = []
    ) {
        $this->request = $request;
        $this->_backendData = $backendData;
        $this->urlHelper = $urlHelper;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );
    }


    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        if ($this->request->getModuleName() == 'salesrep') {
            $url = false;

            switch ($this->getAttribute()) {
                case 'sku': case 'category_ids':
                    $url = 'salesrep/promo_widget/chooser'
                        . '/attribute/' . $this->getAttribute();
                    if ($this->getJsFormObject()) {
                        $url .= '/form/' . $this->getJsFormObject();
                    }
                    break;
            } 
            return $url !== false ? $this->urlHelper->getUrl($url) : '';
            //return $url !== false ? $this->_backendData->getUrl($url) : '';
        } else {
            return parent::getValueElementChooserUrl();
        }
    }
    
    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        if ($this->request->getModuleName() == 'salesrep') {
                $html = '';
                switch ($this->getAttribute()) {
                    case 'sku':
                    case 'category_ids':
                        $image = $this->_assetRepo->getUrl('Epicor_SalesRep::epicor/salesrep/images/rule_chooser_trigger.gif');
                        break;
                }

                if (!empty($image)) {
                    $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                        $image .
                        '" alt="" class="v-middle rule-chooser-trigger" title="' .
                        __(
                            'Open Chooser'
                        ) . '" /></a>';
                }
                return $html;
        }else{
                return parent::getValueAfterElementHtml();
        }
       
    }
   
    /**
     * @return string
     */
    public function getRemoveLinkHtml()
    {
         if ($this->request->getModuleName() == 'salesrep') {
                $src = $this->_assetRepo->getUrl('Epicor_SalesRep::epicor/salesrep/images/rule_component_remove.gif');
                $html = ' <span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove" title="' . __(
                    'Remove'
                ) . '"><img src="' . $src . '"  alt="" class="v-middle" /></a></span>';
                return $html;
         }else{
                return parent::getRemoveLinkHtml();
         }
        
    }
    
    
}
