<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Details;


/**
 * Order Details page title
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Title extends \Epicor\Dealerconnect\Block\Portal\Inventory\Details\Template
{

    protected $_reorderType = 'Orders';
    protected $_returnType = 'Order';
    protected $_title;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;
    
     /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;    
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;  

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;    

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->urlEncoder = $urlEncoder;
        $this->scopeConfig =$context->getScopeConfig();
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
             $data
        );
    }
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/deid/title.phtml');
    }

    /**
     * Sets the Reorder link url
     */
    public function updateAddressUrl()
    {
        $order = $this->registry->registry('deid_order_details');

        return  $this->getOrderReturnUrl($order);
    }

    /**
     * Sets the Return link url
     */
    protected function _addInventory()
    {
        $deid = $this->registry->registry('deid_order_details');
        $this->_returnUrl = $this->getOrderReturnUrl($deid);
    }
    
    
    /**
     * Returns an order return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $order
     * 
     * @return string 
     */
    public function getOrderReturnUrl($deid)
    {
        $url = $this->getCreateReturnUrl(
            'details', array(
            'location_number' => $deid->getLocationNumber()
            )
        );
        return $url;
    }    
    
    /**
     * Returns an order reorder URL fro the invoice object provided,
     *
     * Also optional to change the return url
     * 
     * @param \Epicor\Comm\Model\Xmlvarien $orderObj
     * @param string $return
     * @return type
     */
    public function getCreateReturnUrl($type, $data)
    {
        $params = array(
            'type' => base64_encode(serialize($type)),
            'data' => base64_encode(serialize($data)),
            'return' => $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl())
        );
        return $this->getUrl('dealerconnect/inventory/address', $params);

    }    
    
    
    public function getConfigFlag($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
    }     
    
    /**
     * Gets the Detail page title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    public function getBomUrl()
    {
        $deid = $this->registry->registry('deid_order_details');
        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $order_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $deid->getLocationNumber() .']:[' . $deid->getIdentificationNumber() .']:[' . $deid->getSerialNumber() ));
        $requestedUrl = $this->getUrl('*/*/billOfMaterials', array('location' => $order_requested));
        return $requestedUrl;
    }
}
