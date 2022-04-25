<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Epicor\Elasticsearch\Api\Data\BoostInterfaceFactory;
use Epicor\Elasticsearch\Api\BoostRepositoryInterface;
use Epicor\Elasticsearch\Model\Boost\Copier;
/**
 * Abstract Boost controller
 *
 */
abstract class AbstractBoost extends Action
{
    /**
     * @var PageFactory|null
     */
    protected $resultPageFactory = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var BoostRepositoryInterface
     */
    protected $boostRepository;

    /**
     * Boost Factory
     *
     * @var BoostInterfaceFactory
     */
    protected $boostFactory;

    /**
     * @var Copier
     */
    protected $boostCopier;

    /**
     * Abstract constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param BoostRepositoryInterface $boostRepository
     * @param BoostInterfaceFactory $boostFactory
     * @param Copier $boostCopier
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        BoostRepositoryInterface $boostRepository,
        BoostInterfaceFactory $boostFactory,
        Copier $boostCopier
    ) {
        parent::__construct($context);
        $this->resultPageFactory    = $resultPageFactory;
        $this->coreRegistry         = $coreRegistry;
        $this->boostRepository  = $boostRepository;
        $this->boostFactory     = $boostFactory;
        $this->boostCopier      = $boostCopier;
    }

    /**
     * Create result page
     *
     * @return Page
     */
    protected function createPage()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Elasticsearch::boost')
            ->addBreadcrumb(__('Boost'), __('Boost'));
        return $resultPage;
    }

    /**
     * Check if allowed to manage boost.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Epicor_Elasticsearch::boost');
    }
}
