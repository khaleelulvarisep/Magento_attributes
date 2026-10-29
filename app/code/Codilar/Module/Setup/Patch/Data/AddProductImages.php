<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Adds a base/small/thumbnail image to the products of both stores.
 * Images live in Codilar/Module/files/product-images and are named after the SKU.
 */
class AddProductImages implements DataPatchInterface
{
    private const IMAGE_DIR = 'files/product-images';
    private const MEDIA_IMPORT_DIR = 'import/codilar';

    private const IMAGES = [
        // Main Website Store
        'New Shirt' => 'new-shirt.jpg',
        'classic-white-shirt' => 'classic-white-shirt.jpg',
        'blue-denim-shirt' => 'blue-denim-shirt.jpg',
        'slim-fit-chinos' => 'slim-fit-chinos.jpg',
        'black-formal-trousers' => 'black-formal-trousers.jpg',
        // Codilar Store
        'codilar-headphones' => 'codilar-headphones.jpg',
        'codilar-phone-case' => 'codilar-phone-case.jpg',
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly State $appState,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly Filesystem $filesystem,
        private readonly File $fileDriver,
        private readonly ModuleDirReader $moduleDirReader
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, function () {
            $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

            foreach (self::IMAGES as $sku => $fileName) {
                $this->addImage($sku, $fileName);
            }
        });
        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    private function addImage(string $sku, string $fileName): void
    {
        try {
            $product = $this->productRepository->get($sku, true, Store::DEFAULT_STORE_ID);
        } catch (NoSuchEntityException) {
            return; // product not present in this environment
        }
        if ($product->getMediaGalleryEntries()) {
            return; // already has images
        }

        // The gallery only accepts files inside pub/media, so copy the image there first
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $source = $this->moduleDirReader->getModuleDir('', 'Codilar_Module') . '/' . self::IMAGE_DIR . '/' . $fileName;
        $target = self::MEDIA_IMPORT_DIR . '/' . $fileName;
        $mediaDirectory->writeFile($target, $this->fileDriver->fileGetContents($source));

        $product->addImageToMediaGallery(
            $mediaDirectory->getAbsolutePath($target),
            ['image', 'small_image', 'thumbnail'],
            true, // move: removes the copy from pub/media/import
            false // not excluded from the gallery
        );
        $this->productRepository->save($product);
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
