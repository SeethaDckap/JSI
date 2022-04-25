<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Controller\Index;

class Vote extends \Epicor\Faqs\Controller\Index
{
     /**
     * @var \Epicor\Faqs\Helper\Data
     */
    protected $faqsHelper;

     /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory ;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Faqs\Helper\Data $faqsHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory    
    ) {
        $this->faqsHelper = $faqsHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context,
            $faqsHelper    
        );
    }
    
    
    public function execute()
    {
        $faqId = $this->getRequest()->getParam('id');
        
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('faqs_vote')->setFaqId($faqId);
        echo $this->_view->getLayout()->getBlock('faqs_vote')->toHtml();
    }
    

}
