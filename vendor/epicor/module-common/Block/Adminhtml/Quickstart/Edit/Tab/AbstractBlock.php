<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Quickstart\Edit\Tab;


class AbstractBlock extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\Common\Helper\Quickstart
     */
    protected $commonQuickstartHelper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Common\Helper\Quickstart $commonQuickstartHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->commonQuickstartHelper = $commonQuickstartHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {
        $form = $this->formFactory->create();

        $helper_quickstart = $this->commonQuickstartHelper;
        $form = $helper_quickstart->_buildForm($form, $this->getKeysToRender(), $this);

        $this->formExtras($form);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getKeysToRender()
    {
        return array();
    }

    protected function formExtras(\Magento\Framework\Data\Form $form)
    {
        return $form;
    }

}
