<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Model\Carrier;

use Epicor\BranchPickup\Helper\Branchpickup;
use Epicor\BranchPickup\Helper\Data;
use Epicor\BranchPickup\Model\ResourceModel\Branchpickup as BranchpickupResource;
use Magento\Checkout\Model\CartFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Branchpickup
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Epicorbranchpickup extends AbstractCarrier implements CarrierInterface
{

    const ECC_BRANCHPICKUP = 'eccbranchpickup';

    const ECC_BRANCHPICKUP_COMBINE = 'eccbranchpickup_eccbranchpickup';

    protected $_code = self::ECC_BRANCHPICKUP;

    /**
     * @var Data
     */
    protected $branchPickupHelper;

    /**
     * @var Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    /**
     * @var CartFactory
     */
    protected $checkoutCartFactory;

    /**
     * @var ResultFactory
     */
    protected $shippingRateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $quoteQuoteAddressRateResultMethodFactory;

    /**
     * @var BranchpickupResource
     */
    private $branchpickup;

    /**
     * Epicorbranchpickup constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Data $branchPickupHelper
     * @param Branchpickup $branchPickupBranchpickupHelper
     * @param CartFactory $checkoutCartFactory
     * @param ResultFactory $shippingRateResultFactory
     * @param MethodFactory $quoteQuoteAddressRateResultMethodFactory
     * @param BranchpickupResource|null $branchpickup
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Data $branchPickupHelper,
        Branchpickup $branchPickupBranchpickupHelper,
        CartFactory $checkoutCartFactory,
        ResultFactory $shippingRateResultFactory,
        MethodFactory $quoteQuoteAddressRateResultMethodFactory,
        BranchpickupResource $branchpickup = null,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $data
        );
        $this->branchPickupHelper = $branchPickupHelper;
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        $this->checkoutCartFactory = $checkoutCartFactory;
        $this->shippingRateResultFactory = $shippingRateResultFactory;
        $this->quoteQuoteAddressRateResultMethodFactory = $quoteQuoteAddressRateResultMethodFactory;
        $this->branchpickup = $branchpickup ?: ObjectManager::getInstance()->get(BranchpickupResource::class);
    }

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|null
     */
    public function collectRates(RateRequest $request)
    {
        $helpers = $this->branchPickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        $branchpickupEnabled = $helpers->isBranchPickupAvailable();
        $branchPickuphelper = $this->branchPickupBranchpickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Branchpickup */
        $checkCartPage = $branchPickuphelper->checkPage();

        $isMultiShipping = false;
        $quoteId = $this->checkoutCartFactory->create()->getCheckoutSession()->getQuoteId();
        if ($quoteId) {
            $isMultiShipping = $this->branchpickup->isMultiShipping($quoteId);
        }

        if (($branchpickupEnabled) && (!$isMultiShipping) && (!$checkCartPage)) {
            $result = $this->shippingRateResultFactory->create();
            $method = $this->quoteQuoteAddressRateResultMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));
            $method->setMethodTitles($this->getConfigData('name'));
            $method->setPrice('0.00');
            $method->setCost('0.00');
            $result->append($method);
            return $result;
        }
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            $this->_code => $this->getConfigData('name')
        );
    }
}
