<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Element;


/**
 * Manufacturers attribute display, displays serialized manufacturer data
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Manufacturers extends \Epicor\Common\Lib\Varien\Data\Form\Element\Serialized
{

    protected $_columns = array(
        'name' => array(
            'type' => 'text',
            'label' => 'Name'
        ),
        'product_code' => array(
            'type' => 'text',
            'label' => 'Product Code'
        )
    );

}
