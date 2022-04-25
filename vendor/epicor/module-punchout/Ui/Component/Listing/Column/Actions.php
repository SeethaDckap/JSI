<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Ui
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 */
class Actions extends Column
{
    const URL_PATH_EDIT    = 'epicor_punchout/connections/edit';
    const URL_PATH_DELETE  = 'epicor_punchout/connections/delete';
    const DISABLE_TEMPLATE = '__disableTmpl';

    /**
     * URL builder
     *
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Edit URL
     *
     * @var string
     */
    private $editUrl;

    /**
     * Delete URL
     *
     * @var string
     */
    private $deleteUrl;

    /**
     * Escaper.
     *
     * @var Escaper
     */
    private $escaper;


    /**
     * Constructor.
     *
     * @param ContextInterface   $context            Context interface.
     * @param UiComponentFactory $uiComponentFactory UI component factory.
     * @param UrlInterface       $urlBuilder         URL builder.
     * @param Escaper            $escaper            Escaper.
     * @param array              $components         Components array.
     * @param array              $data               Data array.
     * @param string             $editUrl            Edit URL.
     * @param string             $deleteUrl          Delete URL.
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components=[],
        array $data=[]
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl    = self::URL_PATH_EDIT;
        $this->deleteUrl  = self::URL_PATH_DELETE;
        $this->escaper    = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);

    }//end __construct()


    /**
     * Prepare data source.
     *
     * @param array $dataSource Data source.
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['edit']   = [
                        'href'                 => $this->urlBuilder->getUrl($this->editUrl, ['entity_id' => $item['entity_id']]),
                        'label'                => __('Edit'),
                        self::DISABLE_TEMPLATE => true,
                    ];
                    $title                 = $this->escaper->escapeHtml($item['connection_name']);
                    $item[$name]['delete'] = [
                        'href'                 => $this->urlBuilder->getUrl($this->deleteUrl, ['entity_id' => $item['entity_id']]),
                        'label'                => __('Delete'),
                        'confirm'              => [
                            'title'                => __('Delete %1', $title),
                            'message'              => __('Are you sure you want to delete  %1 record?', $title),
                            self::DISABLE_TEMPLATE => false,
                        ],
                        'post'                 => false,
                        self::DISABLE_TEMPLATE => false,
                    ];
                }
            }//end foreach
        }//end if

        return $dataSource;

    }//end prepareDataSource()


}//end class

