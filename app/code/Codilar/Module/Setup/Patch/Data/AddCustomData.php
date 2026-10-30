<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Codilar\Module\Model\CustomDataFactory;
use Codilar\Module\Model\ResourceModel\CustomData as CustomDataResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomData implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private CustomDataFactory $customDataFactory;
    private CustomDataResource $customDataResource;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomDataFactory $customDataFactory,
        CustomDataResource $customDataResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customDataFactory = $customDataFactory;
        $this->customDataResource = $customDataResource;
    }

    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        // Option 1: bulk insert directly with the DB adapter
        $connection->insertMultiple(
            $this->moduleDataSetup->getTable('codilar_custom_data'),
            [
                ['name' => 'John Doe', 'email' => 'john@example.com', 'status' => 1],
                ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 1],
            ]
        );

        
        // Option 2: save a single row through the model + resource model
        $customData = $this->customDataFactory->create();
        $customData->setData([
            'name' => 'Sam Wilson',
            'email' => 'sam@example.com',
            'status' => 0,
        ]);
        $this->customDataResource->save($customData);

        $connection->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
