<?php
declare(strict_types=1);

namespace Codilar\Support\Model;

use Codilar\Support\Model\ResourceModel\Status as StatusResource;
use Magento\Framework\Model\AbstractModel;

class Status extends AbstractModel
{
    protected $_eventPrefix = 'codilar_support_status';

    protected function _construct(): void
    {
        $this->_init(StatusResource::class);
    }
}
