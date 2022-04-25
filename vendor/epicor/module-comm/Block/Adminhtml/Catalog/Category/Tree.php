<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Category;


/**
 * Categories tree block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Epicor
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Backend\Model\Auth\Session $backendSession,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        array $data = []
    )
    {
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        parent::__construct(
            $context,
            $categoryTree,
            $registry,
            $categoryFactory,
            $jsonEncoder,
            $resourceHelper,
            $backendSession,
            $data
        );
    }
    protected function _prepareLayout()
    {
        $this->setTemplate('Magento_Catalog::catalog/category/tree.phtml');
        return parent::_prepareLayout();
    }

    /**
     * Get category name
     *
     * @param \Magento\Framework\DataObject $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = '';
        $erpModel = $this->catalogCategoryFactory->create()->load($node->getId());
        if ($erpModel->getEccErpCode())
            $result .= $erpModel->getEccErpCode() . ': ';

        $result .= parent::buildNodeName($node);
        return $result;
    }

}
