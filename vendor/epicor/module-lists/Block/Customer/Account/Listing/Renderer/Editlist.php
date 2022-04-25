<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Listing\Renderer;


/**
 * List Grid link grid renderer
 * 
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Editlist extends \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;
    /**
     * @var \Epicor\Common\Helper\Data
     */
    private $commonHelper;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlInterface,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->listsListModelFactory = $listsListModelFactory;
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
        $this->commonHelper = $commonHelper;
        $this->request = $request;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }
        if ($this->getColumn()->getLinks() == true) {
            $customerSession = $this->customerSession->getCustomer();
            $ownerId = $row->getData('owner_id');
            /* @var $row \Epicor\Lists\Model\ListModel */
            $checkMasterErp = $row->isValidEditForErpAccount($customerSession, $row->getId());
            $checkCustomer = $row->isValidEditForCustomers($customerSession, $row->getId(), $ownerId);
            $isMasterShopper = $customerSession->getData('ecc_master_shopper');
            if (!$isMasterShopper) {
                $customerId = $customerSession->getData('entity_id');
                if ($customerId == $ownerId) {
                    $edit = true;
                } else {
                    $edit = false;
                }
                //if not master shopper display all lists and allow addcarttolist, but remove all other actions
                if(!$checkCustomer){
                    $actions = $this->setViewActions();

                }
            } else {
                $edit = true;
            }

            $html = '';
     //this check is no longer required if a non mastershopper user can view lists not owned by themselves
    //        if ((!$checkMasterErp) || (!$checkCustomer) || (!$edit)) {
            $generalFields = array('edit', 'addListToCart');
            foreach ($actions as $action) {
                if (is_array($action)) {
                    $html .= '';
                    $actionId = $action['id'];
                    if (in_array($actionId, $generalFields)) {
                        $action['field'] = 'id/' . base64_encode($row->getId());
                        $hrefVal = $this->urlInterface->getUrl('*/*/' . $action['id']) . $action['field'];
                        $html .='<a id="'.$actionId . '" href="'.$hrefVal.'">' . $action['caption']->getText() . '</a>';
                    }

                    if($checkMasterErp) {
                        if ($actionId == "separator") {
                            $html .= $action['caption']->getText();
                        }

                        if ($actionId == "delete") {
                            $confirmationText = "'" . $action['confirm']->getText() . "'";
                            $warning = 'return window.confirm(' . $confirmationText . ' )';
                            $hrefVal = $this->urlInterface->getUrl('*/*/' . $action['id']) . 'id/' . $row->getId();
                            $html .= '<a id="' . $actionId . '" href="' . $hrefVal . '"  onclick="' . $warning . '"> ' . $action['caption']->getText() . '</a>';
                        }
                    }

                    //$html .= $this->_toLinkHtml($action, $row);
                }
            }
        //    }
            return $html;
        } else {
            return parent::render($row);
        }
    }
    protected function setViewActions()
    {
        $actions = array(
            array(
                'caption' => __('View'),
                'url' => array(
                    'base' => '*/*/edit'
                ),
                'id' => 'edit',
                'field' => 'id'
            )
        );
        if ($this->commonHelper->getScopeConfig()->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && $this->commonHelper->getScopeConfig()->isSetFlag('epicor_lists/savecartaslist/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($this->request->getFullActionName() == 'epicor_lists_lists_index'
            || $this->request->getFullActionName() == 'epicor_lists_lists_listgrid')
        ) {
            $actions[] = array(
                'caption' => __(' | '),
                'id' => 'separator',
                'field' => 'id',
            );
            $actions[] = array(
                'caption' => __('Add List To Cart'),
                'url' => array(
                    'base' => '*/*/addListToCart'
                ),
                'field' => 'id',
                'id' => 'addListToCart',
            );

        }
        return $actions;
    }
}
