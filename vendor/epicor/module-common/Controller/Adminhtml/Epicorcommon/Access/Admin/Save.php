<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Admin;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Admin
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    private $_cacheState;

    private $_cacheManager;


    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Cache\StateInterface $state,
        \Magento\Framework\App\CacheInterface $cache
    )
    {
        $this->_cacheState = $state;
        $this->backendSession = $backendSession;
        $this->eventManager = $eventManager;
        $this->_cacheManager = $cache;
    }
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {

            $this->backendSession->setFormData($data);
            try {

                if (isset($data['element_excluded'])) {
                    $this->saveElements($data);
                    //M1 > M2 Translation Begin (Rule 12)
                    //if (Mage::app()->useCache('access')) {
                    if ($this->_cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Access::TYPE_IDENTIFIER)) {
                        //$cache = Mage::app()->getCacheInstance();
                        $cache = $this->_cacheManager;
                        //M1 > M2 Translation End
                        /* @var $cache Mage_Core_Model_Cache */
                        $cache->clean(array('EXCLUSIONS'));
                    }
                }

                $this->eventManager->dispatch('epicor_common_access_rights_admin_save', array('request' => $this->getRequest()));

                $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->backendSession->addError($e->getMessage());
                $this->_redirect('*/*/');
            }

            return;
        }
        $this->backendSession->addError(__('No data found to save'));
        $this->_redirect('*/*/');
    }

    }
