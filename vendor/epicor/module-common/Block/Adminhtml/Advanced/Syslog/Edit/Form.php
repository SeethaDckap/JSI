<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Syslog\Edit;

/**
 * Sites edit form
 *
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->storeSystemStore = $storeSystemStore;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }

     /**
      * Init form
      *
      * @return void
      */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('syslog_form');
        $this->setTitle(__('System Log'));
    }

    protected function _prepareForm()
    {
        $site = $this->_coreRegistry->registry('current_site');
        $form = $this->_formFactory->create(['data' => [
                'id' => 'edit_form'
        ]]);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
