<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Account;

class SaveShippingAddress extends \Epicor\Customerconnect\Controller\Account
{

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

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
        \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->customerconnectMessageRequestCuau = $customerconnectMessageRequestCuau;
        $this->resultJsonFactory = $resultJsonFactory;
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
        $helper = $this->customerconnectHelper;
        $data = $this->getRequest()->getPost();

        $error = false;

        if ($data) {

            $form_data = json_decode($data['json_form_data'], true);
            $this->customerconnectHelper->addressValidate($form_data, false);
            $old_form_data = json_decode($form_data['old_data'], true);
            unset($form_data['old_data']);

            // add this otherwise the difference check will always be true and always send a message
            if (!isset($form_data['address_code'])) {
                $form_data['address_code'] = $old_form_data['address_code'];
            }

            if ($old_form_data != $form_data) {

                $message = $this->customerconnectMessageRequestCuau;

                $action = ($old_form_data) ? 'U' : 'A';

                $message->addDeliveryAddress($action, $form_data, $old_form_data);
                $message->setAddressType('delivery');
                if ($action == 'U') {
                    $this->_successMsg = __('Delivery Address updated successfully');
                    $this->_errorMsg = __('Failed to update Delivery Address');
                    $message->setAction('update');
                } else {
                    $this->_successMsg = __('Delivery Address added successfully');
                    $this->_errorMsg = __('Failed to add Delivery Address');
                    $message->setAction('add');
                }
                $resultData = $this->sendUpdate($message);
            } else {
                $this->messageManager->addNoticeMessage(__('No Changes Made to Shipping Address'));
                $error = true;
            }
        } else {
            $error = true;
        }

        if ($error) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //echo json_encode(array('redirect' => Mage::getUrl('customerconnect/account/'), 'type' => 'success'));
            $resultData = array('redirect' => $this->_url->getUrl('customerconnect/account/'), 'type' => 'success');
            //M1 > M2 Translation End
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($resultData);

        return $result;

    }

}
