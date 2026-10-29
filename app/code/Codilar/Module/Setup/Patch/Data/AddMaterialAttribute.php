<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddMaterialAttribute implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(Product::ENTITY, 'material', [
            'type' => 'varchar',
            'label' => 'Material',
            'input' => 'text',
            'required' => false,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'user_defined' => true,
            'searchable' => true,
            'filterable' => false,
            'comparable' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'group' => 'General',
        ]);

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
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
