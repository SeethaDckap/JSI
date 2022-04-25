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


use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Epicor\Punchout\Api\TransactionlogsRepositoryInterface;

/**
 * Cancel Punchout Session
 */
class Logout extends \Epicor\Punchout\Controller\Punchout\Index
{
    /**
     * Punchout logout action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
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
            $finalarray = $this->transferCart->getPunchoutOrderXml($connection, true);
            $xml = $this->xmltoarray->array2Xml($finalarray);
            $encodexml = urlencode($xml);
            $transactionlogData['cxml_request'] = $xml;
            $transactionlogData['cxml_response'] = 'N/A';
            $transactionlogData['message_code'] = 200;
            $transactionlogData['message_status'] = 'Success';
            $transactionlogData['source_url'] = $this->helper->getLogoutUrl();
            $transactionlogData['target_url'] = $this->customerSession->getPostUrl();
            $transactionlog->endTiming();
            $transactionlog->addData($transactionlogData);
            $this->transactionlogsRepository->save($transactionlog);
            echo 'Cancel Punchout Session';
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

}//end class
