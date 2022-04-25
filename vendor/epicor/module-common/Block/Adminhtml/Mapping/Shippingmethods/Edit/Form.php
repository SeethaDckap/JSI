<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping\Shippingmethods\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Epicor\Comm\Model\Erp\Mapping\ShippingFactory;
use Magento\Framework\Data\FormFactory;
use Epicor\Common\Block\Adminhtml\Mapping\Shippingmethods\Edit\ModalBox;

class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingFactory
     */
    protected $commErpMappingShippingFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * Form constructor.
     *
     * @param Context         $context
     * @param Registry        $registry
     * @param ShippingFactory $commErpMappingShippingFactory
     * @param FormFactory     $formFactory
     * @param array           $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ShippingFactory $commErpMappingShippingFactory,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->commErpMappingShippingFactory = $commErpMappingShippingFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare Form.
     *
     * @return Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        if ($this->_session->getShippingmethodsMappingData()) {
            $data = $this->_session->getShippingmethodsMappingData();
            $this->_session->getShippingmethodsMappingData(null);
        } elseif ($this->registry->registry('shippingmethods_mapping_data')) {
            $data = $this->registry->registry('shippingmethods_mapping_data')
                ->getData();
        } else {
            $data = array();
        }

        $form = $this->formFactory->create(
            [
                'data' => [
                    'id'      => 'edit_form',
                    'action'  => $this->getUrl(
                        '*/*/save',
                        array('id' => $this->getRequest()->getParam('id'))
                    ),
                    'method'  => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        $fieldset = $form->addFieldset('mapping_form', array(
            'legend' => __('Mapping Information'),
        ));

        $fieldset->addField('shipping_method_code', 'select', array(
            'label'    => __('Shipping Method'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'shipping_method',
            'values'   => $this->commErpMappingShippingFactory->create()
                ->toOptionArray(),
            'note'     => __('Required Shipping Method'),
        ));

        $fieldset->addField('erp_code', 'text', array(
            'label'    => __('Code Value'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'erp_code',
        ));

        $fieldset->addField('tracking_url', 'text', array(
                'label'              => __('Tracking Url'),
                'required'           => false,
                'name'               => 'tracking_url',
                'class'              => 'validate-tracking-url validate-tracking-url-tnum',
                'note'               => __('e.g. http://yourdomain.com/{{TNUM}} - URL should contain this value {{TNUM}}'),
                'after_element_html' => '<a href="#"  id="test_track_url">Test Tracking Url</a>',
            )
        );

        $data = $this->includeStoreIdElement($data);
        $form->setValues($data);

        return parent::_prepareForm();
    }

    /**
     * @param string $html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);

        return $html.$this->getLayout()->createBlock(
            \Magento\Backend\Block\Template::class,
            $this->getNameInLayout().'_modal_box'
            )->setOrder($this->getOrder())
                ->setTemplate('Epicor_Common::epicor_common/mapping/shipping_method/modalbox.phtml')
                ->toHtml();
    }

}
