<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Source;


/**
 * CMS page dropdown optiosn for config
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 */
class Cmspages
{

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $cmsPage;

    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory
    )
    {
        $this->cmsPage = $pageFactory;
    }


    public function toOptionArray()
    {
        $arr = array();

        //M1 > M2 Translation Begin (Rule p2-1)
        //$collection = Mage::getmodel('cms/page')->getCollection();
        $collection = $this->cmsPage->create()->getCollection();
        //M1 > M2 Translation End
        foreach ($collection->getItems() as $page) {
            /* @var $page Mage_Cms_Model_Page */
            $arr[] = array('label' => $page->getTitle(), 'value' => $page->getId());
        }

        return $arr;
    }

}
