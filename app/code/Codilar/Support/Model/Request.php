<?php
declare(strict_types=1);

namespace Codilar\Support\Model;

use Codilar\Support\Model\ResourceModel\Request as RequestResource;
use Magento\Framework\Model\AbstractModel;

class Request extends AbstractModel
{
    public const DEFAULT_STATUS = 'pending';

    protected $_eventPrefix = 'codilar_support_request';

    protected function _construct(): void
    {
        $this->_init(RequestResource::class);
    }
}
