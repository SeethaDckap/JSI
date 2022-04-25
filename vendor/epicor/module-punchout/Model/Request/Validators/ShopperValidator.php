<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Request\Validators;

use Epicor\Comm\Helper\Data as CommHelper;
use Magento\Customer\Model\CustomerFactory;
use Epicor\Punchout\Model\ValidatorInterface;
use Epicor\Punchout\Model\Request\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\Punchout\Model\ResourceModel\Connections\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManager;

/**
 * Class for header validation
 */
class ShopperValidator extends Validator implements ValidatorInterface
{

    const CUSTOMER_EMAIL_TAG = 'UserEmail';

    /**
     * Connection collection.
     *
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Customer Factory
     *
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * Comm helper.
     *
     * @var CommHelper
     */
    private $commHelper;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Constructor.
     *
     * @param CollectionFactory                                 $collectionFactory Connection collection factory.
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory           $customerFactory
     * @param \Magento\Store\Model\StoreManager                 $storeManager
     * @param \Epicor\Comm\Helper\Data                          $commHelper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        StoreManager $storeManager,
        CommHelper $commHelper
    ) {
        $this->collectionFactory  = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->customerFactory    = $customerFactory;
        $this->storeManager       = $storeManager;
        $this->commHelper         = $commHelper;
        parent::__construct(
            $collectionFactory
        );

    }//end __construct()


    /**
     * Validate data
     *
     * @param \SimpleXMLElement $request Request data object.
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException LocalizedException.
     */
    public function validate(\SimpleXMLElement $request)
    {
        $id               = null;
        $error            = 1;
        $errorCode        = '401';
        $headerSender = $request->Header->Sender;
        $sharedSecret = (string) $headerSender->Credential->SharedSecret;
        $identity     = (string) $request->Header->From->Credential->Identity;
        if (!empty((string) $headerSender->Credential->Identity)) {
            $identity     = (string) $headerSender->Credential->Identity;
        }
        $requestBody = $request->Request->PunchOutSetupRequest ? : $request->Request->OrderRequest;
        $extrinsic   = $requestBody->Extrinsic;
        $connectionData   = $this->getPunchoutConnection($identity, $sharedSecret);

        if (!empty((string) $requestBody->Contact->Email)) {
            $userEmail = (string) $requestBody->Contact->Email;
        } else {
            $userEmail = $this->getUserEmail($extrinsic, $connectionData);
        }

        if ($connectionData->getId()) {
            $websiteId = ($connectionData->getWebsiteId() > 0) ? $connectionData->getWebsiteId(): $this->getDefaultWebSiteId();
            $storeId   = ($connectionData->getStoreId() > 0) ? $connectionData->getStoreId(): $this->getStoreIdByWebsiteId($websiteId);
            $shopperId = $connectionData->getDefaultShopper();
        }
        if (!empty($userEmail)) {
            $shopperDetails = $this->getCustomerIdByEmail($userEmail, $websiteId);
            $shopperId      = $shopperDetails['shopper_id'];
            if ($shopperDetails['error']) {
                return $shopperDetails;
            }
        }
        $erpAccount   = $this->commHelper->getErpAccountByAccountNumber($identity);
        $erpAccountId = $erpAccount->getId();

        $customerobj           = $this->customerFactory->create();
        $validCustomerCollection = $customerobj->getCollection()
                                               ->addFieldToFilter('entity_id', $shopperId)
                                               ->addFieldToFilter('website_id', $websiteId)
                                               ->addAttributeToFilter('ecc_erpaccount_id', $erpAccountId)
                                               ->getFirstItem();
        if (!empty($validCustomerCollection->getId())) {
            $id    = $validCustomerCollection->getId();
            $error = 0;
        }
        return [
            'shopper_id'  => $id,
            'website_id'  => $websiteId,
            'store_id'    => $storeId,
            'website_url' => $this->getWebsiteUrl($websiteId),
            'error'       => $error,
            'code'        => $errorCode,
            'error_message' =>'customer does not belongs to connection website',
        ];

    }//end validate()


    /**
     * Get User Email from extrinsic tag.
     *
     * @param $extrinsic
     *
     * @return string
     */
    public function getUserEmail($extrinsic, $connectionData)
    {
        $userEmail = '';
        $extrinsicEmailTag = $this->getExtrinsicEmailTag($connectionData);

        if (!empty($extrinsic)) {
            foreach ($extrinsic as $v) {
                if ((string) $v->attributes()['name'] === $extrinsicEmailTag) {
                    $userEmail = (string) $v;
                }
            }
        }

        return $userEmail;

    }//end getUserEmail()


    /**
     * Get the Default Shopper Name.
     *
     * @param $defaultShopper
     *
     * @return string
     */
    public function getExtrinsicEmailTag($connectionData)
    {
        return  !empty($connectionData->getExtrinsicEmailTag()) ? $connectionData->getExtrinsicEmailTag() :self::CUSTOMER_EMAIL_TAG;

    }//end getDefaultShopperEmail()


    /**
     *  Get Customer Id By Email.
     *
     * @param string $defaultShopper Email Id.
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerIdByEmail(string $defaultShopper, $websiteId)
    {
        $customerId = null;
        try {
            $customerData = $this->customerRepository->get($defaultShopper, $websiteId);
            $customerId   = (int)$customerData->getId();
            return [
                'shopper_id' => $customerId,
                'error'      => 0,
            ];
        } catch (NoSuchEntityException $noSuchEntityException) {
            return [
                'shopper_id'    => $customerId,
                'error'         => 1,
                'code'          => '401',
                'error_message' => $noSuchEntityException->getMessage(),
            ];
        }

    }//end getCustomerIdByEmail()


    /**
     *
     */
    public function getDefaultWebSiteId()
    {
        return  $this->storeManager->getWebsite()->getId();
    }

    /**
     * Get store id by website id
     *
     * @param int $id
     * @return id
     */
    public function getStoreIdByWebsiteId(int $websiteId)
    {
        return $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();

    }//end getStoreIdByWebsiteId()

    public function getWebsiteUrl(int $websiteId)
    {
        return $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getBaseUrl();

    }//end getStoreIdByWebsiteId()


}//end class
