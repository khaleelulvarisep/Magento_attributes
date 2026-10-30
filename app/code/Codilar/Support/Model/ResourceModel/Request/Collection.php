<?php
declare(strict_types=1);

namespace Codilar\Support\Model\ResourceModel\Request;

use Codilar\Support\Model\Request;
use Codilar\Support\Model\ResourceModel\Request as RequestResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'request_id';

    protected function _construct(): void
    {
        $this->_init(Request::class, RequestResource::class);
    }
}
