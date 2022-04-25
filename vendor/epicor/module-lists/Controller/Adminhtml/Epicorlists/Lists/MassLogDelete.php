<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;


use Magento\Backend\App\Action\Context;
use Epicor\Lists\Api\ImportRepositoryInterface;
use Magento\Backend\App\Action;

class MassLogDelete extends Action
{
    /**
     * @var ImportRepositoryInterface
     */
    private $importRepository;

    /**
     * MassDelete constructor.
     *
     * @param Context                   $context
     * @param ImportRepositoryInterface $importRepository
     */
    public function __construct(
        Context $context,
        ImportRepositoryInterface $importRepository
    ) {
        parent::__construct($context);
        $this->importRepository = $importRepository;
    }

    /**
     * Deletes array of given List
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array)$this->getRequest()->getParam('massid');
        if ($ids) {
            foreach ($ids as $id) {
                $import = $this->importRepository->getById($id);
                $this->importRepository->delete($import);
            }
            $this->messageManager->addSuccess(__(count($ids).' Log Deleted'));
        }

        // Delete single Row
        $singleId = $this->getRequest()->getParam('id', null);
        if ($singleId) {
            $singleImport = $this->importRepository->getById($singleId);
            $this->importRepository->delete($singleImport);
            $this->messageManager->addSuccess(__('Delete Successfull'));
        }

        $this->_redirect('*/*/addbycsv');
    }

}
