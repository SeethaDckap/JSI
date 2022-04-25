<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Roles\Edit;


/**
 * Role edit tabs
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Epicor\AccessRight\Model\RoleModel
     */
    private $_role;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $jsonEncoder,
            $authSession,
            $data
        );
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('Access Role');
    }

    protected function _beforeToHtml()
    {
        $role = $this->getRole();
        /* @var $role Epicor/AccessRight/Model/RoleModel */

        $roleId = $role->getId();
        $this->addTab('details', array(
            'label' => 'Details',
            'title' => 'Details',
            'content' => $this->getLayout()->createBlock('\Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab\Details')->toHtml(),
        ));


            $this->addTab(
                'accessrights_section',
                [
                    'label' => __('Access Rights'),
                    'title' => __('Access Rights'),
                    'content' => $this->getLayout()->createBlock(
                        \Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab\AccessRights::class,
                        'user.roles.grid'
                    )->toHtml()
                ]
            );


//            $typeInstance = $list->getTypeInstance();

//            if ($typeInstance->isSectionVisible('erpaccounts')) {
//                $this->addTab('erpaccounts', array(
//                    'label' => 'ERP Accounts',
//                    'title' => 'ERP Accounts',
//                    'url' => $this->getUrl('*/*/erpaccounts', array('id' => $list->getId(), '_current' => true)),
//                    'class' => 'ajax',
//                ));
//            }
//
//
//            if ($typeInstance->isSectionVisible('customers')) {
//                $this->addTab('customers', array(
//                    'label' => 'Customers',
//                    'title' => 'Customers',
//                    'url' => $this->getUrl('*/*/customers', array('id' => $list->getId(), '_current' => true)),
//                    'class' => 'ajax',
//                ));
//            }


        $this->addTab('erpaccounts', array(
            'label' => 'ERP Accounts',
            'title' => 'ERP Accounts',
            'url' => $this->getUrl('*/*/erpaccounts', array('id' => $roleId, '_current' => true)),
            'class' => 'ajax',
        ));

        $this->addTab('customers', array(
            'label' => 'Customers',
            'title' => 'Customers',
            'url' => $this->getUrl('*/*/customers', array('id' => $role->getId(), '_current' => true)),
            'class' => 'ajax',
        ));

        return parent::_beforeToHtml();
    }

    /**
     * Gets the current Role
     *
     * @return \Epicor\AccessRight\Model\RoleModel
     */
    public function getRole()
    {
        if (!$this->_role) {
            $this->_role = $this->registry->registry('role');
        }
        return $this->_role;
    }

}
