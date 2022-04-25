<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Generic\Listing;

use Epicor\Common\Block\Generic\Listing\ColumnRendererReader;
use Epicor\Common\Helper\Data as CommonHelper;
use Epicor\Common\Model\GridConfigOptionsModelReader;
use Epicor\Common\Model\Message\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Url\Helper\Data as FrameworkurlHelper;

/**
 * Class Search
 * @package Epicor\Customerconnect\Block\Generic\Listing
 */
class Search extends Grid
{
    /**
     * @var string
     */
    protected $_configLocation = 'grid_config';

    /**
     * @var bool
     */
    protected $_footerPagerVisibility = false;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var GridConfigOptionsModelReader
     */
    protected $configOptionsModelReader;

    /**
     * @var ColumnRendererReader
     */
    protected $columnRendererReader;

    /**
     * Search constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $commonMessageCollectionFactory
     * @param CommonHelper $commonHelper
     * @param FrameworkurlHelper $frameworkHelperDataHelper
     * @param GridConfigOptionsModelReader $configOptionsModelReader
     * @param ColumnRendererReader $columnRendererReader
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $commonMessageCollectionFactory,
        CommonHelper $commonHelper,
        FrameworkurlHelper $frameworkHelperDataHelper,
        GridConfigOptionsModelReader $configOptionsModelReader,
        ColumnRendererReader $columnRendererReader,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->configOptionsModelReader = $configOptionsModelReader;
        $this->columnRendererReader = $columnRendererReader;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setSaveParametersInSession(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
    }

    /**
     * Columns initialization
     */
    protected function initColumns()
    {
        $columnConfig = unserialize($this->scopeConfig->getValue($this->getMessageBase() . '_enabled_messages/' . strtoupper($this->getMessageType()) . '_request/' . $this->_configLocation, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        $columns = array();

        foreach ($columnConfig as $column) {
            if ($column['filter_by'] == 'none') {
                $column['filter'] = false;
            } else {
                unset($column['filter']);
            }

            if ($column['visible'] && !$column['showfilter']) {
                $column['filter'] = false;
            }

            if (strpos($column['index'], '>') !== false) {
                $trimFields = preg_replace('/\s+/', '', $column['index']);
                $q = implode('>', array_map('ucfirst', explode('>', $trimFields)));
                $goodVals = str_replace('>', '', $q);
                $decamelize = $this->decamelize($goodVals);
                $column['index'] = $decamelize;
            }

            if ((preg_match('/[A-Z]/', $column['index'])) && strpos($column['index'], '>')) {
                $decamelize = $this->decamelize($column['index']);
                $column['index'] = $decamelize;
            }

            if ($column['type'] == 'options' && !empty($column['options'])) {
                $column['options'] = $this->configOptionsModelReader->getModel($column['options'])->toGridArray();
            } else if (isset($column['options'])) {
                unset($column['options']);
            }

            if ($column['type'] == 'number') {
                $column['align'] = 'right';
            }
            if ($column['type'] == 'date' || $column['type'] == 'datetime') {
                $column['format'] = \DateTime::ATOM;
            }

            if (!$column['visible'] && $column['showfilter']) {
                $column['column_css_class'] = 'no-display';
                $column['header_css_class'] = 'no-display';
            }

            if (@$column['renderer']) {
                $column['renderer'] = $this->columnRendererReader->getRenderer($column['renderer']);
            }

            if (!$column['visible'] && !$column['showfilter']) {
                unset($column);
            }  else {
                if (isset($column['index'])) {
                    $columns[$column['index']] = $column;
                }
            }

        }
        $this->setCustomColumns($columns);
    }

    /**
     * @param $string
     * @return string
     */
    public function decamelize($string)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

    /**
     * Set visibility of pager in footer section
     *
     * @param boolean $visible
     */
    public function setFooterPagerVisibility($visible = false)
    {
        $this->_footerPagerVisibility = $visible;
    }

    /**
     * To get the protected value in the template
     * for the footer pagination visibility
     * 
     * @return boolean
     */
    public function getFooterPagerVisibility()
    {
        return $this->_footerPagerVisibility;
    }

    /**
     * @return $this
     */
    public function initMassaction()
    {
        $preqActive = $this->_scopeConfig->getValue('customerconnect_enabled_messages/PREQ_request/active');

        if ($preqActive) {
            $this->setMassactionIdField('id');
            $this->getMassactionBlock()->setFormFieldName('entityid');
            $maxCount = $this->scopeConfig->getValue('customerconnect_enabled_messages/PREQ_request/max_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 10;

            //Bulk email action
            if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_EMAIL)) {
                $this->getMassactionBlock()->addItem('email', array(
                    'label' => __('Send Email'),
                    'url' => $this->getUrl('customerconnect/massactions/massEmail', array('_query' => array('entity' => $this->getEntityType(), 'action' => 'E', 'maxCount' => $maxCount)))
                ));
            }
        }

        return $this;
    }
}
