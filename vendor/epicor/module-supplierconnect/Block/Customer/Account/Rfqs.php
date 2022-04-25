<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Account;


class Rfqs extends \Epicor\Supplierconnect\Block\Customer\Info
{

    protected $_linkTo = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    protected $urlEncoder;


    protected $scopeConfig;

    protected $enableRfqs;

    protected $dashboardInformation;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->registry = $registry;
        $this->backendHelper = $backendHelper;
        $this->_localeResolver = $localeResolver;
        $this->urlEncoder = $urlEncoder;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $supplierconnectHelper,
            $registry,
            $localeResolver,
            $backendHelper,
            $urlEncoder,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->dashboardInformation = $this->getDashboardInformation();
        if(empty($this->dashboardInformation)) {
            $this->enableRfqs = true;
        } else {
            $this->enableRfqs = (isset($this->dashboardInformation['enable_rfqs_supplier'])) ? true: false;
        }
        if (($this->registry->registry('supplier_connect_account_details')) &&($this->enableRfqs)) {
            $this->setTitle(__('RFQs'));
            $this->setTemplate('supplierconnect/customer/account/supplierinfo.phtml');
            $helper = $this->supplierconnectHelper;
            $locale = $this->_localeResolver->getLocale();
            $url = $this->getUrl('supplierconnect/rfq/index') . 'filter/';
            $dayToday = date('l');
            $today = date('m/d/Y');


            if ($dayToday == 'Monday') {
                $lastMonday = date('m/d/Y');
            } else {
                $lastMonday = date('m/d/Y', strtotime('last monday'));
            }

            $tomorrow = date('m/d/Y', strtotime("tomorrow"));
            $yesterday = date('m/d/Y', strtotime("yesterday"));

            if ($dayToday == 'Sunday') {
                $nextSunday = date('m/d/Y');
            } else {
                $nextSunday = date('m/d/Y', strtotime('next sunday'));
            }

            $this->_linkTo = array(
                'Today :' => array('link' => $url, 'filter' => "status=O&response=Waiting&due_date[locale]={$locale}&due_date[from]={$today}&due_date[to]={$today}"),
                'This Week :' => array('link' => $url, 'filter' => "status=O&response=Waiting&due_date[locale]={$locale}&due_date[from]={$lastMonday}&due_date[to]={$nextSunday}"),
                'Future :' => array('link' => $url, 'filter' => "status=O&response=Waiting&due_date[locale]={$locale}&due_date[from]={$tomorrow}"),
                'Open :' => array('link' => $url, 'filter' => "status=O&due_date[locale]={$locale}"),
                'Overdue :' => array('link' => $url, 'filter' => "status=O&response=Waiting&due_date[locale]={$locale}&due_date[to]={$yesterday}"),
            );

            /* @var $helper Epicor_Supplierconnect_Helper_Data */
            foreach ($this->_linkTo as $key => $value) {
                $this->_linkTo[$key]['link'] = $value['link'] .$this->urlEncoder->encode($value['filter']) . '/';
                $this->_linkTo[$key]['active'] = true;
            }
            $details = $this->registry->registry('supplier_connect_account_details');
            $rfq = $details->getRfqs();



            $this->_infoData = array(
                'Today :' => $rfq->getDueToday(),
                'This Week :' => $rfq->getDueWeek(),
                'Future :' => $rfq->getDueFuture(),
                'Open :' => $rfq->getOpen(),
                'Overdue :' => $rfq->getOverDue()
            );
            $this->setColumnCount(5);
            $this->setOnLeft(true);
        }
    }

    public function getDashboardInformation() {
        return $this->supplierconnectHelper->getDashboardInformation();
    }

    /**
     * @return array
     */
    public function getLinkTo()
    {
        return $this->_linkTo;
    }

    public function fixArrayKey(&$arr)
    {
        $arr = array_combine(
            array_map(
                function ($str) {
                    return str_replace(" ", "_", $str);
                },
                array_keys($arr)
            ),
            array_values($arr)
        );

        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                fixArrayKey($arr[$key]);
            }
        }
    }


    public function getRfqsFilter() {
        if(empty($this->dashboardInformation)) {
            $filterVals  ='Today,ThisWeek,Future,Open,Overdue';
        } else {
            $filterVals =  $this->dashboardInformation['rfqs_filter'];
        }
        return $filterVals;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(
            'Epicor_Supplier::supplier_rfqs_read'
        )) {
            return '';
        }

        return parent::_toHtml();
    }

}