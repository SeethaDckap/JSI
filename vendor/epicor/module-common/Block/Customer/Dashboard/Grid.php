<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Customer\Dashboard;

use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;
/**
 * Customer Quotes list Grid config
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    const FRONTEND_RESOURCE_DETAIL = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    protected $hidePrice;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $id = '';

    protected $messageBase = '';

    protected $messageType = '';

    protected $idColumn = '';

    protected $entityType = '';

    protected $dashboardSection = 'all';

    protected $massAction = false;

    protected $_defaultDateFilter = 'created_at';

    protected $_statusFilter = [];

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        HidePrice $hidePrice,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->hidePrice = $hidePrice;
        $this->eventManager = $context->getEventManager();
        $this->registry = $registry;
        $locale = $localeResolver->getLocale();
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $data
        );

        $this->setId($this->id);
        $this->setMessageBase($this->messageBase);
        $this->setMessageType($this->messageType);
        $this->setIdColumn($this->idColumn);
        $this->setEntityType($this->entityType);
        $this->setNoFilterMassactionColumn(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        if ($this->massAction) {
            $this->setMassactionBlockName('Epicor\Common\Block\Widget\Grid\Massaction\Extended');
        }

        $dasboardInformation = $this->getDashboardConfiguration();
        $section = $this->dashboardSection;
        $dateRange = (isset($dasboardInformation[$section]['date_range'])) ? $dasboardInformation[$section]['date_range'] : \Epicor\Common\Model\Managedashboard::DATE_RANGE;
        $rfqsCount = (isset($dasboardInformation[$section]['grid_count'])) ? $dasboardInformation[$section]['grid_count']: \Epicor\Common\Model\Managedashboard::GRID_COUNT;

        $today = date('m/d/Y');
        $filters = [];
        switch (true) {
            case ($dateRange == "30d"):
                $thirtyDays = date('m/d/Y', strtotime('today - 29 days'));
                $filters[$this->_defaultDateFilter] = [
                    'from' => $thirtyDays,
                    'to' => $today,
                    'locale' => $locale
                ];
                break;
            case ($dateRange == "3m"):
                $lastThreeMonths = date('m/d/Y', strtotime('-3 months'));
                $filters[$this->_defaultDateFilter] = [
                    'from' => $lastThreeMonths,
                    'to' => $today,
                    'locale' => $locale
                ];
                break;
        }
        if (!empty($this->_statusFilter)
        && isset($this->_statusFilter['status'])
        && isset($this->_statusFilter['value'])) {
            $filters[$this->_statusFilter['status']] = $this->_statusFilter['value'];
        }
        $this->setDefaultFilter($filters);
        $this->setCacheDisabled(true);
        $this->setMaxResults($rfqsCount);

        $this->initColumns();
    }

    protected function _prepareMassaction()
    {
        if ($this->massAction) {
            return $this->initMassaction();
        }
        return $this;
    }

    public function getDashboardConfiguration()
    {
        return $this->registry->registry('dashboard_configuration');
    }
}
