<?php
declare(strict_types=1);

namespace Codilar\Support\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Request extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('codilar_support_request', 'request_id');
    }

    /**
     * Number of requests currently using the given status code
     */
    public function countByStatus(string $statusCode): int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['COUNT(*)'])
            ->where('status = ?', $statusCode);

        return (int)$connection->fetchOne($select);
    }
}
