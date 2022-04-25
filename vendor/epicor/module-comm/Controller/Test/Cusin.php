<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class Cusin extends \Epicor\Comm\Controller\Test
{

public function execute()
    {
        $xml_str = '<?xml version="1.0" encoding="utf-8"?>
<messages xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <request type="CUS" id="100001553">
    <header>
      <datestamp>2013-12-24T12:10:18+00:00</datestamp>
      <source>1</source>
      <erp>E10</erp>
    </header>
    <body>
      <customer delete="N" allowBackOrders="N">
        <accountNumber>2</accountNumber>
        <accountName>Addison, INC</accountName>
        <taxCode>EU</taxCode>
        <brands>
          <brand>
            <company>EPIC03</company>
            <site />
            <warehouse />
            <group />
          </brand>
        </brands>
        <account onStop="N">
          <balance>56705.750</balance>
          <creditLimit>1000000</creditLimit>
          <unallocatedCash>0</unallocatedCash>
          <baseCurrencyCode>USD</baseCurrencyCode>
          <emailAddress />
          <salesRep>LANE</salesRep>
          <minOrderValue />
          <currencies>
            <currency>
              <currencyCode>USD</currencyCode>
            </currency>
            <currency>
              <currencyCode>EUR</currencyCode>
            </currency>
          </currencies>
        </account>
        <defaults>
          <registeredAddress>
            <addressCode>2RG</addressCode>
            <name>Addison, INC</name>
            <address1>210 Martin Luther King, Jr. Blvd</address1>
            <address2 />
            <address3 />
            <city>Madison</city>
            <county>WI</county>
            <country>USA</country>
            <postcode>53703</postcode>
            <telephoneNumber />
            <faxNumber />
          </registeredAddress>
          <deliveryAddress>
            <addressCode>001</addressCode>
            <name>Addison, INC</name>
            <address1>215 Martin Luther King, Jr. Blvd</address1>
            <address2>Door 2</address2>
            <address3 />
            <city>Madison</city>
            <county>WI</county>
            <country>USA</country>
            <postcode>53703</postcode>
            <telephoneNumber>608-555-5678</telephoneNumber>
            <faxNumber>608-555-5666</faxNumber>
            <carriageText>Leave in blue shed on Wednesdays please. </carriageText>
          </deliveryAddress>
          <invoiceAddress>
            <addressCode>2BT</addressCode>
            <name>Addison, INC</name>
            <address1>210 Martin Luther King, Jr. Blvd</address1>
            <address2 />
            <address3 />
            <city>Madison</city>
            <county>WI</county>
            <country>USA</country>
            <postcode>53703</postcode>
            <telephoneNumber />
            <faxNumber />
          </invoiceAddress>
          <registrationEmailAddress>test@epicor.com</registrationEmailAddress>
        </defaults>
      </customer>
    </body>
  </request>
</messages>';
        $this->debug($xml_str);
        $this->_schemeValidation($xml_str, 'upload' . '/' . 'cus.xsd');
    }

    }
