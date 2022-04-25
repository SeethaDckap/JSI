<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Search\Generic\Listing;


/**
 * Generic grid list for use with  messages
 * 
 * @method setMessageBase()
 * @method getMessageBase()
 * @method setCacheDisabled()
 * @method getCacheDisabled()
 * @method setMessageType()
 * @method getMessageType()
 * @method setDataSubset()
 * @method getDataSubset()
 * @method getIdColumn()
 * @method setIdColumn()
 * @method setRowUrlValue()
 * @method getRowUrlValue()
 * @method getCustomData()
 * @method setCustomData()
 * @method getShowAll()
 * @method setShowAll()
 * @method getAdditionalFilters()
 * @method setAdditionalFilters()
 * @method setMaxResults()
 * @method getMaxResults()
 * @method boolean getKeepRowObjectType()
 * @method setKeepRowObjectType(boolean $keepObject)
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const FRONTEND_RESOURCE_EXORT = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_DETAIL = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_REORDER = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_PRINT = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_EMAIL = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    /**
     * @var \Epicor\Common\Model\Message\CollectionFactory
     */
    protected $commonMessageCollectionFactory;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Dealerconnect\Model\Message\Request\Inventory\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
        $this->commonMessageCollectionFactory = $commonMessageCollectionFactory;
        $this->commonHelper = $commonHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        if (!$this->getRowValue()) {
            $this->setRowUrlValue('*/*/edit');
        }

        //M1 > M2 Translation Begin (Rule 57)
        $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/widget/grid/extended.phtml');
        //M1 > M2 Translation End

        // Default setup example
        // $this->setId('id');
        // $this->setDefaultSort('start_datestamp');
        // $this->setDefaultDir('desc');
        // $this->setSaveParametersInSession(true);
        //
        // $this->setCustomColumns(array());
        //
        // $this->setExportTypeCsv(array(
        //     'url' => '*/*/exportCsv',
        //     'text' => 'CSV'
        // ));
        //
        // $this->setExportTypeXml(array(
        //     'url' => '*/*/exportXml',
        //     'text' => 'XML'
        // ));
        //
        // $this->setRowUrlValue('*/*/view');
    }

    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }


    public function getExportTypes()
    {
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_EXORT)) {
            return false;
        }
        return parent::getExportTypes();
    }

    protected function _prepareCollection()
    {
        $collection = $this->commonMessageCollectionFactory->create();

        $collection->setMessageBase($this->getMessageBase());
        $collection->setMessageType($this->getMessageType());
        $collection->setIdColumn($this->getIdColumn());
        $collection->setData($this->getCustomData());
        $collection->setDataSubset($this->getDataSubset());
        $collection->setColumns($this->getCustomColumns());
        $collection->setKeepRowObjectType($this->getKeepRowObjectType() ? true : false);
        $collection->setShowAll($this->getShowAll());
        $collection->setGridId($this->getId());
        $collection->setAdditionalFilters($this->getAdditionalFilters());
        $collection->setMaxResults($this->getMaxResults());
        if ($this->getCacheDisabled()) {
            $collection->setCacheEnabled(false);
        } else {
            $collection->setCacheEnabled(true);
        }

        $this->setCollection($collection);

        parent::_prepareCollection();
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if (!$this->getCacheDisabled()) {
            $cacheTime = $this->getCollection()->getCacheTime();

            $date = $this->commonHelper->getLocalDate($cacheTime);

            $identifier = $this->getMessageType() ?: $this->getId();

            $url = $this->getUrl('*/grid/clear', array('grid' => $identifier, 'location' => $this->frameworkHelperDataHelper->getEncodedUrl()));

            //M1 > M2 Translation Begin (Rule 55)
            //$html = '<p>' . $this->__('Data correct as of %s', $date) . ' <a href="' . $url . '">' . $this->__('Refresh Data') . '</a></p>' . $html;
            $html = '<p>' . __('Data correct as of %1', $date) . ' <a href="' . $url . '">' . __('Refresh Data') . '</a></p>' . $html;
            //M1 > M2 Translation End
        }
        return $html;
    }

    protected function _prepareColumns()
    {
        if (is_array($this->getCustomColumns())) {
            foreach ($this->getCustomColumns() as $columnId => $columnInfo) {
                $add = true;
                if (isset($columnInfo['type']) && $columnInfo['type'] == 'date') {
                    $columnInfo['renderer'] = 'Epicor\Customerconnect\Block\Listing\Renderer\Date';
                }
                
                if($columnInfo['pacattributes'] !="") {
                   $decodeJson = json_decode($columnInfo['datatypejson'],true);
                   $dataType = $decodeJson['datatype'];
                   if($dataType =="combobox" || $dataType =="checkbox") {
                        if($decodeJson['datatype'] =="checkbox") {
                           $columnInfo['options'] =  array(' ' => ' ','Y' => 'Yes', 'N' => 'No');
                        } 
                        $columnInfo['renderer'] = 'Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Renderer\Pac';  
                   } else {
                        $columnInfo['renderer'] = 'Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Renderer\Pacstatic';  
                   }
                }
                if ($columnInfo['index'] == 'bill_of_materials' && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL)) {
                    $columnInfo['column_css_class'] = 'bom-align';
                    $columnInfo['renderer'] = 'Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Renderer\Bom';
                } else if ($columnInfo['index'] == 'bill_of_materials'){
                    $add = false;
                }

                $columnInfo['header'] = __(isset($columnInfo['header']) ? $columnInfo['header'] : '');
                if ($add) {
                    $this->addColumn($columnId, $columnInfo);
                }
              
            }
        }
        
       

        $exportCsv = $this->getExportTypeCsv();
        if ($exportCsv) {
            $this->addExportType(@$exportCsv['url'], __(@$exportCsv['text']));
        }

        $exportXml = $this->getExportTypeXml();
        if ($exportXml) {
            $this->addExportType(@$exportXml['url'], __(@$exportXml['text']));
        }

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl($this->getRowUrlValue(), array('id' => $row->getId()));
    }

    public function isRowUrlAllowed()
    {
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL)) {
            return false;
        }
        return true;
    }

    protected function _setFilterValues($data)
    {
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset($data[$columnId]) && (!empty($data[$columnId]) || strlen($data[$columnId]) > 0) && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);

                $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }
    
    
    public function toOptionArray() {
        return [
            ['value' => 'N', 'label' => __('No')],
            ['value' => 'Y', 'label' => __('Yes')]
        ];
    }    

    /**
     * Retrieve grid
     *
     * @param   string $paramName
     * @param   mixed $default
     * @return  mixed
     */
    public function getParam($paramName, $default = null)
    {
        if ($this->getRequest()->has($paramName)) {
            $param = $this->getRequest()->getParam($paramName);
            return $param;
        }
        return $default;
    }

    public function getVisibleColumns()
    {
        $visibleColumns = 0;
        foreach ($this->getColumns() as $column) {
            if ($column->getType() != 'hidden')
                $visibleColumns++;
        }

        return $visibleColumns;
    }

    public function isReorderAllowed()
    {
        return $this->_isAccessAllowed(static::FRONTEND_RESOURCE_REORDER) &&
            $this->_isAccessAllowed('Epicor_Checkout::checkout_checkout_can_checkout');
    }

}