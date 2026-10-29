<?php
declare(strict_types=1);

namespace Codilar\Module\Model;

use Codilar\Module\Model\ResourceModel\CustomData as CustomDataResource;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomDataManager
{
    public function __construct(
        private CustomDataFactory $customDataFactory,
        private CustomDataResource $customDataResource
    ) {
    }

    /**
     * Update an existing row by its entity_id.
     *
     * @param int $id
     * @param array $data e.g. ['name' => 'New Name', 'status' => 1]
     * @return CustomData
     * @throws NoSuchEntityException
     */
    public function update(int $id, array $data): CustomData
    {
        $customData = $this->customDataFactory->create();
        $this->customDataResource->load($customData, $id);

        if (!$customData->getId()) {
            throw new NoSuchEntityException(__('Custom data with ID "%1" does not exist.', $id));
        }

        // Never allow the primary key to be changed through $data
        unset($data['entity_id']);

        $customData->addData($data);
        $this->customDataResource->save($customData);

        return $customData;
    }
}
