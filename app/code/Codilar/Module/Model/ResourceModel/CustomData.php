<?php
declare(strict_types=1);

namespace Codilar\Module\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomData extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('codilar_custom_data', 'entity_id');
    }
}
