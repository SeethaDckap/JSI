<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer;


/**
 * Dealer Groups admin actions
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
abstract class Groups extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * ACL ID
     *
     * @var string
     */
    protected $_aclId = 'Epicor_Dealerconnect::groups';

    protected $_currentCustomersInErpAccounts = array();
    protected $_erpaccounts = array();

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $dateTimeTimezone;

    /**
     * @var \Epicor\Dealerconnect\Model\DealergroupsFactory
     */
    protected $dealerGroupsModelFactory;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelperFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->registry = $context->getRegistry();
        $this->dealerGroupsModelFactory = $context->getDealerModelFactory();
        $this->backendJsHelper = $context->getBackendJsHelper();
        $this->commHelperFactory = $context->getCommHelperFactory();
        $this->backendSession = $context->getBackendSession();
        $this->jsonHelper = $context->getJsonHelper();
        $this->dateTimeTimezone = $context->getDateTimeTimezone();
        $this->serializer = $context->getSerializer();
        parent::__construct($context, $backendAuthSession);
    }


    /**
     * Saves details for the Dealer Group
     *
     * @param \Epicor\Dealerconnect\Model\Dealergroups $dealerGrp
     * @param array $data
     *
     */
    protected function processDetailsSave($dealerGrp, $data)
    {
        $dealerGrp->setTitle($data['title']);
        $dealerGrp->setDescription($data['description']);
        $dealerGrp->setActive(isset($data['active']) ? 1 : 0);

        if ($dealerGrp->isObjectNew()) {
            $dealerGrp->setCode($data['code']);
        }
    }

    /**
     * Checks if Dealer Accounts Information needs to be saved
     *
     * @param \Epicor\Dealerconnect\Model\Dealergroups $dealerGrp
     *
     * @param array $data
     */
    protected function processERPAccountsSave($dealerGrp, $data)
    {
        $erpaccounts = $this->getRequest()->getParam('selected_erpaccounts');
        if (!is_null($erpaccounts)) {
            $this->saveERPAccounts($dealerGrp, $data);
            // if erp_account_link_type = 'N', save erp account exclusion indicator as 'N', else save value
            $dataExclusion = isset($data['erp_accounts_exclusion']) ? 'Y' : 'N';
            $dealerGrp->setDealerAccountsExclusion($dataExclusion);
        }
    }

    /**
     * Save Dealer Accounts Information
     *
     * @param \Epicor\Dealerconnect\Model\Dealergroups $dealerGrp
     * @param array $data
     *
     * @return void
     */
    protected function saveERPAccounts(&$dealerGrp, $data)
    {
        $erpaccounts = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['erpaccounts']));
        $dealerGrp->removeErpAccounts($dealerGrp->getErpAccounts());
        $dealerGrp->addErpAccounts($erpaccounts);
    }


    /**
     * Deletes the given Dealer Group by id
     *
     * @param integer $id
     * @param boolean $mass
     *
     * @return void
     */
    protected function delete($id, $mass = false)
    {
        $model = $this->dealerGroupsModelFactory->create();
        /* @var $dealerGrp \Epicor\Dealerconnect\Model\Dealergroups */

        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                if ($model->delete()) {
                    if (!$mass) {
                        $this->messageManager->addSuccess(__('Dealer Group deleted'));
                    }
                } else {
                    $this->messageManager->addError(__('Could not delete Dealer Group ' . $id));
                }
            }
        }
    }
    /**
     * Loads Dealer Group
     **/
    protected function loadEntity()
    {
        $id = $this->getRequest()->getParam('id', null);
        $dealerGrp = $this->dealerGroupsModelFactory->create()->load($id);
        /* @var $dealerGrp \Epicor\Dealerconnect\Model\Dealergroups */
        $this->registry->register('dealergrp', $dealerGrp);

        return $dealerGrp;
    }

}
