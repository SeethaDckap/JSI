<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Cart\Contract\Select\Renderer;

/**
 * Column Renderer for Line Contract Select Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Select extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Epicor\Lists\Helper\Messaging\Customer
     */
    protected $listsMessagingCustomerHelper;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\App\Request\Http $request, \Magento\Framework\Url\EncoderInterface $urlEncoder, \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper, array $data = []
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->urlEncoder = $urlEncoder;
        $this->listsMessagingCustomerHelper = $listsMessagingCustomerHelper;
        parent::__construct(
                $context, $data
        );
    }

    /**
     * Render column
     *
     * @param   \Epicor\Lists\Model\ListModel $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row) {
        $item = $this->registry->registry('ecc_line_contract_item');
        /* @var $item Epicor_Comm_Model_Quote_Item */
        $delimiter = $this->listsMessagingCustomerHelper->getUOMSeparator();
        $erpCode = explode($delimiter, $row->getErpCode());

        if ($erpCode[1] == $item->getEccContractCode()) {
            $html = __('Currently Selected');
        } else {
            $urlReturn = $this->request->getParam('return_url');
            $params = array(
                'itemid' => $item->getId(),
                'contract' => $row->getId(),
                'return_url' => base64_encode($this->urlEncoder->encode($urlReturn))
            );
            $url = $this->getUrl('epicor_lists/cart/applycontractselect', $params);
            $html = '<a href="' . $url . '">' . __('Select') . '</a>';
        }

        return $html;
    }

}
