<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Dashboard;


class Claimsection extends \Epicor\AccessRight\Block\Template
{

    protected $dashboardSection = "dealer_dashboard_claimsection";

    protected $_infoData = [];

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Dealerconnect\Model\Claimstatus
     */
    protected $_claimStatus;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Epicor\Dealerconnect\Helper\Messaging
     */
    protected $_helper;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Claimstatus
     */
    protected $_claimStatusMapping;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $_dealerHelper;

    protected $_updatedAt = '';

    protected $_rigthSection = [
        'Request',
        'Overdue',
        'Today',
        'Future',
    ];

    /**
     * Claimsection constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Dealerconnect\Model\Claimstatus $claimstatus
     * @param \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Epicor\Dealerconnect\Helper\Messaging $helper
     * @param \Epicor\Dealerconnect\Helper\Data $dealerHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Model\Claimstatus $claimstatus,
        \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Dealerconnect\Helper\Messaging $helper,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->_claimStatus =  $claimstatus;
        $this->_claimStatusMapping = $claimStatusMapping;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->_helper = $helper;
        $this->_dealerHelper = $dealerHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        if ($this->isSectionEnabled()) {
            $this->setTitle(__('Claims'));
            $this->setClaimStatusData();
            $this->setTemplate('dealerconnect/customer/dashboard/claimsection.phtml');
        }
    }

    public function getDashboardConfiguration()
    {
        $dashboardConfiguration = $this->registry->registry('dashboard_configuration');
        return isset($dashboardConfiguration['summary']) ? $dashboardConfiguration['summary'] : [];
    }

    public function isSectionEnabled()
    {
        $dashboardConfiguration = $this->getDashboardConfiguration();
        if (isset($dashboardConfiguration[$this->dashboardSection])
            && isset($dashboardConfiguration[$this->dashboardSection]['status'])
            && $dashboardConfiguration[$this->dashboardSection]['status'] == 1
            && isset($dashboardConfiguration[$this->dashboardSection]['allowed'])
            && $dashboardConfiguration[$this->dashboardSection]['allowed'] == 1
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getInfoData()
    {
        ksort($this->_infoData);
        return $this->_infoData;
    }

    /**
     * Set Claims Section Data
     */
    public function setClaimStatusData()
    {
        $statuses = [];
        $configuredStatuses = $this->getConfiguredStatuses();
        $mappingStatuses = $this->getClaimsStatusMappings();
        if (!empty($configuredStatuses)&& !empty($mappingStatuses)) {
            $statuses = array_keys(array_intersect($mappingStatuses, $configuredStatuses));
        }
        $claims = $this->_claimStatus->getClaimsStatuses();
        if (!empty($claims)) {
            $claims = $this->sanitizeClaimData($claims, $mappingStatuses, $configuredStatuses);
            foreach ($claims as $claim) {
                $_statusCode = $claim['status_code'];
                if (isset($mappingStatuses[$_statusCode])
                    && !in_array($mappingStatuses[$_statusCode], $configuredStatuses)
                ) {
                    continue;
                }
                if ($this->_updatedAt == '') {
                    $this->_updatedAt =  isset($claim['updated_at']) ? $claim['updated_at'] : '';
                }
                $this->setInfoData($claim, $mappingStatuses);
            }
        }
    }

    /**
     * @param $claim
     * @param $mappingStatuses
     * @return void
     */
    public function setInfoData($claim, $mappingStatuses)
    {
        $claimStatusCode = $claim['status_code'];
        $claimStatus = isset($mappingStatuses[$claimStatusCode]) ? ucfirst($mappingStatuses[$claimStatusCode]) : $claimStatusCode;
        $extraInfo = json_decode($claim['extra_info'], true);
        $claimUrl = isset($extraInfo['claims']) ? $this->getHotLinks($extraInfo['claims']) : '';
        $data = [
            'label' => $claimStatus,
            'count' => $claim['count'],
            'cases' => $claimUrl
        ];
        if (in_array($claimStatus, $this->_rigthSection)) {
            $this->_infoData[2][$claimStatus] = $data;
        } else {
            $this->_infoData[1][$claimStatus] = $data;
        }
        return;
    }

