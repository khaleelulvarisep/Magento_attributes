<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Adds products to "Main Website Store" (root: Default Category):
 * Shirts -> Classic White Shirt, Blue Denim Shirt
 * Trousers -> Slim Fit Chinos, Black Formal Trousers
 */
class CreateMainStoreProducts implements DataPatchInterface
{
    private const MAIN_ROOT_CATEGORY_ID = 2; // "Default Category"

    private const PRODUCTS = [
        'Shirts' => [
            ['sku' => 'classic-white-shirt', 'name' => 'Classic White Shirt', 'price' => 1299],
            ['sku' => 'blue-denim-shirt', 'name' => 'Blue Denim Shirt', 'price' => 1599],
        ],
        'Trousers' => [
            ['sku' => 'slim-fit-chinos', 'name' => 'Slim Fit Chinos', 'price' => 1799],
            ['sku' => 'black-formal-trousers', 'name' => 'Black Formal Trousers', 'price' => 1999],
        ],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly State $appState,
        private readonly StoreManagerInterface $storeManager,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly CategoryInterfaceFactory $categoryFactory,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ProductInterfaceFactory $productFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryLinkManagementInterface $categoryLinkManagement
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, function () {
            $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

            foreach (self::PRODUCTS as $categoryName => $products) {
                $categoryId = (int)$this->getOrCreateCategory($categoryName)->getId();
                foreach ($products as $productData) {
                    $this->saveProduct($productData, $categoryId);
                }
            }
        });
        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    private function getOrCreateCategory(string $name): CategoryInterface
    {
        $existing = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', $name)
            ->addAttributeToFilter('parent_id', self::MAIN_ROOT_CATEGORY_ID)
            ->getFirstItem();
        if ($existing->getId()) {
            return $existing;
        }

        $category = $this->categoryFactory->create();
        $category->setName($name)
            ->setParentId(self::MAIN_ROOT_CATEGORY_ID)
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
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
