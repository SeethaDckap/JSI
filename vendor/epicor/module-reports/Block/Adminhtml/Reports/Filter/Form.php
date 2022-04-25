<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Block\Adminhtml\Reports\Filter;


class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\Reports\Model\RawdataFactory
     */
    protected $reportsRawdataFactory;

    /**
     * @var \Epicor\Reports\Helper\Data
     */
    protected $reportsHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Reports\Model\RawdataFactory $reportsRawdataFactory,
        \Epicor\Reports\Helper\Data $reportsHelper,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->reportsRawdataFactory = $reportsRawdataFactory;
        $this->reportsHelper = $reportsHelper;
        $this->storeSystemStore = $storeSystemStore;
        $this->_localeResolver = $localeResolver;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Preparing form
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model = $this->reportsRawdataFactory->create();
        /* @var $model Epicor_Reports_Model_Rawdata */
        $dependenceBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');
        /* @var $dependenceBlock Mage_Adminhtml_Block_Widget_Form_Element_Dependence */
        $helper = $this->reportsHelper;
        /* @var $helper Epicor_Reports_Helper_Data */
        $store = $this->storeSystemStore;
        /* @var $store Mage_Adminhtml_Model_System_Store */

        $form = $this->formFactory->create(['data' => [
            'id' => 'filter_form',
            'action' => $this->getUrl('*/*/graph'),
            'method' => 'post',
            'enctype' => 'multipart/form-data']
        ]);
          

        $htmlIdPrefix = 'messaging_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Filter')));

        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$dateFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $dateFormat =  $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        //M1 > M2 Translation End


        $fieldset->addField('store_id', 'select', array(
            'name' => 'store_id',
            'label' => __('Store(s)'),
            'title' => __('Store(s)'),
            'required' => true,
            'values' => $this->_changeKeysStoresArray($store->getStoreValuesForForm(false, true)),
        ));

        $chartTypeInput = $fieldset->addField('chart_type', 'select', array(
            'name' => 'chart_type',
            'options' => $helper->getChartTypes(),
            'label' => __('Chart type'),
            'required' => true,
            //TESTING
            'value' => 'speed'
        ));

        $fieldset->addField('message_status', 'select', array(
            'name' => 'message_status',
            'options' => $helper->getMessageStatus(),
            'label' => __('Message status'),
            'value' => 'combined'
        ));

        $fieldset->addField('message_type', 'multiselect', array(
            'name' => 'message_type',
            'class' => 'required-entry',
            'values' => $helper->getMessageTypes(),
            'label' => __('Message type'),
            //TESTING
            'value' => array('MSQ')
        ));

        $resolutionInput = $fieldset->addField('resolution', 'text', array(
            'name' => 'resolution',
            'class' => 'validate-number required-entry',
            'label' => __('Resolution'),
            'value' => 1
        ));

        $resolutionUnitInput = $fieldset->addField('resolution_unit', 'select', array(
            'name' => 'resolution_unit',
            'options' => $helper->getMinMaxAvgResolutionUnits(),
            'label' => __('Unit of Resolution'),
            'value' => 86400 //$helper->getMinMaxAvgResolutionUnitDefault()
        ));

        $fieldset->addField('from', 'date', array(
            'name' => 'from',
            'date_format' => $dateFormat,
            'time_format' => 'hh:mm:ss',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'label' => __('From'),
            'title' => __('From'),
            'required' => true,
            //'value'     => '2014-10-05 01:16:00',
            'value' => date('Y-m-d', strtotime('-2 weeks')),
            'time' => true
        ));

        $fieldset->addField('to', 'date', array(
            'name' => 'to',
            'date_format' => $dateFormat,
            'time_format' => 'hh:mm:ss',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'label' => __('To'),
            'title' => __('To'),
            'required' => true,
            'value' => date('Y-m-d'),
            #'value'     => '2014-07-17 10:16:15',
            'time' => true
        ));

        $fieldset->addField('cached', 'multiselect', array(
            'name' => 'cached',
            'class' => 'validate-chart-type',
            'values' => $helper->getCachedValues(),
            'label' => __('Cached'),
            'value' => 'none'
        ))->setSize(3);

//        $fieldset->addField('switched', 'radios', array(
//            'name'      => 'switched',
//            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
//            'label'     => __('Switch chart'),
//            'value'     => 0
//        ));

        $dependenceBlock
            ->addFieldMap($chartTypeInput->getHtmlId(), $chartTypeInput->getName())
            ->addFieldMap($resolutionInput->getHtmlId(), $resolutionInput->getName())
            ->addFieldMap($resolutionUnitInput->getHtmlId(), $resolutionUnitInput->getName())
            ->addFieldDependence(
                $resolutionInput->getName(), $chartTypeInput->getName(), $model::REPORT_TYPE_MIN_MAX_AVERAGE
            )
            ->addFieldDependence(
                $resolutionUnitInput->getName(), $chartTypeInput->getName(), $model::REPORT_TYPE_MIN_MAX_AVERAGE
        );
        $this->setChild('form_after', $dependenceBlock);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _changeKeysStoresArray($array)
    {
        foreach ($array as &$item) {
            if ($item['value'] !== 0) {
                if (is_array($item['value'])) {
                    $item['value'] = $this->_changeKeysStoresArray($item['value']);
                } else {
                    $item['value'] = str_replace(html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8'), '', $item['label']);
                }
            }
        }
        return $array;
    }

}
