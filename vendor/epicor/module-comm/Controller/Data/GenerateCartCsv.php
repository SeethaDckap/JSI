<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Data;

use Magento\Framework\App\Filesystem\DirectoryList;

class GenerateCartCsv extends \Epicor\Comm\Controller\Data {
    /*
      public function postdataAction()
      {
      $xml = '';
      if ($this->getRequest()->getParam('xml')) {
      $xml = $this->getRequest()->getParam('xml');
      $_url = Mage::getStoreConfig('Epicor_Comm/xmlMessaging/url');
      $_api_username = Mage::getStoreConfig('Epicor_Comm/licensing/username');
      $_api_password = Mage::helper('epicor_comm')->decrypt(Mage::getStoreConfig('Epicor_Comm/licensing/password'));
      $_company = Mage::app()->getStore()->getWebsite()->getCompany() ? : Mage::app()->getStore()->getGroup()->getCompany();

      $connection = new Zend_Http_Client();
      $adapter = new Zend_Http_Client_Adapter_Curl();
      $connection->setUri($_url);
      //$adapter->setCurlOption(CURLOPT_URL, $this->url);
      $adapter->setCurlOption(CURLOPT_HEADER, FALSE);

      $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, FALSE);
      $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, FALSE);
      $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, 1);

      // post options
      $adapter->setCurlOption(CURLOPT_POST, 1);
      $adapter->setCurlOption(CURLOPT_TIMEOUT, 60000);
      $connection->setAdapter($adapter);


      $connection->setHeaders('Content-Length', strlen($xml));
      if (Mage::getStoreConfig('Epicor_Comm/licensing/erp') == 'p21') {
      $connection->setHeaders('Authorization', 'Bearer ' . Mage::getStoreConfig('Epicor_Comm/licensing/p21_token'));
      } else {
      $connection->setAuth($_api_username, $_api_password);
      }

      $callSettings = array(
      'Company' => $_company
      );

      //        if(is_array($this->_pools)) {
      //            if(array_key_exists('eccweb_service', $this->_pools))
      //                    $callSettings['ECCWebService'] = $this->_pools['eccweb_service'];
      //
      //            if(array_key_exists('eccweb_form', $this->_pools))
      //                    $callSettings['ECCWebForm'] = $this->_pools['eccweb_form'];
      //        }

      $connection->setHeaders('CallSettings', base64_encode(json_encode($callSettings)));

      $connection->setRawData($xml, 'text/xml');
      $response = $connection->request(Zend_Http_Client::POST);
      $xml_back = $response->getBody();
      echo "<p><strong>URL : </strong>" . $_url . '</p>';
      echo "<p><strong>HTTP Status : </strong>" . $response->getStatus() . '</p>';


      $helper = Mage::helper('epicor_common/xml');


      $valid_xml = simplexml_load_string ($xml_back, 'SimpleXmlElement', LIBXML_NOERROR+LIBXML_ERR_FATAL+LIBXML_ERR_NONE);
      if (false == $valid_xml) {
      echo '<h2>Received non (or invalid) XML</h2>';
      echo nl2br(htmlentities($xml_back));
      } else {
      echo '<h2>Received XML</h2>';
      echo $helper->convertXmlToHtml($xml_back);
      }
      }

      echo '<hr>
      <form method="POST">
      <textarea name="xml" rows="30" cols="100" >' . $xml . '</textarea>
      <input type="submit" value="Send Xml" />
      </form>
      ';
      }
     */

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;
    private $_fileFactory;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Epicor\Comm\Helper\Product $commProductHelper, \Magento\Catalog\Model\ProductFactory $catalogProductFactory, \Magento\Framework\Session\Generic $generic, \Epicor\Comm\Helper\Locations $commLocationsHelper, \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->_fileFactory = $fileFactory;
        parent::__construct(
                $context
        );
    }

    /**
     * Generates a CSV that can be used for upload
     */
    public function execute() {
        //added by Tani
        $fileName = 'example_cart.csv';
        $locHelper = $this->commLocationsHelper;
        /* @var $helper Epicor_Comm_Helper_Locations */

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=example_cart.csv");
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies

        $header = '"SKU","QTY","UOM"';
        if ($locHelper->isLocationsEnabled()) {
            $header .= ',"LOCATION"';
        }
        $colValues = '"ExampleProduct1","1","EA"';
        if ($locHelper->isLocationsEnabled()) {
            $colValues .= ',"Location 1"';
        }
        $data = $header . PHP_EOL . $colValues;
        $this->_fileFactory->create(
                $fileName, $data, DirectoryList::VAR_DIR, 'application/octet-stream'
        );
    }

}
