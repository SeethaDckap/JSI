<?php
namespace Epicor\Comm\Controller\Onepage;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;

class GetShippingDates extends \Epicor\Comm\Controller\Onepage
{

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $shippingdates;
    
 public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Epicor\Comm\Model\Checkout\Dates $shippingdates,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
                
        );
        $this->shippingdates = $shippingdates;
    }

    
    public function execute()
    {
        $this->_expireAjax();
        $quote = $this->getOnepage()->getQuote();
        $data = $this->getRequest()->getPost();
        $addressdata =  json_decode($data['addressdata'], True);
        $result['success']= 0;
        if($this->shippingdates->isShow()){  
            if($addressdata){
                $result['success']= 1;
                if(isset($addressdata['customerAddressId'])){
                    $addressdata['customer_address_id']= $addressdata['customerAddressId'];
                }
                $quote->getShippingAddress()->addData($addressdata);
                $availabledates = $this->shippingdates->getAvailableDates($quote);
                if(count($availabledates) === 0){
                    $result['dates'][$this->shippingdates->getDefaultAvailableDate()] = 'Next Available Day';
                }else{         
                    foreach($availabledates as $key=>$date){
                        $result['dates'][$date] = date( 'F j, Y', strtotime($date));
                    }
                } 
            }  
        }
        return $this->resultJsonFactory->create()->setData($result);
    }

    }
