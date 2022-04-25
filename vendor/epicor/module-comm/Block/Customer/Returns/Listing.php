<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns;


/**
 * Returns creation page, Notes block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Listing extends \Epicor\Comm\Block\Customer\Returns\AbstractBlock
{

    protected $_collection = array();

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory
     */
    protected $commResourceCustomerReturnModelCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory $commResourceCustomerReturnModelCollectionFactory,
        \Epicor\Comm\Model\Context $commModel,
        array $data = [])
    {
        $this->encryptor = $commModel->getEncryptor();
        $this->customerSession = $commModel->getCustomerSession();
        $this->commResourceCustomerReturnModelCollectionFactory = $commResourceCustomerReturnModelCollectionFactory;
        $this->commHelper = $commModel->getCommHelper();
        $registry = $commModel->getRegistry();
        parent::__construct(
            $context,
            $commReturnsHelper,
            $registry,
            $data);
    }
    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Returns List'));
        $customer = $this->customerSession->getCustomer();
        $this->_collection = $this->commResourceCustomerReturnModelCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Return_Collection */
        $this->_collection->filterByCustomer($customer);
    }

    public function getReturns()
    {
        return $this->_collection;
    }

    public function getViewUrl($return)
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        $quoteDetails = array(
            'id' => $return->getId()
        );

        $requested = $helper->getUrlEncoder()->encode($this->encryptor->encrypt(serialize($quoteDetails)));

        return $this->getUrl('epicor_comm/returns/view', array('return' => $requested));
    }

}
