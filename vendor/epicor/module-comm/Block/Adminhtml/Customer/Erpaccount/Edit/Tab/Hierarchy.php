<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


class Hierarchy extends \Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\AbstractBlock
{

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;
    /**
     * @var \Epicor\Comm\Model\Config\Source\Yesnonulloption
     */
    protected $yesnonulloption;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Config\Source\Yesnonulloption $yesnonulloption,
        array $data = []
    )
    {
        $this->layout = $context->getLayout();
        $this->yesnonulloption = $yesnonulloption;
        parent::__construct(
            $context,
            $registry,
            $data
        );
        $this->_title = 'Hierarchy';
        $this->setTemplate('epicor_comm/customer/erpaccount/edit/hierarchy.phtml');
    }

    public function getParentsHtml()
    {
        return $this->layout->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Hierarchy\Parents')->toHtml();
    }

    public function getChildrenHtml()
    {
        return $this->layout->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Hierarchy\Children')->toHtml();
    }

    public function getParentOptions()
    {
        $baseOptions = \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy::$linkTypes;

        $erpAccount = $this->registry->registry('customer_erp_account');
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        $parents = $erpAccount->getParents();

        $options = array();

        foreach ($baseOptions as $key => $val) {
            if (!isset($parents[$key])) {
                $options[$key] = $val;
            }
        }

        return $options;
    }

    public function getChildOptions()
    {
        return \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy::$linkTypes;
    }

    //M1 > M2 Translation Begin (Rule p2-1)
    public function getYesNoNullOption()
    {
        return $this->yesnonulloption;
    }
    //M1 > M2 Translation End

}
