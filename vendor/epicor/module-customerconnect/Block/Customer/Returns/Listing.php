<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Returns;


/**
 * Customer Returns list
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_returns_read';

    const FRONTEND_RESOURCE_CREATE = 'Epicor_Customerconnect::customerconnect_account_returns_create';

    const ACCESS_MESSAGE_DISPLAY = TRUE;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    public function __construct(
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commReturnsHelper = $commReturnsHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    protected function _setupGrid()
    {
        $this->_controller = 'customer_returns_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Returns');

        $returnHelper = $this->commReturnsHelper;
        /* @var $returnHelper Epicor_Comm_Helper_Returns */

        $allowed = $returnHelper->checkConfigFlag('allow_create');

        if ($allowed && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE)) {
            $url = $this->getUrl('epicor_comm/returns/');
            $this->addButton(
                'new', array(
                'label' => __('New Return'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'save',
                ), 10
            );
        }
    }

}
