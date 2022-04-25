<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Balances\Aged;


/**
 * Customer Orders list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
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

        $details = $this->registry->registry('customer_connect_arpayments_details');
        if (!is_null($details)) {
            $balanceInfo = $details[0]->getVarienDataArrayFromPath('account/aged_balances/aged_balance');

            if (count($balanceInfo) == 0) {
                $this->setTemplate(false);
            }
        }
    }

    protected function _setupGrid()
    {
        $this->_controller = 'customer_arpayments_balances_aged_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_ar_payment_aged_debts_read")) {
            $this->_headerText = __('Aged Balances');
        } else {
            $this->_headerText = __('');
        }
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        //$this->setBoxClass('col-1');
        parent::_postSetup();
    }

    protected function _prepareLayout()
    {
        // this is needed for frontend grid use to stop search options being retained for future users. 
        // the omission of calling the parent is intentional
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
