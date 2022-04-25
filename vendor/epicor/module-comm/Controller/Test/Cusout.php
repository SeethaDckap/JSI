<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class Cusout extends \Epicor\Comm\Controller\Test
{

public function execute()
    {
        $xml_str = '<?xml version="1.0"?>
<messages>
  <response type="CUS" id="100001553">
    <header>
      <datestamp>2013-12-24T12:09:58+00:00</datestamp>
      <source>Websales</source>
      <erp>Websales</erp>
    </header>
    <body>
      <status>
        <code>200</code>
        <description></description>
      </status>
    </body>
  </response>
</messages>';
        $this->debug($xml_str);
        $this->_schemeValidation($xml_str, 'upload' . '/' . 'cus.xsd');
    }

    }
