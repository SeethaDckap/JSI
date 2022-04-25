<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Element;


/**
 * ERP Images attribute renderer - renders serialized data
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Erpimages extends \Epicor\Common\Lib\Varien\Data\Form\Element\Serialized
{
    protected  $_data_form_part = 'product_form'; 
    protected $_allowAdd = false;
    protected $_trackRowDelete = true;
    protected $_columns = array(
        'filename' => array(
            'type' => 'static',
            'label' => 'Filename'
        ),
        'description' => array(
            'type' => 'static',
            'label' => 'Description'
        ),
        'types' => array(
            'type' => 'static',
            'label' => 'Types',
            'renderer' => 'Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Types',
        ), 
        'position' => array(
            'type' => 'static',
            'label' => 'Position',
        ), 
        'stores' => array(
            'type' => 'static',
            'label' => 'Stores',
            'renderer' => 'Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Stores'
        ), 
        'status' => array(
            'type' => 'static',
            'label' => 'Status',
            'default' => '0',
            'renderer' => 'Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Status'
        ), 
        'attachment_number' => array(
            'type' => 'static',
            'label' => 'Attachment Id',
        ),
        'erp_file_id' => array(
            'type' => 'static',
            'label' => 'Erp File Id',
        ),
        'url' => array(
            'type' => 'static',
            'label' => 'Erp Attachment Url',
        )
    );
      
}
