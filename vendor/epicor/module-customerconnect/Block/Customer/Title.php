<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer;


/**
 * Customer connect details page title class
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 * 
 */
class Title extends \Magento\Framework\View\Element\Template
{

    const FRONTEND_RESOURCE_REORDER = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;
    const FRONTEND_RESOURCE_RETURN = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;
    const FRONTEND_RESOURCE_ORDER_PRINT = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;
    const FRONTEND_RESOURCE_ORDER_EMAIL = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;
    const FRONTEND_RESOURCE_RETURN_CREATE = "Epicor_Customerconnect::customerconnect_account_returns_create";

    protected $_title;
    protected $_reorderUrl;
    protected $_returnUrl;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    private $customerconnectHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commReturnsHelper = $commReturnsHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $data
        );
        $this->customerconnectHelper = $customerconnectHelper;
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Customerconnect::customerconnect/title.phtml');
    }

    /**
     * Returns whether an entity can be reordered or not
     * 
     * @return boolean
     */
    public function canReorder()
    {
        if ($this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->_isAccessAllowed(static::FRONTEND_RESOURCE_REORDER) &&
                $this->_isAccessAllowed('Epicor_Checkout::checkout_checkout_can_checkout');
        }
    }

    /**
     * Returns whether an entity can be returned or not
     * 
     * @return boolean
     */
    public function canReturn()
    {
        $returnsHelper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $canReturn = false;

        if ($returnsHelper->isReturnsEnabled() && $returnsHelper->checkConfigFlag('allow_create')) {
            $canReturn = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_RETURN)
                        && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_RETURN_CREATE);
        }

        return $canReturn;
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

    /**
     * Gets the Reorder Url for this entity
     * 
     * @return string
     */
    public function getReorderUrl()
    {
        return $this->_reorderUrl;
    }

    public function getPrintAction()
    {
        $linkData = $this->getViewDetailsTypeData();
        return $this->getLayout()
            ->createBlock('\Epicor\Customerconnect\Block\DocumentPrint\ActionLinks')
            ->setData('account_number', $this->customerconnectHelper->getErpAccountNumber())
            ->setData('entity_key', $linkData['entity_key'] ?? '')
            ->setData('entity_document', $linkData['entity_document'] ?? '')
            ->setData('access_print', static::FRONTEND_RESOURCE_ORDER_PRINT)
            ->setData('access_email', static::FRONTEND_RESOURCE_ORDER_EMAIL)
            ->setTemplate('Epicor_Customerconnect::customerconnect/document_print/actionlink.phtml')
            ->toHtml();

    }

    private function getViewDetailsTypeData()
    {
        switch ($this->getData('type')) {
            case 'Epicor\Customerconnect\Block\Customer\Invoices\Details\Title\Interceptor':
                return $this->getInvoiceData();
                break;
            case 'Epicor\Customerconnect\Block\Customer\Shipments\Details\Title\Interceptor':
                return $this->getPackData();
                break;
            case 'Epicor\Customerconnect\Block\Customer\Orders\Details\Title\Interceptor':
                return $this->getOrderData();
                break;
            default:
                return $this->getOrderData();
        }
    }

    private function getInvoiceData()
    {
        $shipmentDetails = $this->getRegistryData('customer_connect_invoices_details');
        $entityKey = $shipmentDetails->getData('invoice_number') ?? '';
        $entityDoc = 'invoice';
        return ['entity_key' => $entityKey, 'entity_document' => $entityDoc];
    }

    private function getPackData()
    {
        $shipmentDetails = $this->getRegistryData('customer_connect_shipments_details');
        $entityKey = $shipmentDetails->getData('packing_slip') ?? '';
        $entityDoc = 'pack';
        return ['entity_key' => $entityKey, 'entity_document' => $entityDoc];
    }

    private function getOrderData()
    {
        return ['entity_key' => $this->getOrderNumber(), 'entity_document' => 'order'];
    }

    private function getOrderNumber()
    {
        if ($customerConnectOrderDetails = $this->getRegistryData('customer_connect_order_details')) {
            return $customerConnectOrderDetails->getData('order_number');
        }
    }

    private function getErpAccountNumber()
    {
        return $this->customerconnectHelper->getCustomer()->getCustomerErpAccount()->getAccountNumber();
    }

    private function getRegistryData($registryKey)
    {
        $registry = $this->commReturnsHelper->getRegistry();
        if ($registry instanceof \Magento\Framework\Registry && $registry->registry($registryKey)) {
            return $registry->registry($registryKey);
        }
    }


    /**
     * Gets the Reorder Url for this entity
     * 
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_returnUrl;
    }

    /**
     * Returns whether an entity can be edited or not
     * 
     * @return boolean
     */
    public function canEdit()
    {
        return false;
    }

    /**
     * Returns whether an entity can be deleted or not
     * 
     * @return boolean
     */
    public function canDelete()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }
}
