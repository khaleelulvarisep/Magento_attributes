<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Codilar\Module\Model\CustomDataManager;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCustomData implements DataPatchInterface
{
    public function __construct(
        private CustomDataManager $customDataManager
    ) {
    }

    public function apply(): void
    {
        // Activate Sam Wilson (entity_id 3) and change the name
        $this->customDataManager->update(3, [
            'name' => 'khaleel',
            'status' => 1,
        ]);
    }

    public static function getDependencies(): array
    {
        return [AddCustomData::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
