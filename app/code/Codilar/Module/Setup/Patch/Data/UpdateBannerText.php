<?php
declare(strict_types=1);

namespace Codilar\Module\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateBannerText implements DataPatchInterface
{
    public function __construct(
        private EavConfig $eavConfig
    ) {
    }

    public function apply(): void
    {
        $attribute = $this->eavConfig->getAttribute(
            Category::ENTITY,
            'banner_text'
        );

        $attribute->setData('is_user_defined', 1);
        $attribute->setData('is_visible', 1);
        $attribute->setData('frontend_label', 'Banner Text');
        $attribute->setData('is_required', 0);
        $attribute->save();
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
