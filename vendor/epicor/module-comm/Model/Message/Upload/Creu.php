<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * Response CREU - Upload Customer Returns
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Creu extends \Epicor\Comm\Model\Message\Upload
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory
     */
    protected $commResourceCustomerReturnModelCollectionFactory;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory $commResourceCustomerReturnModelCollectionFactory,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commResourceCustomerReturnModelCollectionFactory = $commResourceCustomerReturnModelCollectionFactory;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/creu_mapping/');
        $this->setMessageType('CREU');
        $this->setLicenseType(array('Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('creu_update_return', true, true);
//        $this->setResultsPath('return');

    }

    public function processAction()
    {
        $erpReturnsNumber = $this->getVarienData('erp_returns_number', $this->getRequest()); // not updatable 
//        $webReturnsNumber = $this->getVarienData('web_returns_number', $this->getRequest()); // not updatbale
        $rmaDate = $this->getVarienData('rma_date', $this->getRequest());
        $returnsStatus = $this->getVarienData('returns_status', $this->getRequest());
        $customerReference = $this->getVarienData('customer_reference', $this->getRequest());
        $customerCode = $this->getVarienData('customer_code', $this->getRequest());
        $customerName = $this->getVarienData('customer_name', $this->getRequest());
        $invoiceNumber = $this->getVarienData('credit_invoice_number', $this->getRequest());
        $rmaCaseNumber = $this->getVarienData('rma_case_number', $this->getRequest());
        $rmaContact = $this->getVarienData('rma_contact', $this->getRequest());

        if (!$erpReturnsNumber) {
            throw new \Exception(
            $this->getErrorDescription(self::STATUS_INVALID_RETURNS_NUMBER, $erpReturnsNumber), self::STATUS_INVALID_RETURNS_NUMBER
            );
        } else {
            $currentReturn = $this->commResourceCustomerReturnModelCollectionFactory->create()->addFieldToFilter('erp_returns_number', array('eq' => $erpReturnsNumber))
                ->getFirstItem();
        }


        if ($currentReturn->isObjectNew()) {
            throw new \Exception($this->getErrorDescription(self::STATUS_RETURNS_NUMBER_NOT_ON_FILE, $erpReturnsNumber), self::STATUS_RETURNS_NUMBER_NOT_ON_FILE);
        };

        $subject = 'Web Return: ' . $currentReturn->getId();

        if ($currentReturn->getErpReturnsNumber()) {
            $subject .= "\n" . 'ERP Return: ' . $currentReturn->getErpReturnsNumber();
        }

        $this->setMessageSecondarySubject($subject);

        $helper = $this->customerconnectMessagingHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Messaging */

        $rmaMapped = $helper->getRmaStatusMapping($returnsStatus);

        if (!$rmaMapped->isObjectNew() && $rmaMapped->getIsRmaDeleted()) {
            $currentReturn->delete();
        } else {

            //update fields on returns table
            if ($this->isUpdateable('rma_date_update', $currentReturn)) {
                $currentReturn->setRmaDate($rmaDate);
            }
            if ($this->isUpdateable('returns_status_update', $currentReturn)) {
                $currentReturn->setReturnsStatus($returnsStatus);
            }
            if ($this->isUpdateable('customer_reference_update', $currentReturn)) {
                $currentReturn->setCustomerReference($customerReference);
            }
            if ($this->isUpdateable('customer_code_update', $currentReturn)) {
                $currentReturn->setCustomerCode($customerCode);
            }
            if ($this->isUpdateable('customer_name_update', $currentReturn)) {
                $currentReturn->setCustomerName($customerName);
            }
            if ($this->isUpdateable('credit_invoice_number_update', $currentReturn)) {
                $currentReturn->setCreditInvoiceNumber($invoiceNumber);
            }
            if ($this->isUpdateable('rma_case_number_update', $currentReturn)) {
                $currentReturn->setRmaCaseNumber($rmaCaseNumber);
            }
            if ($this->isUpdateable('rma_contact_update', $currentReturn)) {
                $currentReturn->setRmaContact($rmaContact);
            }

            $currentReturn->save();
        }
    }

}
