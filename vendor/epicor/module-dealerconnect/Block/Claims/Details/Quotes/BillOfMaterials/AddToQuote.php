<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\BillOfMaterials;


/**
 * Add to quote block
 *
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class AddToQuote extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;
   
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        array $data = []
    )
    {
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
         $this->setTemplate('Epicor_Dealerconnect::claims/details/quotes/billofmaterials/addtoquote.phtml');
    }
    
    public function getProductId()
    {
        $productId = $this->catalogProductFactory->create()->getIdBySku($this->getData('sku'));
        return $productId;
    }
}
