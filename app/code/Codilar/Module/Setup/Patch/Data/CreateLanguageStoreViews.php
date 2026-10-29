<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogUrlRewrite\Model\Category\Plugin\Store\View as StoreUrlRewritePlugin;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreFactory;

/**
 * Adds a second language store view to each store and translates the catalog for it:
 * Main Website Store -> English (default) + Français (main_fr)
 * Codilar Store      -> English (codilar_en) + Deutsch (codilar_de)
 */
class CreateLanguageStoreViews implements DataPatchInterface
{
    // Existing English views: give them a language name for the language switcher
    private const ENGLISH_VIEWS = [
        'default' => 'English',
        'codilar_en' => 'English',
    ];

    private const STORE_VIEWS = [
        'main_fr' => [
            'name' => 'Français',
            'group' => 'main_website_store',
            'locale' => 'fr_FR',
            'welcome' => 'Bienvenue dans notre boutique !',
            'categories' => [
                'Shirts' => 'Chemises',
                'Trousers' => 'Pantalons',
            ],
            'products' => [
                'New Shirt' => 'Nouvelle chemise',
                'classic-white-shirt' => 'Chemise blanche classique',
                'blue-denim-shirt' => 'Chemise en jean bleue',
                'slim-fit-chinos' => 'Chino coupe ajustée',
                'black-formal-trousers' => 'Pantalon habillé noir',
            ],
        ],
        'codilar_de' => [
            'name' => 'Deutsch',
            'group' => 'codilar_store',
            'locale' => 'de_DE',
            'welcome' => 'Willkommen im Codilar Store!',
            'categories' => [
                'Electronics' => 'Elektronik',
                'Accessories' => 'Zubehör',
            ],
            'products' => [
                'codilar-headphones' => 'Codilar Kabellose Kopfhörer',
                'codilar-phone-case' => 'Codilar Handyhülle',
            ],
        ],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly State $appState,
        private readonly GroupFactory $groupFactory,
        private readonly GroupResource $groupResource,
        private readonly StoreFactory $storeFactory,
        private readonly StoreResource $storeResource,
        private readonly WriterInterface $configWriter,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly CategoryResource $categoryResource,
        private readonly ProductResource $productResource,
        private readonly ProductAction $productAction,
        private readonly StoreUrlRewritePlugin $storeUrlRewritePlugin
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, function () {
            foreach (self::ENGLISH_VIEWS as $code => $name) {
                $store = $this->storeFactory->create();
                $this->storeResource->load($store, $code, 'code');
                if ($store->getId()) {
                    $store->setName($name);
                    $this->storeResource->save($store);
                }
            }

            foreach (self::STORE_VIEWS as $code => $data) {
                $storeId = $this->createStoreView($code, $data);
                $this->configWriter->save('general/locale/code', $data['locale'], ScopeInterface::SCOPE_STORES, $storeId);
                $this->configWriter->save('design/header/welcome', $data['welcome'], ScopeInterface::SCOPE_STORES, $storeId);
                $this->translateCategories($data['categories'], $storeId);
                $this->translateProducts($data['products'], $storeId);
            }
        });
        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    private function createStoreView(string $code, array $data): int
    {
        $store = $this->storeFactory->create();
        $this->storeResource->load($store, $code, 'code');
        if ($store->getId()) {
            return (int)$store->getId();
        }

        $group = $this->groupFactory->create();
        $this->groupResource->load($group, $data['group'], 'code');

        // Saving through the resource model dispatches "store_add" (sales sequence tables)
        $store->setCode($code)
            ->setName($data['name'])
            ->setWebsiteId((int)$group->getWebsiteId())
            ->setGroupId((int)$group->getId())
            ->setSortOrder(10)
            ->setIsActive(1);

        // The plugin that generates category/product URLs for a new store view is only
        // registered for adminhtml, so run it explicitly around the save
        $this->storeUrlRewritePlugin->beforeSave($this->storeResource, $store);
        $this->storeResource->save($store);
        $this->storeUrlRewritePlugin->afterSave($this->storeResource, $this->storeResource);

        return (int)$store->getId();
    }

    private function translateCategories(array $names, int $storeId): void
    {
        foreach ($names as $name => $translation) {
            /** @var Category $category */
            $category = $this->categoryCollectionFactory->create()
                ->addAttributeToFilter('name', $name)
                ->getFirstItem();
            if (!$category->getId()) {
                continue;
            }
            // Only the name gets a store view value; url_key stays shared
            $category->setStoreId($storeId)->setName($translation);
            $this->categoryResource->saveAttribute($category, 'name');
        }
    }

    private function translateProducts(array $names, int $storeId): void
    {
        foreach ($names as $sku => $translation) {
            $productId = $this->productResource->getIdBySku($sku);
            if ($productId) {
                $this->productAction->updateAttributes([(int)$productId], ['name' => $translation], $storeId);
            }
        }
    }

    public static function getDependencies(): array
    {
        return [
            CreateCodilarCatalog::class,
            CreateMainStoreProducts::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
