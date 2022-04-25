<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Themes
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

declare(strict_types=1);

namespace Epicor\Punchout\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Epicor\Punchout\Model\ResourceModel\Transactionlogs\CollectionFactory;
use Epicor\Punchout\Api\TransactionlogsRepositoryInterface;
use Epicor\Punchout\Helper\Data;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;

/**
 * Greenblack menu tab controller
 */
abstract class Transactionlogs extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Epicor_Punchout::transaction_logs';

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * Result Factory
     *
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * Transactionlogs repository interface.
     *
     * @var TransactionlogsRepositoryInterface
     */
    protected $transactionlogsRepository;


    /**
     * Connection collection
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor function.
     *
     * @param Context $context Context.
     * @param \Epicor\Comm\Helper\Data $commHelper Comm Helper.
     * @param PageFactory $resultPageFactory Page factory.
     * @param FIlter $filter UI component filter.
     * @param logsRepositoryInterface $transactionlogsRepository Logs Repos.
     * @param CollectionFactory       $collectionFactory    Connection collection.
     */
    public function __construct(
        Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        PageFactory $resultPageFactory,
        Filter $filter,
        TransactionlogsRepositoryInterface $transactionlogsRepository,
        CollectionFactory $collectionFactory
    )
    {
        parent::__construct($context);

        $this->commHelper=$commHelper;
        $this->resultFactory = $context->getResultFactory();
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->transactionlogsRepository = $transactionlogsRepository;
        $this->collectionFactory    = $collectionFactory;

    }//end __construct()


    /**
     * Load Entity.
     *
     * @param string|null $id ID.
     *
     * @return transactionlogs
     */
    public function loadEntity($id = null)
    {
        return $this->transactionlogsRepository->loadEntity($id);

    }//end loadEntity()

}//end class
