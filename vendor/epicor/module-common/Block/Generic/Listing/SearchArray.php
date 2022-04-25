<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Generic\Listing;


/**
 * Generic grid list for use with  messages
 * 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class SearchArray extends \Epicor\Common\Block\Generic\Listing\GridArray
{

    protected $_configLocation = 'grid_config';
    protected $_footerPagerVisibility = false;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Model\GridConfigOptionsModelReader
     */
    protected $configOptionsModelReader;

    /**
     * @var ColumnRendererReader
     */
    protected $columnRendererReader;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionArrayFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        array $data = []
    )
    {
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

    protected function initColumns()
    {

        $columnConfig = unserialize(
            $this->scopeConfig->getValue(
                $this->getMessageBase() . '_enabled_messages/' .
                strtoupper($this->getMessageType()) . '_request/' . $this->_configLocation,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        );

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

            if((preg_match('/[A-Z]/', $column['index'])) && (strpos($column['index'], '>') == false)){
                $decamelize = $this->decamelize($column['index']);
                $column['index'] = $decamelize;
            }

            if ($column['type'] == 'options' && !empty($column['options'])) {
                $column['options'] = $this->configOptionsModelReader->getModel($column['options'])->toGridArray();
            } else if (isset($column['options'])) {
                unset($column['options']);
            }

            if (!$column['visible'] && $column['showfilter']) {
                $column['column_css_class'] = 'no-display';
                $column['header_css_class'] = 'no-display';
            }

            if ($column['type'] == 'number') {
                $column['align'] = 'right';
            }
            if ($column['type'] == 'date' || $column['type'] == 'datetime') {
                $column['format'] = \DateTime::ATOM;
            }

            if (@$column['renderer']) {
                $column['renderer'] = $this->columnRendererReader->getRenderer($column['renderer']);
            }

            if (!$column['visible'] && !$column['showfilter']) {
                unset($column);
            }  else {
                if(isset($column['index'])) {
                    $columns[$column['index']] = $column;
                }
            }
        }
        $this->setCustomColumns($columns);
    }

    public function decamelize($string) {
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
}
