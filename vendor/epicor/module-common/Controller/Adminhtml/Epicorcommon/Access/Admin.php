<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access;


/**
 * 
 * Access rights controller
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
abstract class Admin extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    protected $_aclId = 'customer/access/admin';

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory
     */
    protected $commonResourceAccessElementCollectionFactory;

    /**
     * @var \Epicor\Common\Model\Access\ElementFactory
     */
    protected $commonAccessElementFactory;

    public function __construct(
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory $commonResourceAccessElementCollectionFactory,
        \Epicor\Common\Model\Access\ElementFactory $commonAccessElementFactory
    ) {
        $this->backendJsHelper = $backendJsHelper;
        $this->commonResourceAccessElementCollectionFactory = $commonResourceAccessElementCollectionFactory;
        $this->commonAccessElementFactory = $commonAccessElementFactory;
    }
    protected function _initPage()
    {
        $this->loadLayout()
            ->_setActiveMenu('epicor_common/access/admin')
            ->_addBreadcrumb(__('Access Management '), __('Administration'));
        return $this;
    }
/**
     * @param array $elementIds
     */
    private function saveElements($data)
    {
        $elementIds = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['elements']));
        $collection = $this->commonResourceAccessElementCollectionFactory->create();
        /* @var $collection Epicor_Common_Model_Resource_Access_Element_Collection */
        $collection->addFieldToFilter('excluded', 1);

        $existing = array();

        // Remove old - only if they're not passed in the data

        foreach ($collection->getItems() as $element) {
            if (!in_array($element->getId(), $elementIds)) {
                $element->setExcluded(0);
                $element->save();
            } else {
                $existing[] = $element->getId();
            }
        }

        // Add new - only if they don't already exist

        foreach ($elementIds as $elementId) {
            if (!in_array($elementId, $existing)) {
                $model = $this->commonAccessElementFactory->create()->load($elementId);
                $model->setExcluded(1);
                $model->save();
            }
        }
    }
}
