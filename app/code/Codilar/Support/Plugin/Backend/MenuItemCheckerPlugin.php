<?php
declare(strict_types=1);

namespace Codilar\Support\Plugin\Backend;

use Magento\Backend\Block\MenuItemChecker;
use Magento\Backend\Model\Menu\Item;

/**
 * Core only marks level-0 menu items as active. This also marks the current
 * Support Management submenu item, so its link can be highlighted.
 */
class MenuItemCheckerPlugin
{
    private const MENU_PREFIX = 'Codilar_Support::';

    /**
     * @param MenuItemChecker $subject
     * @param bool $result
     * @param Item|false|null $activeItem
     * @param Item $item
     * @param int $level
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsItemActive(
        MenuItemChecker $subject,
        $result,
        $activeItem,
        Item $item,
        $level
    ): bool {
        if ($result || (int)$level === 0 || !$activeItem instanceof Item) {
            return (bool)$result;
        }

        return str_starts_with($item->getId(), self::MENU_PREFIX)
            && $activeItem->getId() === $item->getId();
    }
}
