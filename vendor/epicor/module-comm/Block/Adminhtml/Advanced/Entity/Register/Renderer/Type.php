<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Advanced\Entity\Register\Renderer;


/**
 * Entity register log details renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    private $_typesMap = array(
        'ErpAccount' => 'CUS - ERP Account',
        'SupplierErpAccount' => 'SUSP - ERP Account',
        'ErpAddress' => 'CUS / CAD - ERP Addresses',
        'Related' => 'ALT - Related',
        'UpSell' => 'ALT - UpSell',
        'CrossSell' => 'ALT - CrossSell',
        'CustomerSku' => 'CPN - Customer Sku',
        'CategoryProduct' => 'SGP - Category Products',
        'Category' => 'STG - Categories',
        'Product' => 'STK - Products',
        'Customer' => 'CUCO - Customer Contacts',
        'Supplier' => 'SUCO - Supplier Contacts'
    );
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
     * Render column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());

        return isset($this->_typesMap[$data]) ? $this->_typesMap[$data] : $data;
    }

}
