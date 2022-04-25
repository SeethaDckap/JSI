<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Returns;

class Details extends \Epicor\Customerconnect\Controller\Returns
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_returns_details';

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;
    
     /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlDecoder;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->commReturnsHelper = $commReturnsHelper;
        $this->registry = $registry;
        $this->request = $request;
        $this->generic = $generic;
        $this->urlDecoder = $urlDecoder;
        $this->_encryptor  = $encryptor;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commReturnsHelper,
            $generic
        );
    }
    

    /**
     * Details action 
     */
    public function execute()
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $success = false;

        $this->registry->register('review_display', true);
        $this->registry->register('details_display', true);

        $returnDetails = unserialize($this->_encryptor->decrypt($this->urlDecoder->decode($this->request->getParam('return'))));

        if (isset($returnDetails['return_url'])) {
            $this->registry->register('return_url', $returnDetails['return']);
            unset($returnDetails['return_url']);
        }

        $erpAccountNumber = $helper->getErpAccountNumber();

        if (
            count($returnDetails) == 2 && $returnDetails['erp_account'] == $erpAccountNumber && !empty($returnDetails['erp_returns_number'])
        ) {

            $return = $helper->loadErpReturn($returnDetails['erp_returns_number']);
            /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

            if (!empty($return)) {
                if ($return->canBeAccessedByCustomer()) {
                    $this->registry->register('return_model', $return);
                    $success = true;
                }
            } else {
                $this->messageManager->addErrorMessage(__('Failed to retrieve RFQ Details'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('ERROR - Return details not available'));
        }

        if ($success) {
            return $this->resultPageFactory->create(); 
        } else {
            session_write_close();
            $this->_redirect('*/*/index');
        }
    }

    }
