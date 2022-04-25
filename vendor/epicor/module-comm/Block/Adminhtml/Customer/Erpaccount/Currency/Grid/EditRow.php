<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Currency\Grid;

class EditRow extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'row_id';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_controller = 'adminhtml_customer_erpaccount_currency_grid';
        parent::_construct();
        if ($this->_isAllowedAction('Epicor_Comm::add_row')) {
            $this->buttonList->update('save', 'label', __('Save'));

            $data = array(
                'label' => 'Back to',
                'onclick' => 'setLocation(\'' . $this->getFormBackUrl() . '\')',
                'class' => 'back'
            );
            $this->buttonList->remove('back');
            $this->buttonList->add('my_back', $data);
        } else {
            $this->buttonList->remove('save');
        }
        $this->buttonList->remove('reset');
    }

    /**
     * Retrieve text for header element depending on loaded image.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Edit currency');
    }

    public function getFormBackUrl()
    {
        $erpAccount = $this->getRequest()->getParam('erpaccount');
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/edit', ['id' => $erpAccount]);
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get form action URL.
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }

        return $this->getUrl('*/*/save');
    }
}
