<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Addressdetails extends \Epicor\Customerconnect\Controller\Rfqs
{

    const FRONTEND_RESOURCE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;
    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $registry,
            $customerconnectHelper,
            $request,
            $customerconnectMessageRequestCrqd,
            $generic,
            $commonAccessHelper,
            $customerconnectMessagingHelper,
            $commMessagingHelper,
            $commConfiguratorHelper,
            $commProductHelper,
            $catalogProductFactory,
            $storeManager,
            $commMessageRequestCdmFactory,
            $scopeConfig,
            $commonXmlvarienFactory,
            $urlDecoder,
            $encryptor
        );
    }

    public function execute()
    {

        $addressId = $this->request->getParam('addressid');
        $type = $this->request->getParam('type');

        $customer = $this->customerSession->getCustomer();

        $result = $this->resultLayoutFactory->create();
        if ($addressId) {
            if (strpos($addressId, 'erpaddress_') !== false) {
                $addressId = str_replace('erpaddress_', '', $addressId);

                $erpAddress = $this->commCustomerErpaccountAddressFactory->create()->load($addressId);

                $address = $erpAddress->toCustomerAddress($customer);
            } else {
                $address = $customer->getAddressById($addressId);
            }

            $content = $result->getLayout()->createBlock('Epicor\Customerconnect\Block\Customer\Rfqs\Details\Addressdetails')
                ->setAddressType($type)
                ->setAddressFromCustomerAddress($address)->toHtml();
        } else {
            $addressParam = $this->request->getParam('address-data');
            $addressData = !empty($addressParam) ? (array)json_decode($addressParam) : array();

            $content = $result->getLayout()->createBlock('Epicor\Customerconnect\Block\Customer\Editableaddress')
                ->setAddressType($type)
                ->setFieldnamePrefix($type . '_address[')
                ->setFieldnameSuffix(']')
                ->setShowAddressCode(false)
                ->setAddressFromCustomerAddress($this->dataObjectFactory->create($addressData))->toHtml();
        }

        $this->getResponse()->setBody($content);
    }

}
