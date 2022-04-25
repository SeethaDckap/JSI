<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer;


use Epicor\Comm\Model\Customer\ReturnModel\NewFileAttachments;

class Returns extends \Magento\Framework\View\Element\Template
{

    private $_stepData = array(
        'login' => array(
            'label' => 'Login',
            'block' => '\Epicor\Comm\Block\Customer\Returns\Login',
            'remove_section' => 'attachments'
        ),
        'return' => array(
            'label' => 'Return',
            'block' => '\Epicor\Comm\Block\Customer\Returns\ReturnBlock'
        ),
        'products' => array(
            'label' => 'Products to Return',
            'block' => '\Epicor\Comm\Block\Customer\Returns\Products'
        ),
        'attachments' => array(
            'label' => 'Additional Attachments',
            'block' => '\Epicor\Comm\Block\Customer\Returns\Attachments'
        ),
    );

    /**
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /*
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var NewFileAttachments
     */
    private $newFileAttachments;

    public function __construct(
        NewFileAttachments $newFileAttachments,
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->commReturnsHelper = $commReturnsHelper;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
        $this->newFileAttachments = $newFileAttachments;
    }


    public function _construct()
    {
        if ($this->scopeConfig->getValue('epicor_comm_returns/notes/tab_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->_stepData['notes'] = array(
                'label' => 'Notes / Comments',
                'block' => 'Epicor\Comm\Block\Customer\Returns\Notes'
            );
        }
        $this->_stepData['review'] = array(
            'label' => 'Review',
            'block' => 'Epicor\Comm\Block\Customer\Returns\Review'
        );

        parent::_construct();
        $this->setTitle(__('Returns'));
        $this->setTemplate('epicor_comm/customer/returns/index.phtml');

        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $type = $helper->getReturnUserType();

        $enabled = true;

        if (empty($type)) {
            $guestEnabled = $helper->checkConfigFlag('return_attachments', 'guests');
            $b2cEnabled = $helper->checkConfigFlag('return_attachments', 'b2c');
            $b2bEnabled = $helper->checkConfigFlag('return_attachments', 'b2b');

            if (!$guestEnabled && !$b2cEnabled && !$b2bEnabled) {
                unset($this->_stepData['attachments']);
            }
        } else {
            $enabled = $helper->checkConfigFlag('return_attachments');
        }

        if (!$enabled) {
            unset($this->_stepData['attachments']);
        }
    }

    /**
     * Get active step
     *
     * @return string
     */
    public function getActiveStep()
    {
        return $this->isCustomerLoggedIn() ? 'return' : 'login';
    }

    public function getMaxAttachmentFileSize()
    {
        return $this->newFileAttachments->getMaxFileNameLength();
    }

    public function isCustomerLoggedIn()
    {
        $resu =false;
        if($this->customerSession->isLoggedIn()) {
            $resu=true;
        }elseif($this->customerSession->getReturnGuestName() && $this->customerSession->getReturnGuestEmail()){
            $resu=true;
        }
        return $resu;
    }

    /**
     * Get 'one step checkout' step data
     *
     * @return array
     */
    public function getSteps()
    {
        if ($this->isCustomerLoggedIn()) {
            unset($this->_stepData['login']);
        }

        return $this->_stepData;
    }

}
