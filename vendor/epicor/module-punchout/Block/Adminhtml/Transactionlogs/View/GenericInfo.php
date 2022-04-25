<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Block
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Block\Adminhtml\Transactionlogs\View;

use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Magento\Backend\Block\Template\Context;
use Epicor\Punchout\Api\TransactionlogsRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class GenericButton
 *
 */
class GenericInfo extends \Magento\Backend\Block\Template
{

    /**
     * Transactionlog repository interface.
     *
     * @var TransactionlogsRepositoryInterface
     */
    protected $transactionlogRepository;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;
    /**
     * Connection repository interface.
     *
     * @var ConnectionsRepositoryInterface
     */
    protected $connectionRepository;

    /**
     * Rules constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context Context.
     * @param TransactionlogsRepositoryInterface $transactionlogRepository transactionlog repo.
     * @param ConnectionsRepositoryInterface $connectionRepository Connection repo.
     * @param \Epicor\Common\Helper\Data $commonHelper Comm Helper.
     * @param array $data Data array.
     */
    public function __construct(
        Context $context,
        TransactionlogsRepositoryInterface $transactionlogRepository,
        ConnectionsRepositoryInterface $connectionRepository,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    )
    {
        $this->transactionlogRepository = $transactionlogRepository;
        $this->commonHelper = $commonHelper;
        $this->connectionRepository = $connectionRepository;
        parent::__construct($context, $data);

    }//end __construct()

    /**
     * Return Log Info.
     *
     * @return TransactionlogsRepositoryInterface
     * @throws LocalizedException Exception.
     */
    public function getLog()
    {
        return $this->transactionlogRepository->loadEntity();
    }

    /**
     * Date Formate.
     *
     * @return string
     */
    public function getDate($date)
    {
        return $this->commonHelper->getLocalDate($date, \IntlDateFormatter::MEDIUM, true);
    }

    /**
     * Return Connection Info.
     *
     * @param int $id
     * @return string
     */
    public function getConnectionName($id)
    {
        if ($id) {
            $connection = $this->connectionRepository->getById($id);
            return $connection->getConnectionName();
        }
        return '';
    }
}//end class
