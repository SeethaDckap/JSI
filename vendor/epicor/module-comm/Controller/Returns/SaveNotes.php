<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class SaveNotes extends \Epicor\Comm\Controller\Returns
{


    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $customerSession,
            $commCustomerReturnModelFactory,
            $generic,
            $jsonHelper,
            $registry
        );
    }


    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            /* Do action stuff here */
            $errors = array();

            $return = $this->loadReturn();

            if (!$return->isObjectNew()) {
                if ($return->isActionAllowed('Notes') && $this->scopeConfig->getValue('epicor_comm_returns/notes/tab_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $tabLength = $this->scopeConfig->getValue('epicor_comm_returns/notes/tab_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $note = $this->getRequest()->getParam('return-note', '');
                    if ($tabLength && $tabLength < strlen($note)) {
                        $errors[] = $this->__("The notes field exceeds the {$tabLength} characters allowed.");
                        $note = substr($note, 0, $tabLength);
                    }
                    $return->setNoteText($note);
                    $return->save();
                }
            } else {
                $errors[] = __('Failed to find return to add notes to. Please try again.');
            }

            $this->getRequest()->getParam('return-note', false);
            $this->sendStepResponse('notes', $errors);
        }
    }
    
}
