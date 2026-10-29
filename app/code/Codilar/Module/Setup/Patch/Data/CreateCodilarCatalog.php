<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Gives "Codilar Store" its own catalog:
 * Codilar Root -> Electronics, Accessories (+ one sample product each)
 */
class CreateCodilarCatalog implements DataPatchInterface
{
    private const GROUP_CODE = 'codilar_store';
    private const TREE_ROOT_ID = 1; // "Root Catalog", parent of all root categories

    private const CATEGORIES = [
        'Electronics' => [
            'sku' => 'codilar-headphones',
            'name' => 'Codilar Wireless Headphones',
            'price' => 2499,
        ],
        'Accessories' => [
            'sku' => 'codilar-phone-case',
            'name' => 'Codilar Phone Case',
            'price' => 399,
        ],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly State $appState,
        private readonly StoreManagerInterface $storeManager,
        private readonly CategoryInterfaceFactory $categoryFactory,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ProductInterfaceFactory $productFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryLinkManagementInterface $categoryLinkManagement,
        private readonly GroupFactory $groupFactory,
        private readonly GroupResource $groupResource
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, function () {
            $this->createCatalog();
        });
        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    private function createCatalog(): void
    {
        // Save at admin (default) scope
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

        $root = $this->saveCategory('Codilar Root', self::TREE_ROOT_ID);

        // Point the store (group) at the new root category first, so the
        // subcategories below get URL rewrites for the Codilar store view
        $group = $this->groupFactory->create();
        $this->groupResource->load($group, self::GROUP_CODE, 'code');
        $group->setRootCategoryId((int)$root->getId());
        $this->groupResource->save($group);

        foreach (self::CATEGORIES as $categoryName => $productData) {
            $category = $this->saveCategory($categoryName, (int)$root->getId());
            $this->saveProduct($productData, (int)$category->getId());
        }
    }

    private function saveCategory(string $name, int $parentId)
    {
        $category = $this->categoryFactory->create();
        $category->setName($name)
            ->setParentId($parentId)
            ->setIsActive(true)
            ->setIncludeInMenu(true);

        return $this->categoryRepository->save($category);
    }

    private function saveProduct(array $data, int $categoryId): void
    {
        $product = $this->productFactory->create();
        $product->setSku($data['sku'])
            ->setName($data['name'])
            ->setPrice($data['price'])
            ->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId(4) // "Default"
            ->setStatus(Status::STATUS_ENABLED)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setWebsiteIds([1]) // Main Website
            ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'use_config_manage_stock' => 1]);
        $this->productRepository->save($product);

        $this->categoryLinkManagement->assignProductToCategories($data['sku'], [$categoryId]);
    }

    public static function getDependencies(): array
    {
        return [CreateCodilarStore::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
