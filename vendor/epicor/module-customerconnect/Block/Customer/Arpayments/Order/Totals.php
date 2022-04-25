<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Order;

use Epicor\Customerconnect\Model\ArPayment\Order;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * Associated array of totals
     * array(
     *  $totalCode => $totalObject
     * )
     *
     * @var array
     */
    protected $_totals;

    /**
     * @var Order|null
     */
    protected $_order = null;

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->_totals = [];
        
        $this->_totals['grand_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'value' => $source->getGrandTotal(),
                'label' => __('Grand Total'),
            ]
        );

        /**
         * Base grandtotal
         */
//        if ($this->getOrder()->isCurrencyDifferent()) {
//            $this->_totals['base_grandtotal'] = new \Magento\Framework\DataObject(
//                [
//                    'code' => 'base_grandtotal',
//                    'value' => $this->getOrder()->formatBasePrice($source->getBaseGrandTotal()),
//                    'label' => __('Grand Total to be Charged'),
//                    'is_formated' => true,
//                ]
//            );
//        }
        return $this;
    }

}
