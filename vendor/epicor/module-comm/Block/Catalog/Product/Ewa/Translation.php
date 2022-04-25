<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\Ewa;


/**
 * RFQ details js block
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Translation extends \Epicor\Common\Block\Js\Translation
{

    protected function _construct()
    {
        parent::_construct();

        $translations = array(
            /* skin/frontend/base/default/epicor/comm/js/configurator.js */
            'Warning: Your changes will be lost if you close. Click OK if you are you sure you want to close without saving.' => __('Warning: Your changes will be lost if you close. Click OK if you are you sure you want to close without saving.'),
        );

        $this->setTranslations($translations);
    }

}
