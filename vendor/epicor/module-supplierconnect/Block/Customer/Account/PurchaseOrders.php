<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Supplierconnect\Block\Customer\Account;


class PurchaseOrders extends \Epicor\Supplierconnect\Block\Customer\Info
{

    protected $_linkTo = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    protected $urlEncoder;

    protected $enableOrders;

    protected $dashboardInformation;
    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->registry = $registry;
        $this->backendHelper = $backendHelper;
        $this->_localeResolver = $localeResolver;
        $this->urlEncoder = $urlEncoder;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $supplierconnectHelper,
            $registry,
            $localeResolver,
            $backendHelper,
            $urlEncoder,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->dashboardInformation = $this->getDashboardInformation();
        if(empty($this->dashboardInformation)) {
            $this->enableOrders = true;
        } else {
            $this->enableOrders = (isset($this->dashboardInformation['enable_order_supplier'])) ? true: false;
        }
        if (($this->registry->registry('supplier_connect_account_details')) && ($this->enableOrders)) {
            $this->setTitle(__('Orders'));
            $this->setTemplate('supplierconnect/customer/account/supplierinfoorders.phtml');
            $details = $this->registry->registry('supplier_connect_account_details');
            $purchaseOrders = $details->getPurchaseOrders();
            $locale = $this->_localeResolver->getLocale();

            $helper = $this->supplierconnectHelper;

            $this->_linkTo = array(
                'Open :'=> array('link' => $this->getUrl('supplierconnect/orders/new')),
                'PO Line / Release Changes :' => array('link' => $this->getUrl('supplierconnect/orders/changes')),
            );

            foreach ($this->_linkTo as $key => $value) {
                $this->_linkTo[$key]['link'] = $value['link'];
                $this->_linkTo[$key]['active'] = true;
            }

            $this->_infoData = array(
                'Open :' => $purchaseOrders->getOpen(),
                'PO Line / Release Changes :' => $purchaseOrders->getChanges()
            );
            $this->setColumnCount(3);
            $this->setOnLeft(true);
        }

    }

    public function getDashboardInformation() {
        return $this->supplierconnectHelper->getDashboardInformation();
    }

    public function getOrderFilter() {
        if(empty($this->dashboardInformation)) {
            $filterVals  ='Open,POLineReleaseChanges';
        } else {
            $filterVals =  $this->dashboardInformation['order_filter'];
        }
        return $filterVals;
    }

    public function getLinkTo() {
        return $this->_linkTo;
    }

    public function getOrderEnable() {
        return $this->enableOrders;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(
            'Epicor_Supplier::supplier_orders_read'
        )) {
            return '';
        }

        return parent::_toHtml();
    }
}