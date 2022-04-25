<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Block\Adminhtml\Reports;


class Filter extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Epicor\Reports\Helper\Data
     */
    protected $reportsHelper;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Epicor\Reports\Helper\Data $reportsHelper,
        array $data = []
    )
    {
        $this->reportsHelper = $reportsHelper;
        parent::__construct(
            $context,
            $data
        );

        $this->_blockGroup = 'Epicor_Reports';
        $this->_controller = 'adminhtml_reports';
        $this->_mode = 'filter';
        $helper = $this->reportsHelper;
        $this->_headerText = __('Filter Form');

        $this->setUseAjax(true);
        $this->removeButton('back');
        $this->removeButton('save', 'label', 'Filter');
        $this->addButton('filter', array(
            'label' => __('Create Chart'),
            'class' => 'save'
        ));
        $this->addButton('export', array(
            'label' => __('Export data to CSV'),
            'class' => 'show-hide'
        ));
        $this->addButton('refresh_chart', array(
            'label' => __('Refresh Chart'),
            'class' => 'task'
        ));
        $this->addButton('reprocess', array(
            'label' => __('Process current message log data'),
            'onclick' => "document.location.href = '" . $this->getUrl('*/*/reprocess') . "'"
        ));
    }

    protected function _prepareLayout()
    {
        $this->setChild('store_switcher', $this->getLayout()->createBlock('\Magento\Backend\Block\Store\Switcher')
                ->setUseConfirm(false)
                ->setSwitchUrl($this->getUrl('*/*/*', array('store' => null)))
                ->setTemplate('report/store/switcher.phtml')
        );

//        $this->setChild('refresh_button', $this->getLayout()->createBlock('adminhtml/widget_button')
//                ->setData(array(
//                    'label' => __('Refresh'),
//                    'onclick' => $this->getRefreshButtonCallback(),
//                    'class' => 'task'
//                ))
//        );
        parent::_prepareLayout();
        return $this;
    }

}
