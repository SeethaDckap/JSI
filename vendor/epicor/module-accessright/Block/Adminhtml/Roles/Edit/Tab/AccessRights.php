<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab;

use Epicor\AccessRight\Model\ResourceModel\Rules\CollectionFactory as RulesCollectionFactory;

/**
 * Rolesedit Tab Display Block.
 *
 * @api
 * @since 100.0.2
 */
class AccessRights extends \Magento\Backend\Block\Widget\Form implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var string
     */
    protected $_template = 'Epicor_AccessRight::accessrights/tree.phtml';

    /**
     * Acl resource provider
     *
     * @var \Epicor\AccessRight\Acl\AclResource\ProviderInterface
     */
    protected $_aclResourceProvider;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 100.1.0
     */
    protected $coreRegistry = null;
    protected $allowedResources = null;

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory
     */
    protected $rulesCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\AccessRight\Acl\AclResource\ProviderInterface $aclResourceProvider,
        RulesCollectionFactory $rulesCollectionFactory,
        array $data = []
    )
    {


        $this->_aclResourceProvider = $aclResourceProvider;
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Set core registry
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @return void
     * @deprecated 100.1.0
     * @since 100.1.0
     */
    public function setCoreRegistry(\Magento\Framework\Registry $coreRegistry)
    {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get core registry
     *
     * @return \Magento\Framework\Registry
     * @deprecated 100.1.0
     * @since 100.1.0
     */
    public function getCoreRegistry()
    {
        if (!($this->coreRegistry instanceof \Magento\Framework\Registry)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Registry::class);
        } else {
            return $this->coreRegistry;
        }
    }

    /**
     * Get tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Role Resources');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Whether tab is available
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Whether tab is visible
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }


    public function getArrayData()
    {
        return $this->_aclResourceProvider->getAclResources();

    }

    public function getAccessRoleId()
    {
        return $this->getRequest()->getParam('id') ?
            $this->getRequest()->getParam('id') : $this->getRequest()->getParam('cid');
    }

    /**
     * Get a list of available resource using user role id
     *
     * @param string $roleId
     * @return string[]
     */
    public function getAllowedResourcesByRole()
    {
        $roleId = $this->getAccessRoleId();

        if ($this->allowedResources === null) {
            $this->allowedResources = [];
            $rulesCollection = $this->rulesCollectionFactory->create();
            $rulesCollection->getByRoles($roleId)->load();
            /** @var \Magento\Authorization\Model\Rules $ruleItem */
            foreach ($rulesCollection->getItems() as $ruleItem) {
                $resourceId = $ruleItem->getResourceId();
                $this->allowedResources[] = $resourceId;
            }
        }
        return $this->allowedResources;
    }

    public function hasChild($data)
    {
        return (isset($data['children']) && !empty($data['children'])) ? true : false;

    }

    public function isSelected($id)
    {
        if (in_array($id, $this->getAllowedResourcesByRole())) {
            return 'checked="true"';
        } else {
            return '';
        }
    }

    public function buildChildren($nodes, $level = 1)
    {
        $html = '';
        foreach ($nodes as $node) {
            $haschild = $this->hasChild($node);
            $html .= '<li>';
            if ($haschild) {
                $html .= '<i class="fa fa-plus"></i>';
            }

            $htmlid = str_replace('::', '_', $node['id']);
            if (isset($node['displaytype']) || $level > 1) {
                $menu = 'menu-checkbox';
            } else {
                $menu = '';
            }
            $html .= '<label> <input ' . $this->isSelected($node['id']) . ' value="' . $node['id'] . '" 
            name="resource[]" id="' . $htmlid . '" data-id="dt-' . $htmlid . '" class="chkbox ' . $menu . '" 
            type="checkbox" value="1"/>' . $node['title'] . '</label>';


            if (isset($node['actioncontoler'])) {
                $html .= '<button type="button" class="selectal action-default selectal action-secondary" vlaue="selectal">' . __('Full Access') . '</button>
                            <button type="button" class="readonly action-default scalable action-primary" vlaue="readonly">' . __('Read Only') . '</button>
                <button type="button" class="deselectal action-default scalable " vlaue="deselectal">' . __('No Access') . '</button>';
            }

            $level++;
            if (isset($node['displaytype']) && $node['displaytype'] == 'subinfo') {
                $html .= '<ul>';
                $html .= $this->buildChildrenTable($node['children']);
                $html .= '</ul>';
            } elseif (isset($node['displaytype']) && $node['displaytype'] == 'grid') {
                $html .= '<ul>';
                $html .= $this->buildChildrenGrid($node['children']);
                $html .= '</ul>';
            } elseif ($haschild) {
                $html .= '<ul>';
                $html .= $this->buildChildren($node['children'], $level);
                $html .= '</ul>';
            }
            $html .= '</li>';
            $level--;
        }
        return $html;
    }

    public function buildChildrenTable($nodes)
    {
        $allActions = [];
        $allDatas = [];
        foreach ($nodes as $node) {
            $haschild = $this->hasChild($node);
            $allDatas[$node['id']]['id'] = $node['id'];
            $allDatas[$node['id']]['title'] = $node['title'];
            if ($haschild) {
                if (isset($node['displaytype']) && $node['displaytype'] == 'grid') {
                    foreach ($node['children'] as $node2) {
                        if (isset($node2['actioncode'])) {
                            $allActions[$node2['actioncode']] = $node2['title'];
                            $allDatas[$node['id']][$node2['actioncode']] = $node2['actioncode'];
                            $allDatas[$node['id']][$node2['actioncode']] = $node2['id'];
                        }
                    }
                }
            }
        }
        $html = '';
        if (!empty($allActions)) {
            $html .= '<table border="1"><tr><th>&nbsp;</th>';
            foreach ($allActions as $action) {
                $html .= '<th>' . $action . '</th>';
            }
            $html .= '</tr>';
            foreach ($allDatas as $data) {

                $html .= '<tr>';
                $html .= '<td>' . $data['title'] . '</td>';
                foreach ($allActions as $key => $action) {
                    if (isset($data[$key])) {
                        //  echo '<pre>'; print_r($data);
                        $htmlid = str_replace('::', '_', $data[$key]);
                        $acceesspoints = '';
                        if($key != 'read'){
                            $acceesspoints = 'acceesspoints';
                        }
                        $html .= '<td><li> <label>
                    <input   ' . $this->isSelected($data[$key]) . '  data-id="dt-' . $htmlid . '" name="resource[]"
                     value="' . $data[$key] . '" type="checkbox"   class="'.$acceesspoints .' ' . $key . '"  
                     id="' . $htmlid . '">
                    </label></li></td>';
                    } else {
                        $html .= '<td> </td>';

                    }
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        return $html;
    }

    public function buildChildrenGrid($nodes)
    {
        $allActions = [];
        $allDatas = [];
        foreach ($nodes as $node) {
            $haschild = $this->hasChild($node);
            $allDatas[$node['id']]['id'] = $node['id'];
            $allDatas[$node['id']]['title'] = $node['title'];
            if (isset($node['actioncode'])) {
                $allActions[$node['actioncode']]['title'] = $node['title'];
                $allActions[$node['actioncode']]['id'] = $node['id'];
                $allDatas[$node['id']][$node['actioncode']] = $node['actioncode'];
            }
        }
        $html = '';
        $html .= '<table border="1"><tr>';
        foreach ($allActions as $action) {
            $html .= '<th>' . $action['title'] . '</th>';
        }
        $html .= '</tr>';
        $html .= '<tr>';
        foreach ($allActions as $key => $action) {
            if (isset($allDatas[$action['id']][$key])) {
                $htmlid = str_replace('::', '_', $action['id']);
                $acceesspoints = '';
                if($key != 'read'){
                    $acceesspoints = 'acceesspoints';
                }
                $html .= '<td><li> <label><input  ' . $this->isSelected($action['id']) . '  name="resource[]" type="checkbox"  value="' . $action['id'] . '" 
                    class="'.$acceesspoints .' ' . $key . '"  id="' . $htmlid . '"  data-id="dt-' . $htmlid . '">
                    </label></li></td>';
            } else {
                $html .= '<td> </td>';

            }
        }
        $html .= '</tr>';

        $html .= '</table>';
        return $html;
    }
}
