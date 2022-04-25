<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Lists
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;
use Epicor\Lists\Api\ImportRepositoryInterface as RepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * View Log
 */
class View extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RepositoryInterface
     */
    private $importRepository;

    /**
     * View constructor.
     *
     * @param Context             $context
     * @param RepositoryInterface $importRepository
     */
    public function __construct(
        Context $context,
        RepositoryInterface $importRepository,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->importRepository=$importRepository;
    }

    /**
     * View Action.
     *
     * @return Page
     * @throws LocalizedException Exception.
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $import = $this->loadEntity($id);
        $resultPage = $this->pageFactory->create();

        $title = __('New Import');
        if ($import->getId()) {
            $title = $import->getFileName();
            $title = __("Import log for ".$title);
        }

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;

    }//end execute()

    /**
     * @param null $id
     *
     * @return mixed
     */
    public function loadEntity($id = null)
    {
        return $this->importRepository->loadEntity($id);

    }//end loadEntity()


}//end class
