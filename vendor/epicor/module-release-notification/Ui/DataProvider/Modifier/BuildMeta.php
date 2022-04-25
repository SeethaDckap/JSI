<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\ReleaseNotification\Ui\DataProvider\Modifier;

use Magento\ReleaseNotification\Ui\Renderer\NotificationRenderer;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Modal;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class BuildMeta implements ModifierInterface
{
    /**
     * @var NotificationRenderer
     */
    private $renderer;

    /**
     * BuildMeta constructor.
     *
     * @param NotificationRenderer $renderer
     */
    public function __construct(NotificationRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Modify data.
     *
     * @param array $data
     *
     * @return array|void
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Builds modal content meta.
     *
     * @param array $meta
     *
     * @return array|void
     */
    public function modifyMeta(array $meta)
    {
        $content = [];
        if ($meta) {
            $pages = $meta['pages'];
            $pageCount = count($pages);
            $counter = 1;

            foreach ($pages as $page) {
                $content = $this->buildNotificationMeta($content, $page, $counter++ == $pageCount);
            }
        } else {
            $content = $this->hideNotification($content);
        }

        return $content;
    }

    /**
     * Builds the notification modal by modifying $meta for the ui component.
     *
     * @param array $meta
     * @param array $page
     * @param bool $isLastPage
     *
     * @return array
     */
    private function buildNotificationMeta(array $meta, array $page, $isLastPage)
    {
        $meta['notification_modal_' . $page['name']]['arguments']['data']['config'] = [
            'componentType' => Modal::NAME,
            'isTemplate' => false
        ];

        $meta['notification_modal_' . $page['name']]['children']['notification_fieldset']['children']
        ['notification_text']['arguments']['data']['config'] = [
            'text' => $this->renderer->getNotificationContent($page)
        ];

        if ($isLastPage) {
            $meta['notification_modal_' . $page['name']]['arguments']['data']['config']['options'] = [
                'title' => $this->renderer->getNotificationTitle($page),
                'buttons' => [
                    [
                        'text' => 'Done',
                        'actions' => [
                            [
                                'targetName' => '${ $.name }',
                                '__disableTmpl' => false,
                                'actionName' => 'closeReleaseNotes'
                            ]
                        ],
                        'class' => 'release-notification-button-next'
                    ]
                ],
            ];

            $meta['notification_modal_' . $page['name']]['children']['notification_fieldset']['children']
            ['notification_buttons']['children']['notification_button_next']['arguments']['data']['config'] = [
                'buttonClasses' => 'hide-release-notification'
            ];
        } else {
            $meta['notification_modal_' . $page['name']]['arguments']['data']['config']['options'] = [
                'title' => $this->renderer->getNotificationTitle($page)
            ];
        }

        return $meta;
    }

    /**
     * Sets the modal to not display if no content is available.
     *
     * @param array $meta
     * @return array
     */
    private function hideNotification(array $meta)
    {
        $meta['notification_modal_1']['arguments']['data']['config']['options'] = [
            'autoOpen' => false
        ];

        return $meta;
    }
}
