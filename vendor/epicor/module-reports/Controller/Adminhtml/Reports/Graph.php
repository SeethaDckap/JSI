<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Controller\Adminhtml\Reports;

class Graph  extends \Epicor\Reports\Controller\Adminhtml\Reports
{

    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\Filesystem\Io\FileFactory $ioFileFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($ioFileFactory, $directoryList, $context, $backendAuthSession);
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        /* $post = array(
          'store_id' => 0,
          'chart_type' => 'performance',
          'message_status' => 'both',
          'message_type' => array('GOR'),
          'from' => '2014-07-14',
          'to' => '2014-07-16'
          ); */
       // $this->loadLayout();
        //M1 > M2 Translation Begin (Rule 13)
        //$this->_initLayoutMessages('reports/session');
        //M1 > M2 Translation End
     //   $this->getLayout()->getBlock('root')->setChartOptions($post);
        
        //   $response = array('error' => false, 'success' => true, 'ajaxExpired' => true, 'ajaxRedirect' => $url);
      //  $resultJson = $this->resultJsonFactory->create();
       // $resultJson->setData( $this->getLayout()->getBlock('root')->setChartOptions($post));
        //$this->renderLayout();
              $resultPage = $this->_resultPageFactory->create();
//           $block = $resultPage->getLayout()->getBlock('root')->setChartOptions($post);
//
//        $this->getResponse()->setBody($block);
//        
        $block = $resultPage->getLayout()->createBlock('Epicor\Reports\Block\Adminhtml\Reports')
                ->setChartOptions($post)
                ->setTemplate('epicor_reports/graph.phtml')
                ->toHtml();

        $this->getResponse()->setBody($block);
        
        
    }

 }
