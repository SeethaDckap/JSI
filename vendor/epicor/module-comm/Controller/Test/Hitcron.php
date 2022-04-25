<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
ini_set('display_errors',1);
class Hitcron extends \Magento\Framework\App\Action\Action
{
protected $_resultPageFactory;
    protected $productCron;

	public function __construct(
		Context $context, //\Epicor\Comm\Helper\Product $product,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Epicor\Comm\Model\Cron\Product $productCron
       // \Epicor\Dealerconnect\Model\Cron $productCron
        )
	{
		$this->_resultPageFactory = $resultPageFactory;
        $this->croncall = $productCron;
        parent::__construct($context);
	}

	public function execute()
	{
        //die('testing');
	   // $this->croncall->updateClaimsStatusData();
		//$this->croncall->scheduleRelatedDocument();
        $this->croncall->scheduleImage();
echo "i am back";
	}
    }
