<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Erp\Mapping;

/**
 * @method getErpCode()
 * @method getMagentoCode() 
 */
class Paymentmethods {

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    public function __construct(
            
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        $this->_paymentConfig = $paymentConfig;
        $this->paymentHelper = $paymentHelper;
    }

    public function toOptionArray() {
        return $this->getPaymentMethodList(true, true, false);
    }

    /**
     * Retrieve all payment methods list as an array
     *
     * Possible output:
     * 1) assoc array as <code> => <title>
     * 2) array of array('label' => <title>, 'value' => <code>)
     * 3) array of array(
     *                 array('value' => <code>, 'label' => <title>),
     *                 array('value' => array(
     *                     'value' => array(array(<code1> => <title1>, <code2> =>...),
     *                     'label' => <group name>
     *                 )),
     *                 array('value' => <code>, 'label' => <title>),
     *                 ...
     *             )
     *
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $withGroups
     * @param Store|null $store
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getPaymentMethodList($sorted = true, $asLabelValue = false, $withGroups = false, $store = null) {
        $methods = [];
        $groups = [];
        $groupRelations = [];

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            $storedTitle = $this->paymentHelper->getMethodInstance($code)->getConfigData('title', $store);
            if (isset($storedTitle)) {
                $methods[$code] = $storedTitle;
            } elseif (isset($data['title'])) {
                $methods[$code] = $data['title'];
            }

            if ($asLabelValue && $withGroups && isset($data['group'])) {
                $groupRelations[$code] = $data['group'];
            }
        }

        if ($asLabelValue && $withGroups) {
            $groups = $this->_paymentConfig->getGroups();
            foreach ($groups as $code => $title) {
                $methods[$code] = $title;
            }
        }
        if ($sorted) {
            asort($methods);
        }
        if ($asLabelValue) {
            $labelValues = [];
            foreach ($methods as $code => $title) {
                $labelValues[$code] = [];
            }
            foreach ($methods as $code => $title) {
                if (isset($groups[$code])) {
                    $labelValues[$code]['label'] = $title;
                    if (!isset($labelValues[$code]['value'])) {
                        $labelValues[$code]['value'] = null;
                    }
                } elseif (isset($groupRelations[$code])) {
                    unset($labelValues[$code]);
                    $labelValues[$groupRelations[$code]]['value'][$code] = ['value' => $code, 'label' => $title];
                } else {
                    $labelValues[$code] = ['value' => $code, 'label' => $title];
                }
            }
            return $labelValues;
        }

        return $methods;
    }

}
