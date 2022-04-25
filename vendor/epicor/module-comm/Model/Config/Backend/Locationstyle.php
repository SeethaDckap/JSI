<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Config\Backend;

/**
 * Default Erp account backend controller
 * 
 * Updates the Default ERP code if the Erp account changes
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Locationstyle extends \Magento\Framework\App\Config\Value {

    public function beforeSave() {
        $style = $this->getValue();
        if ($style == 'inventory_view') {
            $defaultB2CLocation = $this->getFieldsetDataValue('b2cdefault');
            $defaultB2BLocation = $this->getFieldsetDataValue('b2bdefault');
            $defaultGuestLocation = $this->getFieldsetDataValue('guestdefault');
            if (!$defaultB2CLocation || !$defaultB2BLocation || !$defaultGuestLocation) {
                throw new \Magento\Framework\Exception\ValidatorException(__("An error occurred while saving this configuration: All Default Locations need to be set for Inventory View."));
            }
        }
        parent::beforeSave();
    }

}
