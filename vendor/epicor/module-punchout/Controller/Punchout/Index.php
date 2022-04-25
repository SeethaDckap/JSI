<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Controller\Punchout;

use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Epicor\Punchout\Api\TransactionlogsRepositoryInterface;
use Epicor\Punchout\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;

/**
 * Transfer Cart
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * Connection repository interface.
     *
     * @var ConnectionsRepositoryInterface
     */
    protected $connectionRepository;

    /**
     * Transactionlogs repository interface.
     *
     * @var TransactionlogsRepositoryInterface
     */
    protected $transactionlogsRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Punchout\Model\PunchoutSession
     */
    protected $punchoutSession;

    /**
     * Json serializer.
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * Helper.
     *
     * @var helper
     */
    protected $helper;


    /**
     * @var \Epicor\Punchout\Model\Xmltoarray
     */
    protected $xmltoarray;


    /**
     * @var \Epicor\Punchout\Model\TransferCart
     */
    protected $transferCart;

    /**
     * Constructor function.
     *
     * @param Context $context Context.
     * @param ConnectionsRepositoryInterface $connectionRepository Connection repository inetrface.
     * @param logsRepositoryInterface $transactionlogsRepository Logs Repos.
     * @param \Epicor\Punchout\Model\PunchoutSession $punchoutSession
     * @param Data $helper Helper class.
     * @param \Epicor\Punchout\Model\Xmltoarray $xmltoarray Xml to array.
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ConnectionsRepositoryInterface $connectionRepository,
        TransactionlogsRepositoryInterface $transactionlogsRepository,
        \Epicor\Punchout\Model\PunchoutSession $punchoutSession,
        Data $helper,
        \Epicor\Punchout\Model\Xmltoarray $xmltoarray,
        \Epicor\Punchout\Model\TransferCart $transferCart
    )
    {
        $this->connectionRepository = $connectionRepository;
        $this->transactionlogsRepository = $transactionlogsRepository;
        $this->customerSession = $punchoutSession->getCustomerSession();
        $this->punchoutSession = $punchoutSession;
        $this->serializer = $helper->getSerializer();
        $this->helper = $helper;
        $this->xmltoarray = $xmltoarray;
        $this->transferCart = $transferCart;
        parent::__construct($context);
    }

    /**
     * Transfer Cart.
     *
     * @return Page
     * @throws LocalizedException Exception.
     */
    public function execute()
    {
        if ($this->customerSession->getIsPunchout()) {
            $transactionlog = $this->transactionlogsRepository->loadEntity();
            $transactionlog->startTiming();
            $connectionId = $this->customerSession->getConnectionId();
            $connection = $this->connectionRepository->getById($connectionId);
            $transactionlogData = [
                'connection_id' => $connectionId,
                'type' => 'PunchOut Order'
            ];
            $finalarray = $this->transferCart->getPunchoutOrderXml($connection);


            $xml = $this->xmltoarray->array2Xml($finalarray);
            $encodexml = urlencode($xml);
            $transactionlogData['cxml_request'] = $xml;
            $transactionlogData['cxml_response'] = 'N/A';
            $transactionlogData['message_code'] = 200;
            $transactionlogData['message_status'] = 'Success';
            $transactionlogData['source_url'] = $this->helper->getPunchoutUrl();
            $transactionlogData['target_url'] = $this->customerSession->getPostUrl();
            $transactionlog->endTiming();
            $transactionlog->addData($transactionlogData);
            $this->transactionlogsRepository->save($transactionlog);
            echo 'Cart is Getting Transfer';
            $postUrl = $this->customerSession->getPostUrl();
            $this->clearPunchoutSession();
            echo '<form id="cxml_form" method="POST" action="' . $postUrl . '" enctype="application/x-www-form-urlencoded">
                <input type="hidden" name="cXML-urlencoded" value="' . $encodexml . '"> </form>
            <script type="text/javascript">document.getElementById("cxml_form").submit()</script>';


        } else {
            $error = __('You do not have Permissions');
            $this->messageManager->addErrorMessage($error);
            $this->_redirect('customer/account/login');
        }
    }

    /**
     * Clear Session.
     */
    public function clearPunchoutSession()
    {
        $this->customerSession->unsIsPunchout();
        $this->customerSession->unsBuyerCookie();
        $this->customerSession->unsConnectionId();
        $this->customerSession->unsPostUrl();
        $this->customerSession->unsAllowedResource();
        $this->customerSession->unsPunchoutResource();
        $this->transferCart->logoutPunchoutSession();
        $this->customerSession->setDisplayLocations(false);
        $this->punchoutSession->initCustomerSection();
    }

}//end class
