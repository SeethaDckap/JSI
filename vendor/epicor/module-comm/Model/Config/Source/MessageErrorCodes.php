<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Config\Source;

/**
 * ECC Error message code
 *
 * @author bhavik.ghodasara
 */
class MessageErrorCodes extends \Epicor\Comm\Model\Message\Request\Gor 
{
       
    public function toOptionArray() 
    {
        $options = array();
        $eccMessageModel = $this->error_status_codes;
        foreach ($eccMessageModel as $key => $value) {
            $newValue = str_replace(['(%s):', '- %s', '- "%s"', '"%s"', '%s'], "", $value);
            $options[] = array('value' => $key, 'label' => $key . " - " . $newValue);
        }

        return $options;
    }

}
