<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


use Epicor\Comm\Model\MinOrderAmountFlag;

/**
 * Request AST - Account Status Enquiry
 *
 * Get the account information for the specified customer account
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 *
 * @method setIsDeamon(bool $deamon)
 * @method bool getIsDeamon()
 * @method setAccountNumber(string $erpAccountCode)
 * @method setCurrencyCode(string $currencyCode)
 * @method string getCurrencyCode()
 *
 */
class Ast extends \Epicor\Comm\Model\Message\Request
{

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     *
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    private $minOrderAmountFlag;

    /**
     * Approval Budget Date Period
     * Between Amount
     *
     * @var bool|array
     */
    private $periods = false;

    /**
     * Approval Budget Date Period
     * Between Amount.
     *
     * @var bool|array
     */
    private $responcePeriods = false;

    /**
     * Ast constructor.
     * @param MinOrderAmountFlag $minOrderAmountFlag
     * @param \Epicor\Comm\Model\Context $context
     * @param \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        MinOrderAmountFlag $minOrderAmountFlag,
        \Epicor\Comm\Model\Context $context,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('AST');
        $this->setLicenseType('Customer');
        $this->setConfigBase('epicor_comm_enabled_messages/ast_request/');
        $this->setAccountNumber($this->commHelper->getErpAccountNumber());
        $this->setStore($this->storeManager->getStore()->getId());
        $this->customerSession = $context->getCustomerSession();
        $this->minOrderAmountFlag = $minOrderAmountFlag;
    }

    /**
     * Creates an array of cache keys for the message
     *
     * @return array
     */
    public function getCacheKeys()
    {
        if (empty($this->_keys)) {
            $this->_keys = array();
            $brandKey = $this->_brand->getCompany()
                . $this->_brand->getSite()
                . $this->_brand->getWarehouse()
                . $this->_brand->getGroup();

            $currenciesKey = $this->getHelper()->getCurrencyMapping($this->getCurrencyCode());

            $this->_keys = array($this->getAccountNumber(true), $brandKey, $currenciesKey);
        }

        return $this->_keys;
    }

    /**
     * Bulds the XML request from the set data on this message.
     * @return bool successful message.
     */
    public function buildRequest()
    {
        if ($this->getIsDeamon()) {
            $this->_brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
        }

        $erpCode = $this->getAccountNumber();
        $loginId = '';
        if ($erpCode) {
            // For Bistrack ERP
            if ($this->customer && $this->scopeConfig->isSetFlag("Epicor_Comm/integrations/webtrack_enable", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) &&
                    $this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'bistrack') {
                $loginId = $this->customer->getEmail();
            }

            $this->setMessageSecondarySubject($erpCode);
            $message = $this->getMessageTemplate();
            $message['messages']['request']['body'] = array_merge($message['messages']['request']['body'], array(
                'customer' => array(
                    '_attributes' => array(
                        'includeCredits' => $this->getperiods() ? 'Y' : 'N',
                    ),
                    'accountNumber' => $erpCode,
                    'currencyCode' => $this->getHelper()->getCurrencyMapping($this->getCurrencyCode()),
                    'periods' => $this->getperiods()
                        ? array('period' => $this->getperiods())
                        : null,
                ),
            ));

            // For WebTrack
            if ($this->customer && $this->customer->getIsWebtrack()) {
                $message['messages']['request']['body']['customer'] = array_merge($message['messages']['request']['body']['customer'], array(
                    'loginId' => $loginId //ECC V3.0.0 new tab
                ));
            }
            $this->setOutXml($message);
            return true;
        } else {
            return 'Missing Account Number';
        }
    }

