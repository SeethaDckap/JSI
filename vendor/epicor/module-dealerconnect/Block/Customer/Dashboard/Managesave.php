<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Dashboard;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method int getColumnCount()
 * @method void setColumnCount(int $count)
 * @method bool getOnRight()
 * @method void setOnRight(bool $bool)
 * @method bool getOnLeft()
 * @method void setOnLeft(bool $bool)
 */
class Managesave extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_ORDER_READ = 'Dealer_Connect::dealer_orders_read';
    const FRONTEND_RESOURCE_QUOTE_READ = 'Dealer_Connect::dealer_quotes_read';
    const FRONTEND_RESOURCE_INVENTORY_READ = 'Dealer_Connect::dealer_inventory_read';
    const FRONTEND_RESOURCE_CLAIM_READ = 'Dealer_Connect::dealer_claim_read';
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_infoData = array();
    protected $_extraData = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    protected $urlEncoder;

    protected $scopeConfig;

    protected $dashboardInformation;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->backendHelper = $backendHelper;
        $this->_localeResolver = $localeResolver;
        $this->urlEncoder = $urlEncoder;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }

    public function getDashboardSaveUrl()
    {
        return $this->getUrl('dealerconnect/dashboard/managesave');
    }
        public function getDashboardConfiguration()
    {
       return $this->registry->registry('dashboard_configuration');
    }

}
