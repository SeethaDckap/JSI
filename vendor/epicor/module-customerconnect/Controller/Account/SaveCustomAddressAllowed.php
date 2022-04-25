<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Account;

class SaveCustomAddressAllowed extends \Epicor\Customerconnect\Controller\Account
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->request = $request;
        $this->logger = $logger;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commHelper,
            $customerResourceModelCustomerCollectionFactory,
            $commonAccessGroupCustomerFactory,
            $customerconnectHelper,
            $generic,
            $cache
        );
    }

    public function execute()
    {
        $customerSession = $this->customerSession;
        $customer = $customerSession->getCustomer();

        $commHelper = $this->commHelper;
        $erpAccount = $commHelper->getErpAccountInfo();

        $shippingAddressAllowed = $this->request->getParam('shippingAddressAllowed');
        $billingAddressAllowed  = $this->request->getParam('billingAddressAllowed');
        try {
            $erpAccount->setShippingAddressAllowed($shippingAddressAllowed);
            $erpAccount->setBillingAddressAllowed($billingAddressAllowed);
            $erpAccount->save();
            $this->getResponse()->setBody(
                json_encode(
                    array(
                        //M1 > M2 Translation Begin (Rule p2-4)
                        //'redirect' => Mage::getUrl('customerconnect/account/'),
                        'redirect' => $this->_url->getUrl('customerconnect/account/'),
                        //M1 > M2 Translation End
                        'type' => 'success'
                    )
                )
            );
        } catch (\Exception $ex) {
            $this->logger->debug('--- update of erp account failed---');
            $this->logger->debug($ex->getMessage());
            $this->getResponse()->setBody(
                json_encode(
                    array(
                        //M1 > M2 Translation Begin (Rule p2-4)
                        //'redirect' => Mage::getUrl('customerconnect/account/'),
                        'redirect' => $this->_url->getUrl('customerconnect/account/'),
                        //M1 > M2 Translation End
                        'type' => 'error',
                        'message' => __('Unable to update, please try later')
                    )
                )
            );
        }
    }

}
