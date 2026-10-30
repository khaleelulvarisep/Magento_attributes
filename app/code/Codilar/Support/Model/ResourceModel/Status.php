<?php
declare(strict_types=1);

namespace Codilar\Support\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Status extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('codilar_support_status', 'status_id');
    }
}