    /**
     * Process the message response.
     * If running as deamon return true if message successful regardless of message status code.
     * @return bool successful
     */
    public function processResponse()
    {
        $success = false;
        $helper = $this->commMessagingHelper->create();

        if ($this->getIsDeamon()) {
            if ($this->isSuccessfulStatusCode()) {
                $success = true;
            }

            if ($success) {
                $this->configConfig
                    ->setSection('Epicor_Comm')
                    ->setWebsite(null)
                    ->setStore(null)
                    ->setGroups(array(
                        'xmlMessaging' => array(
                            'fields' => array(
                                'failed_msg_count' => array(
                                    'value' => 0
                                )
                            )
                        )
                    ))
                    ->save();
            }

            $this->configConfig
                ->setSection('Epicor_Comm')
                ->setWebsite(null)
                ->setStore(null)
                ->setGroups(array(
                    'xmlMessaging' => array(
                        'fields' => array(
                            'failed_msg_online' => array(
                                'value' => $success
                            )
                        )
                    )
                ))
                ->save();

            $this->storeManager->reinitStores();
        } else {
            if ($this->getIsSuccessful()) {
                $response = $this->getResponse();
                $erpdata = $helper->getErpAccountInfo($this->getCustomerGroupId());
                $erpCustomerId = $erpdata->getId();
                if ($this->isSuccessfulStatusCode()) {
                    /**
                     * Apply Order Approval ERP Budget.
                     * Periods will get when ERP budget should
                     * applicable on checkout shipping page.
                     *
                     */
                    $budgetPeriods = $this->_getGroupedData(
                        'periods',
                        'period',
                        $response->getAccount()
                    );
                    if ($budgetPeriods) {
                        $this->setResponsePeriods($budgetPeriods);
                    }

                    // Update ERP customer group balances
                    $customer = $this->commCustomerErpaccountFactory->create()->load($erpCustomerId);
                    /* @var $customer Epicor_Comm_Model_Customer_Erpaccount */
                    $account = $response->getAccount();
                    $currencyCode = $this->getHelper()->getCurrencyMapping($account->getCurrencyCode(), 'e2m');
                    if ($this->getHelper()->isCurrencyCodeValid($currencyCode)) {
                        $customer->addCurrency($currencyCode);

                        $customer->setOnstop((($account->getData('_attributes')->getOnStop() == 'Y') ? 1 : 0), $currencyCode);
                        $customer->setBalance($account->getBalance(), $currencyCode);
                        $customer->setCreditLimit($account->getCreditLimit(), $currencyCode);
                        $customer->setUnallocatedCash($account->getUnallocatedCash(), $currencyCode);
                        if ($this->minOrderAmountFlag->isMinOrderSupportedByErp()) {
                            $customer->setMinOrderAmount(
                                $account->getMinOrderValue(),
                                $currencyCode
                            );
                        }
                    }
                    if ($contracts = $account->getContracts()) {
                        $customer->setAllowedContractType($contracts->getAllowedContractType());
                        $customer->setRequiredContractType($contracts->getRequiredContractType());
                        $customer->setAllowNonContractItems(($contracts->getAllowNonContractItems() == 'T') ? 1 : 0);
                    }

                    $customer->save();

                    if (!empty($account->getCertificate())) {
                        $this->customerSession->setSsoToken($account->getCertificate());
                    }
                    return true;
                }
            }
        }
        return $success;
    }

    /**
     * set customer
     *
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        parent::setCustomer($customer);
    }

    /**
     * Set Budget Periods.
     *
     * @param array $periods
     */
    public function setPeriods($periods)
    {
        $this->periods = $periods;
    }

    /**
     * Get AST response
     * for budget periods.
     *
     * @return false|array
     */
    public function getperiods()
    {
        if ($this->periods) {
            return $this->periods;
        }

        return false;
    }

    /**
     * Set AST response
     * for budget periods.
     *
     * @param array $periods
     */
    public function setResponsePeriods($periods)
    {
        $result = [];
        foreach ($periods as $period) {
            $startDate = date("Y-m-d", strtotime($period["period_from"]));
            $endDate = date("Y-m-d", strtotime($period["period_to"]));
            $keyName = $startDate."_".$endDate;
            $result[$keyName] = $period;
        }

        $this->responcePeriods = $result;
    }

    /**
     * get AST response data
     * for budget periods.
     *
     * @return array|bool
     */
    public function getResponsePeriods()
    {
        return $this->responcePeriods;
    }

}
