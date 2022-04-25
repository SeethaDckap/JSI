<?php
namespace Epicor\Comm\Model\Checkout;


class Dates  extends \Magento\Framework\Model\AbstractModel
{

    protected $_available_dates;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Model\Message\Request\DdaFactory
     */
    protected $commMessageRequestDdaFactory;

   

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\Message\Request\DdaFactory $commMessageRequestDdaFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        
        $this->scopeConfig = $scopeConfig;
        $this->commMessageRequestDdaFactory = $commMessageRequestDdaFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function isShow()
    {
        return $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/dda_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showAsList()
    {
        return $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/dda_request/showaslist', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAvailableDates($quote)
    {

        if (!isset($this->_available_dates)) {
            $dda = $this->commMessageRequestDdaFactory->create();
            /* @var $dda Epicor_Comm_Model_Message_Request_Dda */
            if ($dda->isActive()) {
                $dda->setQuote($quote);
                $dda->sendMessage();
            }
            $this->_available_dates = $dda->getDates();
        }
        return $this->_available_dates;
    }

    public function getDefaultAvailableDate()
    {
        $default_shipping_days = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/daystoship', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $date = date('Y-m-d', strtotime('+'.$default_shipping_days.' days'));
        return $date;
    }

}
