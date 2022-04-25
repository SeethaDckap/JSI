<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Standard Magento block.
 * Should be used when you declare a block in frontend area layout handle.
 *
 * Avoid extending this class.
 *
 * If you need custom presentation logic in your blocks, use this class as block, and declare
 * custom view models in block arguments in layout handle file.
 *
 * Example:
 * <block name="my.block" class="Magento\Backend\Block\Template" template="My_Module::template.phtml" >
 *      <arguments>
 *          <argument name="viewModel" xsi:type="object">My\Module\ViewModel\Custom</argument>
 *      </arguments>
 * </block>
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Template extends \Magento\Framework\View\Element\Template
{

    const FRONTEND_RESOURCE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_CREATE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_EDIT = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_authorization;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        $this->_authorization = $context->getAccessAuthorization();
        parent::__construct($context, $data);
    }


    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_isAccessAllowed(static::FRONTEND_RESOURCE);
    }

    public function _isAccessAllowed($code)
    {
        return $this->_authorization->isAllowed($code);
    }

    public function _isFormAccessAllowed()
    {
        $allowed = true;
        $action = $this->getRequest()->getActionName();
        switch($action) {
            case 'new':
            case 'duplicate':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE);
                break;
            case 'details':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT);
                break;
        }
        return $allowed;
    }

    public function _isCreateAllowed()
    {
        return $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE);
    }
}
