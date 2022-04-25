<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class Cccnin extends \Epicor\Comm\Controller\Test
{




/**
     * Test Action
     */
    public function execute()
    {
        echo '<pre>';
        $xml_str = '<?xml version="1.0" encoding="UTF-8"?>
<messages version="1.0">
  <request type="CCCN" id="95a483270f52e91701a4f7b3f5e449b7">
    <header>
      <datestamp>2013-08-12T13:54:25+00:00</datestamp>
      <source>ERP SIMulator</source>
      <erp>ECC</erp>
    </header>
    <body>
      <contract delete="N">
        <brands>
          <brand>
            <company>EPIC06</company>
            <site/>
            <warehouse/>
            <group/>
          </brand>
        </brands>
        <accountNumber>36</accountNumber>
        <contractCode>CTCO6</contractCode>
        <currencyCode>USD</currencyCode>
        <contractTitle>Contract1</contractTitle>
        <startDate>2016-01-01</startDate>
        <endDate>2016-12-12</endDate>
        <contractStatus>A</contractStatus>   
        <lastModifiedDate>2016-12-12</lastModifiedDate>
        <salesRep>Sales Re</salesRep>
        <contactName>Contact Name</contactName>
        <purchaseOrderNumber>PON11</purchaseOrderNumber>
        <contractDescription>sfcontractdescription2</contractDescription>
        <deliveryAddresses>
          <deliveryAddress>
            <addressCode>ADDCD2</addressCode>
            <purchaseOrderNumber>PON11</purchaseOrderNumber>
            <name>AddName</name>
            <address1>Add1</address1>
            <address2>Add2</address2>
            <address3>Add3</address3>
            <city>City</city>
            <county>County</county>
            <country>US</country>
            <postcode>0011</postcode>
            <telephoneNumber>TelNum</telephoneNumber>
            <faxNumber>FaxNum</faxNumber>
            <emailAddress>email@example.com</emailAddress>
          </deliveryAddress>
          <deliveryAddress>
            <addressCode>ADDCD2</addressCode>
            <purchaseOrderNumber>PON11</purchaseOrderNumber>
            <name>AddName</name>
            <address1>Add1</address1>
            <address2>Add2</address2>
            <address3>Add3</address3>
            <city>City2</city>
            <county>County</county>
            <country>US</country>
            <postcode>0011</postcode>
            <telephoneNumber>TelNum</telephoneNumber>
            <faxNumber>FaxNum</faxNumber>
            <emailAddress>email@example.com</emailAddress>
          </deliveryAddress>
        </deliveryAddresses>
        <parts>
          <part delete="N">
            <productCode>10320</productCode>
            <contractLineNumber>0001</contractLineNumber>
            <contractPartNumber>UOMP2</contractPartNumber>
            <effectiveStartDate>2016-02-02</effectiveStartDate>
            <effectiveEndDate>2016-11-11</effectiveEndDate>
            <lineStatus>A</lineStatus>
            <unitOfMeasures>
              <unitOfMeasure default="Y">
                <unitOfMeasureCode>EA</unitOfMeasureCode>
                <minimumOrderQty>0</minimumOrderQty>
                <maximumOrderQty>10</maximumOrderQty>
                <contractQty>10</contractQty>
                <isDiscountable>Y</isDiscountable>
                <currencies>
                  <currency>
                    <currencyCode>USD</currencyCode>
                    <contractPrice>10</contractPrice>
                    <breaks>
                      <break>
                        <quantity>2</quantity>
                        <contractPrice>5</contractPrice>
                        <discount>
                          <description>test br desc</description>
                        </discount>
                      </break>
                      <break>
                        <quantity>4</quantity>
                        <contractPrice>2</contractPrice>
                        <discount>
                          <description>test br desc</description>
                        </discount>
                      </break>
                    </breaks>
                    <valueBreaks>
                      <valueBreak>
                        <lineValue>x</lineValue>
                        <contractPrice>2</contractPrice>
                        <discount>
                          <description>testdesc</description>
                        </discount>
                      </valueBreak>
                    </valueBreaks>
                  </currency>
                </currencies>
              </unitOfMeasure>
            </unitOfMeasures>
          </part>
          <part delete="N">
            <productCode>JBRAND02</productCode>
            <contractLineNumber>0002</contractLineNumber>
            <contractPartNumber>UOMP3</contractPartNumber>
            <effectiveStartDate>2016-02-02</effectiveStartDate>
            <effectiveEndDate>2016-11-11</effectiveEndDate>
            <lineStatus>A</lineStatus>
            <unitOfMeasures>
              <unitOfMeasure default="N">
                <unitOfMeasureCode>EA</unitOfMeasureCode>
                <minimumOrderQty>2</minimumOrderQty>
                <maximumOrderQty>12</maximumOrderQty>
                <contractQty>14</contractQty>
                <isDiscountable>Y</isDiscountable>
                <currencies></currencies>
              </unitOfMeasure>
            </unitOfMeasures>
          </part>
        </parts>
      </contract>
    </body>
  </request>
</messages>';

        //     Mage::getModel('epicor_comm/message_upload')->debug($xml_str);
        $this->_schemeValidation($xml_str, 'upload' . '/' . 'cccn.xsd');
    }

    }
