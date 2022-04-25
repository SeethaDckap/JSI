<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Admin;

class Updateelements extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Admin
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\ElementFactory
     */
    protected $commonResourceAccessElementFactory;

    public function __construct(
        \Epicor\Common\Model\ResourceModel\Access\ElementFactory $commonResourceAccessElementFactory
    ) {
        $this->commonResourceAccessElementFactory = $commonResourceAccessElementFactory;
    }
    public function execute()
    {
        $model = $this->commonResourceAccessElementFactory->create();
        /* @var $model Epicor_Common_Model_Resource_Access_Element */
        $model->regenerate();
        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setBody('true');
        $this->_response->setBody('true');
        //M1 > M2 Translation End
    }

}
