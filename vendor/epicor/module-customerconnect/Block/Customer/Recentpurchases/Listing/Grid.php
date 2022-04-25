<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Recentpurchases\Listing;
use Magento\Framework\DataObjectFactory as DataObject;
use Epicor\Customerconnect\Helper\ExportFile as ExportFileHelper;

/**
 * Customer Recent Purchase list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search {

    /*
     * constants for access rights
     */
    const FRONTEND_RESOURCE_EXORT    = 'Epicor_Customerconnect::customerconnect_account_recentpurchases_export';

    const FRONTEND_RESOURCE_REORDER  = 'Epicor_Customerconnect::customerconnect_account_recentpurchases_reorder';

    const FRONTEND_RESOURCE_EDIT    = 'Epicor_Customerconnect::customerconnect_account_recentpurchases_edit';

    const FRONTEND_RESOURCE          = 'Epicor_Customerconnect::customerconnect_account_recentpurchases_read';


    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;
    /**
     * @var DataObject
     */
    private $dataObjectFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->localeResolver = $localeResolver;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $configOptionsModelReader, $columnRendererReader, $data
        );
        $this->setFooterPagerVisibility(true);
        $toTime = strtotime("-210 hour");
        $toDate = date("m/d/Y", $toTime);
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$locale = Mage::app()->getLocale()->getLocaleCode();
        $locale = $localeResolver->getLocale();
        //M1 > M2 Translation End
        $this->setId('customerconnect_rph');
        $this->setDefaultSort('last_ordered_date');
        $this->setDefaultDir('desc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('cphs');
        $this->setIdColumn('product_code');
        $this->setDefaultFilter(array('last_ordered_date' => array('from' => $toDate, 'locale' => $locale)));
        $this->dataObjectFactory = $dataObjectFactory;
        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportOrdersCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportOrdersXml'));
        $this->setNoFilterMassactionColumn(true);
        $this->setMassactionBlockName('Epicor\Common\Block\Widget\Grid\Massaction\Extended');
    }

    public function getRowUrl($row) {
        return false;
    }

    /**
     * _prepareMassaction
     * @return $this|\Epicor\Common\Block\Generic\Listing\Search
     */
    protected function _prepareMassaction()
    {
        $preqActive = $this->_scopeConfig->getValue('customerconnect_enabled_messages/PREQ_request/active');

        if ($preqActive) {
            $this->setMassactionIdField('id');
            $this->getMassactionBlock()->setFormFieldName('entityid');
            $maxCount = $this->scopeConfig->getValue('customerconnect_enabled_messages/PREQ_request/max_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 10;

            //Bulk add to cart
            if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_REORDER)) {
                $this->getMassactionBlock()->addItem('addtocart', array(
                    'label' => __('Add to Cart'),
                    'url' => $this->getUrl('customerconnect/recentPurchases/reorder', array('_query' => array('entity' => $this->getEntityType(), 'massaction' => 'y', 'action' => 'R', 'maxCount' => $maxCount)))
                ));
            }
        }


        return $this;
    }

    /**
     * initColumns
     */
    protected function initColumns()
    {
        parent::initColumns();

        $columns = $this->getCustomColumns();

        if (isset($columns['total_qty_ordered'])) {
            $columns['total_qty_ordered']['renderer'] = 'Epicor\Customerconnect\Block\Customer\Recentpurchases\Listing\Renderer\TotalQtyOrdered';
        }


        if ($this->isReorderAllowed() && $this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $columns['reorder'] = array(
                'header' => __('Action'),
                'type' => 'text',
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'action-link-ht',
                'column_css_class' => 'action-link-ht',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Recentpurchases\Listing\Renderer\Reorder',
            );
        }
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);

        $this->setCustomColumns($columnObject->getData());
    }

    /**
     * @return bool
     */
    private function isExportAction()
    {
        return ExportFileHelper::isExportAction(
            $this->getRequest()->getActionName(),
            $this->getRequest()->getModuleName()
        );
    }


}
