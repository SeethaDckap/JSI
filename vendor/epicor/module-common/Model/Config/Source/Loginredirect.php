<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Source;


/**
 * Login redirect options for config
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 */
class Loginredirect
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'last_page', 'label' => 'Last page accessed before login'),
            array('value' => 'cms_page', 'label' => 'Selected CMS Page'),
        );
    }

}
