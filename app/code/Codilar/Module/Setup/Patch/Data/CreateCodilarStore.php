<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

/**
 * Creates Store (group) -> Store View under the existing Main Website:
 * Main Website (base) -> Codilar Store -> Codilar English (codilar_en)
 */
class CreateCodilarStore implements DataPatchInterface
{
    private const WEBSITE_CODE = 'base';
    private const GROUP_CODE = 'codilar_store';
    private const STORE_VIEW_CODE = 'codilar_en';
    private const ROOT_CATEGORY_ID = 2; // "Default Category"

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly WebsiteFactory $websiteFactory,
        private readonly WebsiteResource $websiteResource,
        private readonly GroupFactory $groupFactory,
        private readonly GroupResource $groupResource,
        private readonly StoreFactory $storeFactory,
        private readonly StoreResource $storeResource
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $website = $this->websiteFactory->create();
        $this->websiteResource->load($website, self::WEBSITE_CODE, 'code');

        // 1. Store (group) - holds the root category
        $group = $this->groupFactory->create();
        $this->groupResource->load($group, self::GROUP_CODE, 'code');
        if (!$group->getId()) {
            $group->setCode(self::GROUP_CODE)
                ->setName('Codilar Store')
                ->setWebsiteId((int)$website->getId())
                ->setRootCategoryId(self::ROOT_CATEGORY_ID);
            $this->groupResource->save($group);
        }

        // 2. Store View - saving through the resource model dispatches "store_add",
        // which creates the sales sequence tables for the new view
        $store = $this->storeFactory->create();
        $this->storeResource->load($store, self::STORE_VIEW_CODE, 'code');
        if (!$store->getId()) {
            $store->setCode(self::STORE_VIEW_CODE)
                ->setName('Codilar English')
                ->setWebsiteId((int)$website->getId())
                ->setGroupId((int)$group->getId())
                ->setSortOrder(0)
                ->setIsActive(1);
            $this->storeResource->save($store);
        }

        $group->setDefaultStoreId((int)$store->getId());
        $this->groupResource->save($group);

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
