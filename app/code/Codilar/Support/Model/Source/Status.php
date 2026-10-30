<?php
declare(strict_types=1);

namespace Codilar\Support\Model\Source;

use Codilar\Support\Model\ResourceModel\Status\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Request status options, read from codilar_support_status.
 * The ActiveStatus virtual type (etc/di.xml) sets $onlyActive = true for the request form.
 */
class Status implements OptionSourceInterface
{
    private CollectionFactory $collectionFactory;
    private bool $onlyActive;
    private ?array $options = null;

    public function __construct(CollectionFactory $collectionFactory, bool $onlyActive = false)
    {
        $this->collectionFactory = $collectionFactory;
        $this->onlyActive = $onlyActive;
    }

    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $collection = $this->collectionFactory->create()
                ->setOrder('sort_order', 'ASC')
                ->setOrder('label', 'ASC');
            if ($this->onlyActive) {
                $collection->addFieldToFilter('is_active', 1);
            }

            $this->options = [];
            foreach ($collection as $status) {
                $this->options[] = [
                    'value' => $status->getData('code'),
                    'label' => $status->getData('label'),
                ];
            }
        }

        return $this->options;
    }
}
