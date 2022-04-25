<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Backend;


/**
 * Check validation for input type is integer or not
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Integer extends \Magento\Framework\App\Config\Value
{

    public function beforeSave() {
        $value = $this->getValue();
        $array = (array) $this->getFieldConfig();
        if ($value) {
            $intValue = $value;
            if (!ctype_digit($intValue)) {
                $message = $array['label'] . ' should be integer';
                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            }
        }
        parent::beforeSave();
    }

}
