<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account\Balances\Period;


use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;

/**
 * Customer Orders list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_information_period_balances_read';

    public function __construct(
        HidePrice $hidePrice,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );

        if ($hidePrice->getHidePrices() && in_array($hidePrice->getHidePrices(), [1, 2, 3])) {
            $this->setTemplate(false);
        }

        $details = $this->registry->registry('customer_connect_account_details');
        if (!is_null($details)) {
            $balanceInfo = $details->getVarienDataArrayFromPath('account/period_balances/period_balance');

            if (count($balanceInfo) == 0) {
                $this->setTemplate(false);
            }
        }
    }

    protected function _setupGrid()
    {
        $this->_controller = 'customer_account_balances_period_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Period Balances');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        $this->setBoxClass('col-1');
        parent::_postSetup();
    }
    
    protected function _prepareLayout()
    {
        // this is needed for frontend grid use to stop search options being retained for future users. the omission of calling the parent is intentional
        // as all the processing required when calling parent:: should be included
        $this->setChild( 'grid',
            $this->getLayout()->createBlock(
                str_replace(
                    '_',
                    '\\',
                    $this->_blockGroup
                ) . '\\Block\\' . str_replace(
                    ' ',
                    '\\',
                    ucwords(str_replace('_', ' ', $this->_controller))
                ) . '\\Grid',
                $this->_controller . '.grid'
            )->setSaveParametersInSession(false) );

        return $this;
    }
}
