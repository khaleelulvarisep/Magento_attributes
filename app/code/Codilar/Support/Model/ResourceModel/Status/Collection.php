<?php
declare(strict_types=1);

namespace Codilar\Support\Model\ResourceModel\Status;

use Codilar\Support\Model\ResourceModel\Status as StatusResource;
use Codilar\Support\Model\Status;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'status_id';

    protected function _construct(): void
    {
        $this->_init(Status::class, StatusResource::class);
    }
}