    /**
     * @param $cases
     * @return string
     */
    public function getHotLinks($cases)
    {
        $request = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($cases)));
        return $this->getUrl('dealerconnect/claims/index', ['cases' => $request]);
    }

    /**
     * @return bool
     */
    public function infoDataExists()
    {
        return !empty($this->_infoData);
    }

    /**
     * @return string
     */
    public function getUpdatedDate()
    {
        return $this->_helper->getLocalDate($this->_updatedAt, \IntlDateFormatter::SHORT, true);
    }

    /**
     * @return array
     */
    protected function getConfiguredStatuses()
    {
        $_statuses = [];
        $dashboardConfiguration = $this->getDashboardConfiguration();
        if (isset($dashboardConfiguration[$this->dashboardSection])
            && isset($dashboardConfiguration[$this->dashboardSection]['filters'])
        ) {
            $_filters = $dashboardConfiguration[$this->dashboardSection]['filters'];
            $_statuses = isset($_filters['statuses']) ? $_filters['statuses'] : [];
        }
        return $_statuses;
    }

    /**
     * @return array|false
     */
    public function getClaimsStatusMappings()
    {
        $mappingStatuses = [];
        $claimStatuses = $this->_claimStatusMapping->getAllStatus();
        if (!empty($claimStatuses)) {
            $_erpCodes = array_column($claimStatuses, 'erp_code');
            $_claimMappingStatuses = array_column($claimStatuses, 'claim_status');
            $mappingStatuses = array_combine($_erpCodes, $_claimMappingStatuses);
            if (in_array('request', $mappingStatuses)) {
                $mappingStatuses['Overdue'] = 'Overdue';
                $mappingStatuses['Today'] = 'Today';
                $mappingStatuses['Future'] = 'Future';
            }
        }
        return $mappingStatuses;
    }

    /**
     * @param $claims
     * @param $configuredStatuses
     * @return array
     */
    protected function sanitizeClaimData($claims, $mappingStatuses, $configuredStatuses)
    {
        $_statuses = [];
        $statuses = array_column($claims,'status_code');
        foreach ($statuses as $_status) {
            if (isset($mappingStatuses[$_status])) {
                $_statuses[] = $mappingStatuses[$_status];
            }
        }
        $missingStatuses = array_diff($configuredStatuses, $_statuses);
        if (!empty($missingStatuses)) {
            foreach ($missingStatuses as $status) {
                if (!isset($mappingStatuses[$status])) {
                    continue;
                }
                $claims[] = [
                    'status_code' => ucfirst($status),
                    'count' => 0,
                    'extra_info' => json_encode(['claims' => []])
                ];
            }
        }
        $closedErpCode = $this->_claimStatusMapping
                                ->getClaimStatus(['closed'])
                                ->getFirstItem()
                                ->getData('erp_code');
        if (!is_null($closedErpCode)) {
            $erpKey = array_search($closedErpCode, array_column($claims, 'status_code'));
            $eccKey = array_search('Closed', array_column($claims, 'status_code'));
            if ($eccKey !== false) {
                $claims[$erpKey]['count'] += $claims[$eccKey]['count'];
                $erpExtraInfo = json_decode($claims[$erpKey]['extra_info'], true);
                $eccExtraInfo = json_decode($claims[$eccKey]['extra_info'], true);
                switch(true) {
                    case (isset($erpExtraInfo['claims'])
                        && isset($eccExtraInfo['claims'])
                    ):
                        $udClaims = $erpExtraInfo['claims'];
                        $coreClaims = $eccExtraInfo['claims'];
                        $erpExtraInfo['claims'] = array_merge(
                            array_intersect($udClaims, $coreClaims),
                            array_diff($udClaims, $coreClaims),
                            array_diff($coreClaims, $udClaims)
                        );
                        break;
                    case (!isset($erpExtraInfo['claims'])
                        && isset($eccExtraInfo['claims'])
                    ):
                        $erpExtraInfo['claims'] = $eccExtraInfo['claims'];
                        break;
                }
                $claims[$erpKey]['extra_info'] = json_encode($erpExtraInfo);
                unset($claims[$eccKey]);
            }
        }
        return $claims;
    }

    /**
     * To show refresh data link only if data mapping exists
     * @return bool
     */
    public function canShowRefreshLink()
    {
        return $this->_dealerHelper->claimStatusDataMappingExists();
    }
}