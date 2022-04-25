<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Payment;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Payment
     */

    protected $commErpMappingPayment;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Paymentmethods
     */
    protected $paymentMethods;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,    
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\Erp\Mapping\Paymentmethods $paymentMethods,
        \Epicor\Comm\Model\Erp\Mapping\Payment $commErpMappingPayment,
        array $data = [])
    {
        $this->commErpMappingPayment=$commErpMappingPayment;
        $this->paymentMethods=$paymentMethods;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('paymentmappingGrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {

        $collection = $this->commErpMappingPayment->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('magento_code', array(
            'header' => __('Payment Method'),
            'align' => 'left',
            'index' => 'magento_code',
            'width' => '50%',
            'type' => 'options',
            'options' => $this->paymentMethods->getPaymentMethodList(true),
            'option_groups' => $this->paymentMethods->getPaymentMethodList(true, true, true),
        ));


        $this->addColumn('erp_code', array(
            'header' => __('ERP Code'),
            'align' => 'left',
            'index' => 'erp_code',
        ));

        $this->addColumn('payment_collected', array(
            'header' => __('Payment Collected'),
            'align' => 'left',
            'index' => 'payment_collected',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Mapping\Renderer\Paymentcollected'
        ));

        $this->addColumn('gor_trigger', array(
            'header' => __('Order Trigger'),
            'align' => 'left',
            'index' => 'gor_trigger',
        ));

        $this->addColumn('gor_online_prevent_repricing', array(
            'header' => __('Gor-On Prevent Repricing'),
            'align' => 'left',
            'index' => 'gor_online_prevent_repricing',
        ));

        $this->addColumn('gor_offline_prevent_repricing', array(
            'header' => __('Gor-Off Prevent Repricing'),
            'align' => 'left',
            'index' => 'gor_offline_prevent_repricing',
        ));

        $this->addColumn('bsv_online_prevent_repricing', array(
            'header' => __('Bsv-On Prevent Repricing'),
            'align' => 'left',
            'index' => 'bsv_online_prevent_repricing',
        ));

        $this->addColumn('bsv_offline_prevent_repricing', array(
            'header' => __('Bsv-Off Prevent Repricing'),
            'align' => 'left',
            'index' => 'bsv_offline_prevent_repricing',
        ));



        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
