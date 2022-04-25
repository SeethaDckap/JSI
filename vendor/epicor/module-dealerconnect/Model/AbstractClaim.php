<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model;


/**
 *  Abstract Claim
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 * 
 */
abstract class AbstractClaim extends \Epicor\Common\Model\AbstractModel
{

    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\DclsFactory
     */
    protected $_dclsFactory;

    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\DcldFactory
     */
    protected $_dcldFactory;

    /**
     * @var \Epicor\Dealerconnect\Helper\Messaging
     */
    protected $_dealerMessagingHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Claimstatus
     */
    protected $_claimStatusMapping;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Claimstatus constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
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
        $this->_dclsFactory = $dclsFactory;
        $this->_dcldFactory = $dcldFactory;
        $this->_dealerMessagingHelper = $dealerMessagingHelper;
        $this->_localeResolver = $localeResolver;
        $this->_claimStatusMapping = $claimStatusMapping;
        $this->_localeDate = $localeDate;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Sends Required messages for claims
     *
     * @param $_erpAccounts
     * @return array
     */
    protected function sendClaimMessages($_erpAccounts)
    {
        $_claimsData = [];
        $helper = $this->_dealerMessagingHelper;
        $languageMapping = $helper->getLanguageMapping(
            $this->_localeResolver->getLocale()
        );
        $requestErpStatusCode = $this->getErpStatusCode('request');
        $closedErpStatusCode = $this->getErpStatusCode('closed');
        $curentDate = $this->_localeDate->date()->format("Y-m-d");
        foreach ($_erpAccounts as $erpAccount) {
            $claimIds = $this->sendDcls($erpAccount, $languageMapping);
            if (!empty($claimIds)) {
                foreach ($claimIds as $key => $claimId) {
                    if ($key === 'closed') {
                        continue;
                    }
                    $claimData = $this->sendDcld($erpAccount, $claimId, $languageMapping);
                    if (isset($claimData['claim_status'])
                        && !is_null($claimData['claim_status'])
                    ) {
                        $claimStatus = $claimData['claim_status'];
                        $_claimsData[$erpAccount][$claimStatus]['claims'][] = $claimId;
                        if (($claimStatus == 'Request' || $claimStatus == $requestErpStatusCode)
                            && isset($claimData['claim_update_due_date'])
                            && !is_null($claimData['claim_update_due_date'])
                        ) {
                            $dueDate = $this->_localeDate
                                ->date($claimData['claim_update_due_date'], null, false)
                                ->format("Y-m-d");
                            $curentDateWeek = $this->_localeDate->date()->format("W");
                            $curentDateYear = $this->_localeDate->date()->format("Y");
                            $dueDateWeek = $this->_localeDate
                                ->date($claimData['claim_update_due_date'], null, false)
                                ->format("W");
                            $dueDateYear = $this->_localeDate
                                ->date($claimData['claim_update_due_date'], null, false
                                )->format("Y");
                            switch (true) {
                                case ($curentDate > $dueDate):
                                    $_claimsData[$erpAccount]['Overdue']['claims'][] = $claimId;
                                    if($curentDateWeek == $dueDateWeek && $curentDateYear == $dueDateYear) {
                                        $_claimsData[$erpAccount]['Week']['claims'][] = $claimId;
                                    }
                                    break;
                                case ($curentDate == $dueDate):
                                    $_claimsData[$erpAccount]['Today']['claims'][] = $claimId;
                                    if($curentDateWeek == $dueDateWeek && $curentDateYear == $dueDateYear) {
                                        $_claimsData[$erpAccount]['Week']['claims'][] = $claimId;
                                    }
                                    break;
                                case ($curentDate < $dueDate):
                                    $_claimsData[$erpAccount]['Future']['claims'][] = $claimId;
                                    if($curentDateWeek == $dueDateWeek && $curentDateYear == $dueDateYear) {
                                        $_claimsData[$erpAccount]['Week']['claims'][] = $claimId;
                                    }
                                    break;
                            }
                        }
                    }
                }
                switch(true) {
                    case (!is_null($closedErpStatusCode)
                        && isset($_claimsData[$erpAccount][$closedErpStatusCode]['claims'])
                        && isset($claimIds['closed'])
                    ):
                        $udStatus = $_claimsData[$erpAccount][$closedErpStatusCode]['claims'];
                        $coreStatus = $claimIds['closed'];
                        $_claimsData[$erpAccount][$closedErpStatusCode]['claims'] = array_merge(
                            array_intersect($udStatus, $coreStatus),
                            array_diff($udStatus, $coreStatus),
                            array_diff($coreStatus, $udStatus)
                        );
                    break;
                    case (!is_null($closedErpStatusCode)
                        && !isset($_claimsData[$erpAccount][$closedErpStatusCode]['claims'])
                        && isset($claimIds['closed'])
                        ):
                            $_claimsData[$erpAccount][$closedErpStatusCode]['claims'] = $claimIds['closed'];
                        break;
                    case (is_null($closedErpStatusCode)
                        && isset($claimIds['closed'])
                    ):
                        $_claimsData[$erpAccount]['Closed']['claims'] = $claimIds['closed'];
                        break;
                }
            }
        }
        return $_claimsData;
    }

    /**
     * Checks if DCLS is active
     *
     * @return bool
     */
    protected function isDclsActive()
    {
        $_dcls = $this->_dclsFactory->create();
        $dclsTypeCheck = $_dcls->getHelper()->getMessageType('DCLS');
        return $_dcls->isActive() && $dclsTypeCheck;
    }

    /**
     * Checks if DCLD is active
     *
     * @return bool
     */
    protected function isDcldActive()
    {
        $_dcld = $this->_dcldFactory->create();
        $dcldTypeCheck = $_dcld->getHelper()->getMessageType('DCLD');
        return $_dcld->isActive() && $dcldTypeCheck;
    }

    /**
     * Sends DCLS message
     *
     * @param $erpAccount
     * @param $languageMapping
     * @return array
     */
    protected function sendDcls($erpAccount, $languageMapping)
    {
        $_caseNumbers = [];
        $dcls = $this->_dclsFactory->create();
        $dcls->setAccountNumber($erpAccount)
            ->setLanguageCode($languageMapping);
        if ($dcls->sendMessage()) {
            $_claims = $dcls->getResults();
            foreach ($_claims as $claim) {
                if ($claim->getStatus() == "CLOSED") {
                    $_caseNumbers['closed'][] = $claim->getCaseNumber();
                } else {
                    $_caseNumbers[] = $claim->getCaseNumber();
                }
            }
        }
        return $_caseNumbers;
    }

    /**
     * Sends DCLD message
     *
     * @param $erpAccount
     * @param $claimId
     * @param $languageMapping
     * @return array
     */
    protected function sendDcld($erpAccount, $claimId, $languageMapping)
    {
        $claimData = [];
        $dcld = $this->_dcldFactory->create();
        $dcld->setAccountNumber($erpAccount)
            ->setCaseNumber($claimId)
            ->setLanguageCode($languageMapping);
        if ($dcld->sendMessage()) {
            $_claim = $dcld->getResults();
            $claimData = [
                'claim_id' => $claimId,
                'claim_status' => $_claim->getClaimStatus(),
                'claim_update_due_date' => $_claim->getClaimUpdateDueDate()
            ];
        }
        return $claimData;
    }

    /**
     * Gets ERP status code
     * @param $status
     * @return mixed
     */
    protected function getErpStatusCode($status)
    {
        return $this->_claimStatusMapping
            ->getClaimStatus([$status])
            ->getFirstItem()
            ->getData('erp_code');
    }
}
