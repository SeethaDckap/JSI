<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Lib\Varien\Data\Form\Element;


use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;


/**
 * Serialized data form field, uses abstract array renderer to display a serialized attribute
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Serialized extends \Epicor\Common\Lib\Varien\Data\Form\Element\AbstractArray
{
    protected $_columns;
    protected $_trackChanges = false;
    protected $_rowsContainIds = false;
     /*
     * @var Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Status
     */
    protected $erp_images_Status;
   
    /*
     * @var Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Stores
     */
    protected $erp_images_Stores;
    /*
     * @var Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Types
     */
    protected $erp_images_Types;
    
    protected function getColumns()
    {
        return $this->_columns;
    }

    protected function getRowData()
    {
        $rows = array();

        $data = $this->getData();
        if (!empty($data['value'])) {
            $rows = $data['value'];
        }

        return $rows;
    }


public function __construct(
    Factory $factoryElement,
    CollectionFactory $factoryCollection,
    Escaper $escaper,
    \Epicor\Common\Helper\Data $commonHelper,
     Mapping\SourceModelReader $sourceModelReader,
    \Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Status  $erp_images_Status,
    \Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Stores $erp_images_Stores,
    \Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Types $erp_images_Types,
    array $data = [])
{
    parent::__construct(
        $factoryElement,
        $factoryCollection,
        $escaper,
        $commonHelper,
        $sourceModelReader,
        $erp_images_Status,
        $erp_images_Stores,
        $erp_images_Types,       
        $data);
    $this->setType('serialized');
}
}