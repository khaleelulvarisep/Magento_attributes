<?php
declare(strict_types=1);

namespace Codilar\Module\Model;

use Codilar\Module\Model\ResourceModel\CustomData as CustomDataResource;
use Magento\Framework\Model\AbstractModel;

class CustomData extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(CustomDataResource::class);
    }
}
