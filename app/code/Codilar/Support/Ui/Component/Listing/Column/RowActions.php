<?php
declare(strict_types=1);

namespace Codilar\Support\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Edit / Delete row actions for the support listings.
 * Configure per listing via the column's data arguments:
 *  - editUrlPath, deleteUrlPath: admin routes
 *  - titleField: row field used in the delete confirmation
 */
class RowActions extends Column
{
    private UrlInterface $urlBuilder;
    private Escaper $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $idField = $this->getData('config/indexField');
        $titleField = $this->getData('titleField');
        $name = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item[$idField])) {
                continue;
            }
            $id = $item[$idField];
            $title = $this->escaper->escapeHtml((string)($item[$titleField] ?? $id));

            $item[$name]['edit'] = [
                'href' => $this->urlBuilder->getUrl($this->getData('editUrlPath'), ['id' => $id]),
                'label' => __('Edit'),
            ];
            $item[$name]['delete'] = [
                'href' => $this->urlBuilder->getUrl($this->getData('deleteUrlPath'), ['id' => $id]),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete "%1"', $title),
                    'message' => __('Are you sure you want to delete "%1"?', $title),
                ],
                'post' => true,
            ];
        }

        return $dataSource;
    }
}
