<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Element;


/**
 * Related documents attribute renderer - renders serialized data
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Relateddocuments extends \Epicor\Common\Lib\Varien\Data\Form\Element\Serialized
{

    protected $_columns = array(
        'filename' => array(
            'type' => 'text',
            'label' => 'Filename'
        ),
        'description' => array(
            'type' => 'text',
            'label' => 'Description'
        ),
        'is_erp_document' => array(
            'type' => 'checkbox',
            'label' => 'From ERP?',
            'disabled' => true,
            'default' => 0
        )
    );

}
