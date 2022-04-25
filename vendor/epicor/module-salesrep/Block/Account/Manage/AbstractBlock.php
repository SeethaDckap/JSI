<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage;


/**
 * Sales Rep Account Management Abstract block
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class AbstractBlock extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        array $data = []
    ) {
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
    }

    public function canEdit()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        return $helper->canEdit();
    }

}
