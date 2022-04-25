<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

/**
 * Boost Actions for Ui Component
 *
 */
class BoostActions extends Column
{
    /**
     * Edit Url path
     **/
    const BOOST_URL_PATH_EDIT = 'ecc_elasticsearch/boost/edit';

    /**
     * Delete Url path
     **/
    const BOOST_URL_PATH_DELETE = 'ecc_elasticsearch/boost/delete';

    /**
     * Duplicate Url path
     **/
    const BOOST_URL_PATH_DUPLICATE = 'ecc_elasticsearch/boost/duplicate';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @var string
     */
    private $duplicateUrl;

    /**
     * @var string
     */
    private $deleteUrl;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     * @param string $duplicateUrl
     * @param string $deleteUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::BOOST_URL_PATH_EDIT,
        $duplicateUrl = self::BOOST_URL_PATH_DUPLICATE,
        $deleteUrl = self::BOOST_URL_PATH_DELETE
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        $this->duplicateUrl = $duplicateUrl;
        $this->deleteUrl = $deleteUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource The data source
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                if (isset($item['boost_id'])) {
                    $item[$name]['edit'] = [
                        'href'  => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['boost_id']]),
                        'label' => __('Edit'),
                    ];

                    $item[$name]['duplicate'] = [
                        'href'  => $this->urlBuilder->getUrl($this->duplicateUrl, ['id' => $item['boost_id']]),
                        'label' => __('Duplicate'),
                    ];

                    $item[$name]['delete'] = [
                        'href'    => $this->urlBuilder->getUrl($this->deleteUrl, ['id' => $item['boost_id']]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete ${ $.$data.name }'),
                            'message' => __('Are you sure you want to delete ${ $.$data.name } ?'),
                        ],
                    ];
                }
            }
        }
        return $dataSource;
    }
}
