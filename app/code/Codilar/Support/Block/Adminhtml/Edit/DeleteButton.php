<?php
declare(strict_types=1);

namespace Codilar\Support\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        if (!$this->getEntityId()) {
            return [];
        }

        // {"data": {}} makes deleteConfirm() submit a POST with the form key
        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => sprintf(
                "deleteConfirm('%s', '%s', {\"data\": {}})",
                __('Are you sure you want to delete this record?'),
                $this->getUrl('*/*/delete', ['id' => $this->getEntityId()])
            ),
            'sort_order' => 20,
        ];
    }
}
