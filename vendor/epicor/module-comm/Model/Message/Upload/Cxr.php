<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * Response CAD - Upload Customer Address Record
 *
 * Send customer’s delivery details up to Websales
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Cxr extends \Epicor\Comm\Model\Message\Upload
{

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $directoryCurrencyFactory;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Directory\Model\CurrencyFactory $directoryCurrencyFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->directoryCurrencyFactory = $directoryCurrencyFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/cxr_mapping/');
        $this->setMessageType('CXR');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_XRATE);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');

    }

    /**
     * Process a request
     *
     * @param array $requestData
     * @return
     */
    public function processAction()
    {
        $this->erpData = $this->getRequest()->getExchange();

        $helper = $this->getHelper();

        if (!is_null($this->erpData)) {
            $new_exchange_rates = array();
            $exchange_rates = $this->getVarienDataArray('exchange_rates');

            $notInUse = array();
            $notMapped = array();
            $currencies = $this->_getStoreCurrencies();
            $added = 0;
            foreach ($exchange_rates as $exchange_rate) {
                $helper = $this->getHelper();

                $fromCode = $this->getVarienData('from_currency', $exchange_rate);
                $toCode = $this->getVarienData('to_currency', $exchange_rate);

                $currency_from = $helper->getCurrencyMapping($fromCode, $helper::ERP_TO_MAGENTO);
                $currency_to = $helper->getCurrencyMapping($toCode, $helper::ERP_TO_MAGENTO);
                $currency_rate = ($this->getVarienDataFlag('delete', $exchange_rate)) ? 1 : $this->getVarienData('rate', $exchange_rate);

                $add = true;

                if (!$helper->isCurrencyCodeValid($currency_from)) {
                    $notMapped[] = $fromCode;
                    $add = false;
                } else if (!in_array($currency_from, $currencies['base'])) {
                    if ($currency_from != $fromCode) {
                        $notInUse[] = $fromCode . ' (' . $currency_from . ')';
                    } else {
                        $notInUse[] = $fromCode;
                    }
                }

                if (!$helper->isCurrencyCodeValid($currency_to)) {
                    $notMapped[] = $toCode;
                    $add = false;
                } else if (!in_array($currency_to, $currencies['view'])) {
                    if ($currency_to != $toCode) {
                        $notInUse[] = $toCode . ' (' . $currency_to . ')';
                    } else {
                        $notInUse[] = $toCode;
                    }
                }

                if ($add) {
                    $added++;
                    $new_exchange_rates[$currency_from][$currency_to] = $currency_rate;
                }
            }

            $notMappedValues = implode(', ', array_unique($notMapped));
            $notInUseValues = implode(', ', array_unique($notInUse));

            $status = '';

            //M1 > M2 Translation Begin (Rule 55)
            /*if (!empty($notInUseValues)) {
                if (count($notInUse) == 1) {
                    $status .= __('Currency %s is not in use on any store. ', $notInUseValues);
                } else {
                    $status .= __('Currencies %s are not in use on any store. ', $notInUseValues);
                }
            }

            if (!empty($notMappedValues)) {
                if (count($notMapped) == 1) {
                    $status .= __('Currency code %s does not map to any ECC currency. ', $notMappedValues);
                } else {
                    $status .= __('Currency codes %s do not map to any ECC currencies. ', $notMappedValues);
                }
            }*/
            if (!empty($notInUseValues)) {
                if (count($notInUse) == 1) {
                    $status .= __('Currency %1 is not in use on any store. ', $notInUseValues);
                } else {
                    $status .= __('Currencies %1 are not in use on any store. ', $notInUseValues);
                }
            }

            if (!empty($notMappedValues)) {
                if (count($notMapped) == 1) {
                    $status .= __('Currency code %1 does not map to any ECC currency. ', $notMappedValues);
                } else {
                    $status .= __('Currency codes %1 do not map to any ECC currencies. ', $notMappedValues);
                }
            }
            //M1 > M2 Translation End

            if ($added == 0) {
                throw new \Exception($status, self::STATUS_UNKNOWN);
            } else {
                $this->directoryCurrencyFactory->create()->saveRates($new_exchange_rates);
                $this->setStatusDescription($status);
            }
        } else {
            throw new \Exception($this->getErrorDescription(self::STATUS_XML_TAG_MISSING, 'body -> exchange'), self::STATUS_XML_TAG_MISSING);
        }
    }

    /**
     * Gets an array of languages by checking each store for it's language
     *
     * @return array - array of languages
     */
    private function _getStoreCurrencies()
    {
        $stores = $this->storeManager->getStores();

        $currencies = array(
            'base' => array(),
            'view' => array(),
        );

        foreach ($stores as $store) {
            /* @var $store Epicor_Comm_Model_Store */
            if (!in_array($store->getBaseCurrencyCode(), $currencies['base'])) {
                $currencies['base'][] = $store->getBaseCurrencyCode();
            }

            if (!in_array($store->getBaseCurrencyCode(), $currencies['view'])) {
                $currencies['view'][] = $store->getCurrentCurrencyCode();
            }
        }

        return $currencies;
    }

}
