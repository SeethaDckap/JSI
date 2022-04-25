<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Test;

class Request extends \Epicor\Comm\Controller\Test
{


    public function execute()
    {
        $xml_str = '';
        $this->debug($xml_str);
        $this->_schemeValidation($xml_str, 'request' . '/' . "{$msgType}.xsd");
    }

}
