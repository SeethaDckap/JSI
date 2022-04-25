<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Postdata;

use Epicor\Comm\Controller\Adminhtml\Context;
use Magento\Backend\Model\Auth\Session;
use Epicor\Comm\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Epicor\Common\Helper\Xml;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\DataObjectFactory;
class Postdata extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Postdata
{

    /**
     * @var Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Xml
     */
    protected $commonXmlHelper;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \DOMDocument
     */
    protected $_xmlDoc;

    /**
     * Postdata constructor.
     * @param Context $context
     * @param Session $backendAuthSession
     * @param Data $commHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Xml $commonXmlHelper
     * @param EncryptorInterface $encryptor
     * @param DataObjectFactory $dataObjectFactory\
     */
    public function __construct(
        Context $context,
        Session $backendAuthSession,
        Data $commHelper,
        ScopeConfigInterface $scopeConfig,
        Xml $commonXmlHelper,
        EncryptorInterface $encryptor,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->commHelper = $commHelper;
        $this->registry = $context->getRegistry();
        $this->scopeConfig = $scopeConfig;
        $this->commonXmlHelper = $commonXmlHelper;
        $this->encryptor = $encryptor;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Zend_Http_Client_Adapter_Exception
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $xml = '';
        if ($this->getRequest()->getPost('xml')) {
            $xml = base64_decode($this->getRequest()->getPost('post-xml'));
            $storeId = $this->getRequest()->getParam('post_data_store_id');
            $this->_xmlDoc = new \DOMDocument();

            if (@$this->_xmlDoc->loadXML($xml)) {
                $isREST = $this->commHelper->isEnableRest();
                $branding = $this->commHelper->getStoreBranding($storeId);
                $_company = isset($branding['company']) ? $branding['company'] : null;
                $_api_username = $this->scopeConfig->getValue('Epicor_Comm/licensing/username',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $_api_password = $this->encryptor->decrypt($this->scopeConfig->getValue('Epicor_Comm/licensing/password',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

                $adapter = new \Zend_Http_Client_Adapter_Curl();
                $adapter->setCurlOption(CURLOPT_HEADER, false);
                $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
                $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
                $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, 1);
                $adapter->setCurlOption(CURLOPT_POST, 1);
                $adapter->setCurlOption(CURLOPT_TIMEOUT, 60000);

                $_url = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $connection = new \Zend_Http_Client();
                $connection->setUri($_url);
                $connection->setAdapter($adapter);
                $connection->setHeaders('Content-Length', strlen($xml));
                if ($this->scopeConfig->getValue('Epicor_Comm/licensing/erp',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'p21') {
                    $connection->setHeaders('Authorization',
                        'Bearer ' . $this->scopeConfig->getValue('Epicor_Comm/licensing/p21_token',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                } else {
                    $connection->setAuth($_api_username, $_api_password);
                }
                $callSettings = array(
                    'Company' => $_company
                );

                // set XML
                if ($isREST) {
                    $connection->setHeaders('CallSettings', json_encode($callSettings));
                    $connection->setRawData(json_encode(array("xmlInBytes" => base64_encode($xml))),
                        'application/json');
                } else {
                    $connection->setHeaders('CallSettings', base64_encode(json_encode($callSettings)));
                    $connection->setRawData($xml, 'text/xml');
                }

                // send request
                $response = $connection->request(\Zend_Http_Client::POST);
                $xml_back = null;
                if ($isREST) {
                    $xml_back = $this->_convertRestToXml($response);
                } else {
                    $xml_back = $response->getBody();
                }

                // get XML
                $valid_xml = simplexml_load_string($xml_back, 'SimpleXmlElement',
                    LIBXML_NOERROR + LIBXML_ERR_FATAL + LIBXML_ERR_NONE);
                if (false == $valid_xml) {
                    $resp = '';
                    $this->messageManager->addErrorMessage(__('Message is invalid unable to process'));
                } else {
                    $resp = $xml_back;
                    $this->registry->register('ECC_Message_Response', true, true);
                    $this->messageManager->addSuccessMessage(__('XML Message process completed'));
                }
            } else {
                $resp = '';
                $this->messageManager->addErrorMessage(__('Message is invalid unable to process'));
            }

            $data = $this->dataObjectFactory->create([
                'data' => [
                    'xml' => $xml,
                    'post_data_store_id' => $storeId,
                    'erp_response' => $resp
                ]
            ]);

            $this->registry->register('posted_xml_data', $data, true);
        }

        $resultPage = $this->_initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Post Data'));
        return $resultPage;
    }

    /**
     * @param \Zend_Http_Response $response
     * @return bool|string
     */
    protected function _convertRestToXml(\Zend_Http_Response $response)
    {
        $restBody = json_decode($response->getBody(), 1); // Json convert to array
        if (isset($restBody["returnObj"])) {
            $responseBody = base64_decode($restBody["returnObj"]);
            $dom = new \DOMDocument();
            $dom->loadXML($responseBody);
            $dom->formatOutput = true;
            $responseBody = $dom->saveXML();
        } else {
            $this->_logger->error("REST service error: Please validate Config ERP URL or REST service config.");
            $this->_logger->error("REST Response Error body: " . $response->getBody());
            $responseBody = $response->getBody();
        }
        return $responseBody;
    }

}
