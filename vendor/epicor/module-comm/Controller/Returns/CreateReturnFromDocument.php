<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url\DecoderInterface;

class CreateReturnFromDocument extends \Epicor\Comm\Controller\Returns
{

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        DecoderInterface $decoder = null
    ) {
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $customerSession,
            $commCustomerReturnModelFactory,
            $generic,
            $jsonHelper,
            $registry
        );
        $this->decoder = $decoder ?: ObjectManager::getInstance()->get(DecoderInterface::class);
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    public function execute()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        if ($helper->isReturnsEnabled()) {

            $type = $this->getRequest()->getParam('type', false);
            $data = $this->getRequest()->getParam('data', false);
            $returnUrl = $this->getRequest()->getParam('return', false);

            // create return object
            $return = $helper->createReturnFromDocument($type, $data);

            if ($return->getId()) {
                $this->_redirect('*/*/index', array('return' => $helper->encodeReturn($return->getId())));
            } else {
                $location = $this->decoder->decode($this->decoder->decode($returnUrl));
                if ($this->customerSession->isLoggedIn()) {
                    $this->resultRedirectFactory->create()->setUrl($location);
                } else {
                    $this->_redirect('sales/guest/form');
                }
            }
        }
    }
}
