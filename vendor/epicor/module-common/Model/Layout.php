<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Design;
use Psr\Log\LoggerInterface as Logger;


/**
 * Description of Layout
 *
 * @author David.Wylie
 */
class Layout extends \Magento\Framework\View\Layout
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Layout\ProcessorFactory $processorFactory,
        ManagerInterface $eventManager,
        \Magento\Framework\View\Layout\Data\Structure  $structure,
        MessageManagerInterface $messageManager,
        Design\Theme\ResolverInterface $themeResolver,
        \Magento\Framework\View\Layout\ReaderPool $readerPool,
        \Magento\Framework\View\Layout\GeneratorPool $generatorPool,
        FrontendInterface $cache,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Layout\Reader\ContextFactory $readerContextFactory,
        \Magento\Framework\View\Layout\Generator\ContextFactory $generatorContextFactory,
        AppState $appState,
        Logger $logger,
        $cacheable = true)
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($processorFactory, $eventManager, $structure, $messageManager, $themeResolver, $readerPool, $generatorPool, $cache, $readerContextFactory, $generatorContextFactory, $appState, $logger, $cacheable);
    }


    /**
     * Enter description here...
     *
     * @param \Magento\Framework\Simplexml\Element $node
     * @param \Magento\Framework\Simplexml\Element $parent
     * @return \Magento\Framework\View\LayoutInterface
     */
    protected function _generateAction($node, $parent)
    {
        if (isset($node['ifnotconfig']) && ($configPath = (string) $node['ifnotconfig'])) {
            if ($this->scopeConfig->isSetFlag($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                return $this;
            }
        }
        return parent::_generateAction($node, $parent);
    }

}
