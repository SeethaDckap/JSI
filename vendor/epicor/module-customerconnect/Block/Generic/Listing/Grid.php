<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Generic\Listing;

use Epicor\AccessRight\Acl\RootResource;
use Epicor\Common\Helper\Data as CommonHelper;
use Epicor\Common\Model\Message\CollectionFactory;
use Epicor\Customerconnect\Model\Skus\CpnuManagement;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url\Helper\Data as FrameworkUrlHelper;

/**
 * Class Grid
 * @package Epicor\Customerconnect\Block\Generic\Listing
 */
class Grid extends Extended
{
    const FRONTEND_RESOURCE_EXORT = RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_DETAIL = RootResource::FRONTEND_RESOURCE;

    /**
     * @var CollectionFactory
     */
    protected $commonMessageCollectionFactory;

    /**
     * @var CommonHelper
     */
    protected $commonHelper;

    /**
     * @var FrameworkUrlHelper
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var CpnuManagement
     */
    private $cpnuManagement;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $commonMessageCollectionFactory
     * @param CommonHelper $commonHelper
     * @param FrameworkUrlHelper $frameworkHelperDataHelper
     * @param array $data
     * @param CpnuManagement|null $cpnuManagement
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $commonMessageCollectionFactory,
        CommonHelper $commonHelper,
        FrameworkUrlHelper $frameworkHelperDataHelper,
        array $data = [],
        CpnuManagement $cpnuManagement = null
    ) {
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

        $this->setTemplate('Epicor_Customerconnect::widget/grid/extended.phtml');
        $this->cpnuManagement = $cpnuManagement ?: ObjectManager::getInstance()->get(CpnuManagement::class);
    }

    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    /**
     * @return false|\Magento\Framework\DataObject[]
     */
    public function getExportTypes()
    {
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_EXORT)) {
            return false;
        }
        return parent::getExportTypes();
    }

    /**
     * @return Grid|void
     */
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

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if (!$this->getCacheDisabled()) {
            $cacheTime = $this->getCollection()->getCacheTime();

            $date = $this->commonHelper->getLocalDate($cacheTime);

            $identifier = $this->getMessageType() ?: $this->getId();

            $url = $this->getUrl('*/grid/clear', array('grid' => $identifier, 'location' => $this->frameworkHelperDataHelper->getEncodedUrl()));

            if ($this->getRequest()->getParam('arfiltergrid') != "true") {
                $html = '<p>' . __('Data correct as of %1', $date) . ' <a href="' . $url . '">' . __('Refresh Data') . '</a></p>' . $html;
            }
        }
        return $html;
    }

    /**
     * @return Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        if (is_array($this->getCustomColumns())) {
            foreach ($this->getCustomColumns() as $columnId => $columnInfo) {
                if (isset($columnInfo['type']) && $columnInfo['type'] == 'date') {
                    $columnInfo['renderer'] = 'Epicor\Customerconnect\Block\Listing\Renderer\Date';
                }

                $columnInfo['header'] = __(isset($columnInfo['header']) ? $columnInfo['header'] : '');
                $this->addColumn($columnId, $columnInfo);
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

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string|void
     */
    public function getRowUrl($row)
    {
        if ($this->isRowUrlAllowed()) {
            return $this->getUrl($this->getRowUrlValue(), array('id' => $row->getId()));
        }
    }

    /**
     * @return bool
     */
    public function isRowUrlAllowed()
    {
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_DETAIL)) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $data
     * @return $this|Grid
     */
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
            return $this->getRequest()->getParam($paramName);
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getVisibleColumns()
    {
        $visibleColumns = 0;
        foreach ($this->getColumns() as $column) {
            if ($column->getType() != 'hidden') {
                $visibleColumns++;
            }
        }

        return $visibleColumns;
    }

    /**
     * @return bool
     */
    public function canAddAccountSku()
    {
        return $this->_isAccessAllowed(CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_ADD);
    }

    /**
     * @return bool
     */
    public function canEditAccountSku()
    {
        return $this->_isAccessAllowed(CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_EDIT);
    }

    /**
     * @return bool
     */
    public function isEditAllowed()
    {
        return $this->cpnuManagement->isEditable();
    }

    /**
     * @return bool
     */
    public function isUpdateAllowed()
    {
        return $this->cpnuManagement->erpUpdateAllow();
    }
}
