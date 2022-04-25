<?php

namespace Silk\CustomForms\Controller\DealerRequest;

use Magento\Framework\App\Action\Context;
use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
class CreatePost extends \Magento\Framework\App\Action\Action
{
    private $logger;
    protected $userFactory;
    protected $resultFactory;
    protected $dealerRequestFactory;
    protected $modelVar;
    protected $dataHelper;
    private $storeManager;
    private $transportBuilder;
    private $scopeConfig;
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        ConfigInterface $contactsConfig,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Silk\CustomForms\Model\DealerRequestFactory $dealerRequestFactory,
        \Magento\Variable\Model\Variable $modelVar,
        \Silk\CustomForms\Helper\Data $dataHelper,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager = null,
        TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->modelVar = $modelVar;
        $this->dealerRequestFactory = $dealerRequestFactory;
        $this->resultFactory=$resultFactory;
        $this->pageFactory = $pageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute(){
        $this->_view->loadLayout(); 
        $this->_view->renderLayout();
        $post = (array) $this->getRequest()->getPost();
        // var_dump($post);die();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!empty($post)) {
            $requestData = [
                
                "created_at"  => date('Y/m/d H:i:s', time()),
                "f_name" => $post['f_name'],
                "l_name" => $post['l_name'],
                "company_name" => $post['company_name'],
                "street" => $post['street'],
                "city" => $post['city'],
                "state" => $post['state'],
                "zip_code" => $post['zip_code'],
                "phone" => $post['phone'],
                "email" => $post['email'],
                "showroom" => $post['showroom'],
                "offer" => $post['offer'],
                "design_software" => $post['design_software'],
                "have_designer" => $post['have_designer'],
                "how_did_you_hear_us" => $post['how_did_you_hear_us'],
                "comment" => $post['comment']

            ];
            try{
                $saveStatus = $this->saveRecord($requestData);
            }catch(\Exception $e){
                $this->logger->critical('Error message', ['exception' => $e]);
                return [
                    "status" => false,
                    "message" => "Record saved failure. Please contact us for assistance"
                ];

            }

            $this->messageManager->addSuccessMessage('Submit success!');

            $isEmailSentSuccess = $this->sendEmails($post);
            if(!$isEmailSentSuccess["status"]){
                $this->messageManager->addErrorMessage($isEmailSentSuccess["message"]);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }else{
                $this->messageManager->addSuccessMessage($isEmailSentSuccess["message"]);
            }

            // $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            // return $resultRedirect;


            return $this->resultRedirectFactory->create()->setPath('become-a-dealer-submitted');

        }
    }

    public function saveRecord($data){
        try{
            $dealerRequestRecord = $this->dealerRequestFactory->create();
            foreach($data as $key => $val){
                $dealerRequestRecord->setData($key, $val);
            }
            $dealerRequestRecord->save();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical('Error message', ['exception' => $e]);
            return false;
        }
    }

    public function sendEmails($post){
        try{
            
            if($post['customer_email_id'] == Null){
                $customerEmailTemplate = "dealer_request";
            }else{
                $customerEmailTemplate = $post['customer_email_id'];
            }

            if($csEmailTemplate = $post['cs_email_id'] == Null){
                $csEmailTemplate = "dealer_request";
            }else{
                $csEmailTemplate = $post['cs_email_id'];
            }
            $blockInstance = $this->_objectManager->get('Silk\CustomForms\Block\ContactSupport');
            $emailMapping = json_decode($blockInstance->getVariableValue(), true);
            
            $sender = $this->scopeConfig->getValue(ConfigInterface::XML_PATH_EMAIL_SENDER, ScopeInterface::SCOPE_STORE);
            $requestData = array();
            
            $requestData["created_at"]  = date('Y/m/d H:i:s', time());
            $requestData["f_name"] = $post['f_name'];
            $requestData["l_name"] = $post['l_name'];
            $requestData["company_name"] = $post['company_name'];
            $requestData["street"] = $post['street'];
            $requestData["city"] = $post['city'];
            $requestData["state"] = $post['state'];
            $requestData["zip_code"] = $post['zip_code'];
            $requestData["phone"] = $post['phone'];
            $requestData["email"] = $post['email'];
            $requestData["showroom"] = $post['showroom'];
            $requestData["offer"] = $post['offer'];
            $requestData["design_software"] = $post['design_software'];
            $requestData["have_designer"] = $post['have_designer'];
            $requestData["how_did_you_hear_us"] = $post['how_did_you_hear_us'];
            $requestData["comment"] = $post['comment'];


            
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($requestData);
            //______regular mail_________to Customer service
            
            $transportCs = $this->transportBuilder
                ->setTemplateIdentifier($csEmailTemplate)
                ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom(['email'=>$post['email'],'name'=>$post['f_name']]);
                foreach($emailMapping as $value) {
                    if($value['name'] == 'dealer_request'){
                        $email = explode(", ",$value['receivers']);
                        $transportCs->addTo($email);
                    }
                };
                //->addTo('customerservice@jsicabinetry.com')
                
            $transportCs->getTransport()->sendMessage();
            
            //______regular mail_________to Customer        
            $transportCustomer = $this->transportBuilder
                ->setTemplateIdentifier($customerEmailTemplate)
                ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom(['email'=>'customerservice@jsicabinetry.com','name'=>'Jsi Customer Service'])
                ->addTo($post['email'])
                ->getTransport();
            $transportCustomer->sendMessage();
    
            return [
                "status" => true,
                "message" => "Thank you, we will contact you shortly"
            ];
        }catch(\Exception $e){
            $this->logger->critical('Error message', ['exception' => $e]);
            return [
                "status" => false,
                "message" => "Something went wrong when sending emails. Please contact us for assistance"
            ];
        }
    }
}