<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab\Options\Type\Ecc;


/**
 * ECC option type
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Text extends
\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\AbstractType
{

    /**
     * @var string
     */
    protected $_template = 'Epicor_Comm::epicor_comm/catalog/product/edit/options/type/ecc/text.phtml';
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice,
        array $data = []
    )
    {
        exit; 
        parent::__construct(
            $context,
            $optionPrice,
            $data
        );
        $this->setTemplate('Epicor_Comm::epicor_comm/catalog/product/edit/options/type/ecc/text.phtml');
    }

}
