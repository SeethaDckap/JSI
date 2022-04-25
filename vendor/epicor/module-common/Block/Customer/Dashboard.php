<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Customer;


/**
 * Dashboard
 */
class Dashboard extends \Epicor\Common\Block\Generic\Listing
{
    protected $dashboardSection = 'all';

    protected $sectionController = 'empty';

    protected $sectionBlockGroup = '';

    protected $sectionHeaderText = 'Generic List';

    protected $viewAllUrlParam = '';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

    protected function _setupGrid()
    {
        $this->_controller = $this->sectionController;
        $this->_blockGroup = $this->sectionBlockGroup;
        $this->_headerText = __($this->sectionHeaderText);
    }

    public function toHtml()
    {
        if(!$this->_isAllowed() || !$this->isSectionEnabled()) {
            if(static::ACCESS_MESSAGE_DISPLAY){
                return $this->_accessauthorization->getMessage();
            }
            return '';
        }
        return parent::toHtml();
    }

    public function getHeaderHtml()
    {
        $html = parent::getHeaderHtml();
        $urlParams = explode('/', $this->viewAllUrlParam);
        if (count($urlParams) > 1) {
            $url = $this->viewAllUrlParam;
        } else {
            $url = '*/' . $this->viewAllUrlParam . '/';
        }
        $html .= '<a class="view_all" href="' . $this->getUrl($url) . '">' . __('View All') . '</a>';

        return $html;
    }

    public function isSectionEnabled()
    {
        $dashboardConfiguration = $this->getDashboardConfiguration();
        if (isset($dashboardConfiguration[$this->dashboardSection])
            && isset($dashboardConfiguration[$this->dashboardSection]['status'])
            && $dashboardConfiguration[$this->dashboardSection]['status'] == 1
        ) {
            return true;
        }
        return false;
    }

    public function getDashboardConfiguration()
    {
        return $this->registry->registry('dashboard_configuration');
    }
}
