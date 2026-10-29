<?php
declare(strict_types=1);

namespace Codilar\Module\Model\ResourceModel\CustomData;

use Codilar\Module\Model\CustomData;
use Codilar\Module\Model\ResourceModel\CustomData as CustomDataResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(CustomData::class, CustomDataResource::class);
    }
}
