<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use \Psr\Log\LoggerInterface;
use Epicor\Punchout\Model\Config;

/**
 * Punchout Shipping Model.
 */
class PunchoutShipping extends AbstractCarrier implements CarrierInterface
{

    /**
     * Method code.
     *
     * @var string
     */
    protected $_code = 'punchout_carrier';

    /**
     * Is Fixed.
     *
     * @var boolean
     */
    protected $_isFixed = false;

    /**
     * Rate result factory.
     *
     * @var ResultFactory
     */
    private $rateResultFactory;

    /**
     * Rate method factory.
     *
     * @var MethodFactory
     */
    private $rateMethodFactory;

    /**
     * Config Model
     *
     * @var Epicor\Punchout\Model\Config
     */
    private $config;


    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig       Scope Config.
     * @param ErrorFactory         $rateErrorFactory  Error factory.
     * @param LoggerInterface      $logger            Logger.
     * @param ResultFactory        $rateResultFactory Rate result factory.
     * @param MethodFactory        $rateMethodFactory Rate method factory.
     * @param Config               $config            Config model.
     * @param array                $data              Data array.
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Config $config,
        array $data=[]
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->config            = $config;

    }//end __construct()


    /**
     * Punchout Shipping Rates Collector
     *
     * @param RateRequest $request Rate request.
     *
     * @return Result|boolean
     */
    public function collectRates(RateRequest $request)
    {
        $isPunchoutActive = $this->config->isPunchoutEnable();
        $quote = null;
        if (!($this->getConfigFlag('active') && $isPunchoutActive)) {
            return false;
        }

        if (is_array($request->getAllItems())) {
            $item = current($request->getAllItems());
            if ($item instanceof QuoteItem) {
                $request->setQuote($item->getQuote());
                $quote = $item->getQuote();
            }
        }

        if (!$quote || !$quote->getIsPunchout()) {
            return false;
        }

        $result = $this->rateResultFactory->create();

        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($quote->getPunchoutShippingCode());

        $shippingCost = (float) $quote->getPunchoutShippingAmount();

        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);

        return $result;

    }//end collectRates()


    /**
     * Get allowed shipping methods
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];

    }//end getAllowedMethods()


}//end class

