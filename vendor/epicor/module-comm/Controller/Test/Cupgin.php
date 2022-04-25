<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class Cupgin extends \Epicor\Comm\Controller\Test
{



/**
     * Test Action
     */
    public function execute()
    {
        echo '<pre>';
        $xml_str = '<?xml version="1.0"?>
<messages version="1.0">
  <request type="CUPG" id="100008253">
    <header>
      <datestamp>2016-08-08T14:23:10+01:00</datestamp>
      <source>1</source>
      <erp>E10</erp>     
    </header>
    <body>
      <list delete="N">
        <brands>
          <brand>
            <company>EPIC06</company>
            <site>main</site>
            <warehouse/>
            <group/>
          </brand>
        </brands>
        <accounts>
          <accountNumber>36</accountNumber>
          <accountNumber>48</accountNumber>
        </accounts>
        <listCode>8sflistcode</listCode>
        <listTitle>8sfnewlisttitle</listTitle>
        <listSettings>8sflistsettings</listSettings>
        <listStatus>A</listStatus>
        <listDescription>sftestlistdesc8</listDescription>
        <products>
          <product delete="N">
            <productCode>JBRAND02</productCode>
            <unitOfMeasures>
              <unitOfMeasure>
                <unitOfMeasureCode>XX</unitOfMeasureCode
              </unitOfMeasure>
            </unitOfMeasures>
          </product>
        </products>
      </list>
    </body>
  </request>
</messages>';

        //     Mage::getModel('epicor_comm/message_upload')->debug($xml_str);
        $this->_schemeValidation($xml_str, 'upload' . '/' . 'cupg.xsd');
    }

    }
