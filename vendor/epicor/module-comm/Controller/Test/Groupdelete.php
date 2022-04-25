<?php
 
namespace Epicor\Comm\Controller\Test;
 
use Magento\Framework\App\Action\Context;
 
class Groupdelete extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $product;


    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Epicor\Comm\Helper\Product $product
            )
    {
        $this->product=$product;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
       $adminsession=$this->product->getAdminSession();
       if(isset($adminsession['status'])&& $adminsession['status']==1 ){
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
       }else{
            $customMessage = __("Sorry!! You don't have enough privileges to access the requested page.");
            $this->messageManager->addErrorMessage($customMessage);
             $this->_redirect('/');
            return false;
       }
    }
}