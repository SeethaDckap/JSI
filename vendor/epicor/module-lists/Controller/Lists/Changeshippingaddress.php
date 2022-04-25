<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

/**
 * Class Change shipping address.
 */
class Changeshippingaddress extends \Magento\Framework\App\Action\Action
{

    /**
     * BranchPickupHelper.
     *
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * ListsFrontendRestrictedHelper.
     *
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    private $listsFrontendRestrictedHelper;


    /**
     * Change shipping address constructor.
     *
     * @param \Magento\Framework\App\Action\Context    $context                       Context.
     * @param \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper ListsFrontendRestrictedHelper.
     * @param \Epicor\BranchPickup\Helper\Data         $branchPickupHelper            BranchPickupHelper.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper
    ) {
        $this->branchPickupHelper            = $branchPickupHelper;
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        parent::__construct(
            $context
        );

    }//end __construct()


    /**
     * Change address Action in delivery address grid.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $frontendHelper = $this->listsFrontendRestrictedHelper;
        /* @var $frontendHelper \Epicor\Lists\Helper\Frontend\Restricted */
        $addressId      = $this->getRequest()->getParam('addressid');
        $branchselect   = $this->getRequest()->getParam('branchpickupvisible');
        $removeProducts = [];
        if ($frontendHelper->listsEnabled()) {
            $removeProducts = $frontendHelper->checkProductAddress($addressId);
        }

        if (isset($branchselect)) {
            $helperBranch = $this->branchPickupHelper;
            /* @var $helper \Epicor\BranchPickup\Helper\Data */
            $helperBranch->emptyBranchPickup();
            $helperBranch->resetBranchLocationFilter();
        }

        $this->sendAjaxResponse($removeProducts, $addressId);

    }//end execute()


    /**
     * Send Ajax Response.
     *
     * @param array  $values    Values.
     * @param string $addressId AddressId.
     *
     * @return void
     */
    protected function sendAjaxResponse($values, $addressId)
    {
        $frontendHelper = $this->listsFrontendRestrictedHelper;
        /* @var $frontendHelper \Epicor\Lists\Helper\Frontend\Restricted */

        $result = [
            'type'      => 'success',
            'values'    => !empty($values) ? $values : array(),
            'addressid' => $addressId,
            'details'   => $frontendHelper->getShippingAddress($addressId),
        ];

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));

    }//end sendAjaxResponse()


}
