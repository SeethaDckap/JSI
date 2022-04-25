<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Manage\Select\Grid\Renderer;


/**
 * Column Renderer for Branchpickup Select Grid
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Select extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    protected $urlHelper;

      public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Url\Helper\Data $url,
        array $data = [])
    {
        $this->customerSession = $customerSession;
        $this->salesRepHelper = $salesRepHelper;
        $this->commHelper = $commHelper;
        $this->urlHelper = $url;
        parent::__construct($context, $jsonEncoder, $data);
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }
        if ($this->getColumn()->getLinks() == true) {
            $customerSession = $this->customerSession;
            /* @var $customerSession Mage_Customer_Model_Session */
            $masquerade = $customerSession->getMasqueradeAccountId();
            $html = '';
            $rowId = $row->getId();
            $helper = $this->salesRepHelper;
            /* @var $helper Epicor_SalesRep_Helper_Data */
            $isSecure = $helper->isSecure();

            $url = $this->getUrl('salesrep/account/index', array('_forced_secure' => $isSecure));
            $redirectUrl = $this->getUrl('salesrep/account/index', array('_forced_secure' => $isSecure));
            $returnUrl = $this->urlHelper->getEncodedUrl($url);
            $ajax_url = $this->getUrl('comm/masquerade/masquerade', array('_forced_secure' => $isSecure));
            if ((!empty($masquerade)) && ($rowId == $masquerade)) {
                $html .= __('Currently Selected');
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        if (($html != '') && ($action['caption'] != 'Begin Masquerade')) {
                            $html .= '<span class="action-divider">' . ($this->getColumn()->getDivider() ?: ' | ') . '</span>';
                            $html .= $this->_toLinkHtml($action, $row);
                        }
                    }
                }
            } else {
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        $action['url_id'] = $rowId;
                        $html .= $this->_toLinkHtml($action, $row);
                    }
                }
            }
            $html .= '<input type="hidden" name="return_url" id="return_url" value="' . $returnUrl . '">';
            $html .= '<input type="hidden" name="jreturn_url" id="jreturn_url" value="' . $redirectUrl . '">';
            $html .= '<input type="hidden" name="ajax_url" id="ajax_url" value="' . $ajax_url . '">';
            return $html;
        } else {
            return parent::render($row);
        }
    }

}
