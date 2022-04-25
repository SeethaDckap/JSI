<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model;


/**
 * Claim Status Data
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 * 
 */
class Claimstatus extends \Epicor\Dealerconnect\Model\AbstractClaim
{
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $_erpAccountCollection;

    /**
     * Claimstatus constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $erpAccountCollection
     * @param \Epicor\Dealerconnect\ModelMessage\Request\DclsFactory $dclsFactory
     * @param \Epicor\Dealerconnect\ModelMessage\Request\DcldFactory $dcldFactory
     * @param \Epicor\Dealerconnect\Helper\Messaging $dealerMessagingHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $erpAccountCollection,
        \Epicor\Dealerconnect\Model\Message\Request\DclsFactory $dclsFactory,
        \Epicor\Dealerconnect\Model\Message\Request\DcldFactory $dcldFactory,
        \Epicor\Dealerconnect\Helper\Messaging $dealerMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_erpAccountCollection = $erpAccountCollection;
        parent::__construct(
            $context,
            $registry,
            $dclsFactory,
            $dcldFactory,
            $dealerMessagingHelper,
            $localeResolver,
            $claimStatusMapping,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\Claimstatus');
    }

    /**
     * Updates the Claim Status Data for Dashboard
     * @return void
     */
    public function updateClaimsStatus()
    {
        $_erpAccounts = $this->getDealerErpAccounts();
        if (!empty($_erpAccounts)) {
            return  $this->processClaims($_erpAccounts);
           // $this->saveClaimStatusData($_claimStatusData);
        }
        return false;
    }

    /**
     * Returns Dealer ERP accounts
     * @return array
     */
    public function getDealerErpAccounts()
    {
        $erpAccounts = [];
        $erpAccountCollection = $this->_erpAccountCollection->create();
        $erpAccountCollection
            ->addFieldToSelect('account_number')
            ->addFieldToFilter('account_type', 'dealer');
        $erpAccounts = $erpAccountCollection->getData();
        $erpAccounts = array_column($erpAccounts, 'account_number');
        return $erpAccounts;
    }

    /**
     * Getting Claims Status Data from ERP
     *
     * @param $_erpAccounts
     * @return array|void
     */
    public function processClaims($_erpAccounts)
    {
        if (!$this->isDclsActive()
            || !$this->isDcldActive()
        ) {
            return;
        }
        if (!is_array($_erpAccounts)) {
            $_erpAccounts = [
                $_erpAccounts
            ];
        }
        $claimStatusData = $this->sendClaimMessages($_erpAccounts);
        return $claimStatusData;
    }

    /**
     * Saves the Claim Status Data
     *
     * @param $_claimStatusData
     */
    public function saveClaimStatusData($_claimStatusData, $erp = null)
    {
        $_rowDatas = [];
        if (!empty($_claimStatusData)) {
            $curentDate = $this->_localeDate->date(null, null, false)->format("Y-m-d H:i:s");
            foreach ($_claimStatusData as $erpId => $_data) {
                foreach ($_data as $status => $data) {
                    if ($status == 'Week') {
                        continue;
                    }
                    $_extraInfo = "";
                    $extraInfo['claims'] = $data['claims'];
                    $_count = count($data['claims']);
                    if ($_count > 0) {
                        $_extraInfo = json_encode($extraInfo);
                    }
                    $_rowDatas[] = [
                        'updated_at' => $curentDate,
                        'erp_account_number'=> $erpId,
                        'status_code' => $status,
                        'count' => $_count,
                        'extra_info' => $_extraInfo
                    ];
                }
            }
        }
        $this->getResource()->saveClaimStatusData($_rowDatas, $erp);
        return;
    }

    /**
     * Get the Claim Status data for the customer
     * @param array $status
     * @return mixed
     */
    public function getClaimsStatuses($status = [])
    {
        $erpAccountInfo = $this->_dealerMessagingHelper->getErpAccountInfo();
        $erpAccountNumber = $erpAccountInfo->getAccountNumber();
        return $this->getResource()->getClaimsData($erpAccountNumber, $status);
    }

    /**
     * @return mixed
     */
    public function clearData()
    {
        return $this->getResource()->clearData();
    }
}
