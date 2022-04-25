<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab\Options\Type;


/**
 * EWA option type
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Ewa extends
\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\AbstractType
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $optionPrice,
            $data
        );
        $this->setTemplate('epicor_comm/catalog/product/edit/options/type/ewa.phtml');
    }

}
