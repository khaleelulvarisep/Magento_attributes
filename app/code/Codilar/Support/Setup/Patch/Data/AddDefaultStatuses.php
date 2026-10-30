<?php
declare(strict_types=1);

namespace Codilar\Support\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddDefaultStatuses implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $connection->insertOnDuplicate(
            $this->moduleDataSetup->getTable('codilar_support_status'),
            [
                ['code' => 'pending', 'label' => 'Pending', 'sort_order' => 10, 'is_active' => 1],
                ['code' => 'processing', 'label' => 'Processing', 'sort_order' => 20, 'is_active' => 1],
                ['code' => 'completed', 'label' => 'Completed', 'sort_order' => 30, 'is_active' => 1],
            ],
            ['label', 'sort_order', 'is_active']
        );

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
