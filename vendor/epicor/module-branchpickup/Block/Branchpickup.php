<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block;


/**
 * Branch Pickup Block
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Branchpickup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutsession,
        array $data = []
    )
    {
        $this->checkoutSession = $checkoutsession;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    //M1 > M2 Translation Begin (Rule p2-5.1)
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }
    //M1 > M2 Translation End

}
