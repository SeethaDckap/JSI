<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product;


class Ewa extends \Magento\Framework\View\Element\Template
{

    protected $_ewaData;
    protected $_cimData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Helper\messaging
     */
    protected $commMessageHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor ;
    
    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;        
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;      

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Comm\Helper\messaging $commMessageHelper,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->request = $request;
        $this->commHelper = $commHelper;
        $this->commMessageHelper = $commMessageHelper;
        $this->_encryptor  = $encryptor;
        $this->storeManager = $context->getStoreManager();
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        
        $this->setTemplate('Epicor_Comm::product/ewa.phtml');
        
        parent::__construct(
            $context,
            $data
        );
    }


    private function getEWAData()
    {
        if (!$this->_ewaData)
            $this->_ewaData = $this->registry->registry('EWAData');
        
        return $this->_ewaData;
    }

    private function getCIMData()
    {
        if (!$this->_cimData)
            $this->_cimData = $this->registry->registry('CIMData');
        return $this->_cimData;
    }

    private function getEWASku()
    {
        return $this->registry->registry('EWASku');
    }

    public function hasEWAData()
    {
        return (bool) $this->getEWAData();
    }

    public function getFormUrl()
    {
        //return $this->getUrl(base64_decode($this->request->getParam('return')));
        return $this->scopeConfig->getValue('epicor_comm_enabled_messages/cim_request/ewa_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigName()
    { //<CompanyID>_VER<ConfigVersion>._<ConfigID>
        $configName = $this->getEWAData()->getConfigName();
        return $configName->getCompanyId() . '_VER' . $configName->getConfigVersion() . '.' . $configName->getConfigId();
    }

    public function getRelatedToTable()
    {
        return $this->getEWAData()->getRelatedToTable();
    }

    public function getRelatedToRowID()
    {
        return $this->getEWAData()->getRelatedToRowId();
    }

    public function getPartNum()
    {
        return $this->getEWASku(); //$this->getEWAData()->getProductCode();
    }

    public function getPartRev()
    {
        return $this->getEWAData()->getProductRevision();
    }

    public function getGroupSequence()
    {
        return $this->getEWAData()->getGroupSequence();
    }

    public function getStyleSheet()
    {
        //M1 > M2 Translation Begin (Rule p2-5.4)
        /*$file = Mage::getDesign()->getFilename('css/ewa.css', array('_type' => 'skin'));
        $url = Mage::getDesign()->getSkinUrl('css/ewa.css');*/
        $file = $this->getTemplateFile('css/ewa.css');
        $url = $this->getViewFileUrl('css/ewa.css');
        //M1 > M2 Translation End

        if (!file_exists($file)) {
            $params['ajax'] = 1;
            $params['allow_url'] = 0;
            $url = $this->getUrl('epicor_comm/configurator/ewacss', $params);
        }
        return $url;
    }
    
    public function getEwcStyleSheet()
    {
        //M1 > M2 Translation Begin (Rule p2-5.4)
        /*$file = Mage::getDesign()->getFilename('css/ewa.css', array('_type' => 'skin'));
        $url = Mage::getDesign()->getSkinUrl('css/ewa.css');*/
        $file = $this->getTemplateFile('css/ewc.css');
        $url = $this->getViewFileUrl('css/ewc.css');
        //M1 > M2 Translation End

        if (!file_exists($file))
            $url = $this->getUrl('epicor_comm/configurator/ewccss');

        return $url;
    }    

    public function getLanguage()
    {
        $helper = $this->commMessageHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        return $helper->getLanguageMapping($this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }

    public function getReturnUrl()
    {

        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        if (!$this->request->getParam('return')) {
            $route = 'epicor_comm/configurator/ewacomplete';
        } else {
            $route = base64_decode($this->request->getParam('return'));
        }

        $params = array(
            'SKU' => $helper->getUrlEncoder()->encode($this->getPartNum()),
            'EWACode' => $helper->getUrlEncoder()->encode($this->getRelatedToRowID()),
            'GroupSequence' => $helper->getUrlEncoder()->encode($this->getGroupSequence()),
            'location' => $helper->getUrlEncoder()->encode($this->request->getParam('location')),
            'qty' => $helper->getUrlEncoder()->encode($this->request->getParam('qty')),
        );

        if ($this->getCIMData()->getQuoteId()) {
            $params['quoteId'] = $helper->getUrlEncoder()->encode($this->getCIMData()->getQuoteId());
        }

        if ($this->getCIMData()->getLineNumber()) {
            $params['lineNumber'] = $helper->getUrlEncoder()->encode($this->getCIMData()->getLineNumber());
        }

        if ($this->getCIMData()->getItemId()) {
            $params['itemId'] = $helper->getUrlEncoder()->encode($this->getCIMData()->getItemId());
        }
        $params['ajax']= 1;
        $params['allow_url'] = 0;
        return $this->getUrl($route, $params);
    }

    public function getECCUser()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $username = $this->scopeConfig->getValue('Epicor_Comm/licensing/ewa_username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $helper->eccEncode($username);
    }

    public function getECCPwd()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $password = $this->_encryptor->decrypt($this->scopeConfig->getValue('Epicor_Comm/licensing/ewa_password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        return $helper->eccEncode($password);
        #return md5($helper->decrypt(Mage::getStoreConfig('Epicor_Comm/licensing/password')));
    }

    public function getECCCompanyId()
    {
        return $this->storeManager->getStore()->getWebsite()->getEccCompany() ?: $this->storeManager->getStore()->getGroup()->getEccCompany();
    }
    
    public function getParentUrl() {
        return $this->request->getParam('parenturl');
    }
    
    public function checkLicensed()
    {
        return $this->commConfiguratorHelper->checkConfiguratorProductLicensed();
    }    
    
    public function getEWCUser()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $username = $this->scopeConfig->getValue('Epicor_Comm/licensing/ewc_username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $helper->eccEncode($username);
    }

    public function getEWCPwd()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $password = $this->_encryptor->decrypt($this->scopeConfig->getValue('Epicor_Comm/licensing/ewc_password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        return $helper->eccEncode($password);
        #return md5($helper->decrypt(Mage::getStoreConfig('Epicor_Comm/licensing/password')));
    }    

    public function getEwcAppUrl()
    {
        //return $this->getUrl(base64_decode($this->request->getParam('return')));
        return $this->scopeConfig->getValue('epicor_comm_enabled_messages/cim_request/ewc_appurl', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }       
    
    public function getKineticOrConfigurator() {
        $productId = $this->request->getParam('productId');
        $Kinetic=false;
        if(!empty($productId)) {
            $product = $this->catalogProductFactory->create()->load($productId);
            if($product->getEccProductType() =="K") {
                $Kinetic=true;
            }
        }
        return $Kinetic;
    }       

    public function getEwcFormUrl()
    {
        //return $this->getUrl(base64_decode($this->request->getParam('return')));
        return $this->scopeConfig->getValue('epicor_comm_enabled_messages/cim_request/ewc_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }     
    
}
