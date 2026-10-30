<?php
declare(strict_types=1);

namespace Codilar\Support\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;

/**
 * Shared helpers for the support form buttons. URLs are relative to the current
 * controller, so the same buttons work for both the request and the status form.
 */
class GenericButton
{
    protected Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function getEntityId(): int
    {
        return (int)$this->context->getRequest()->getParam('id');
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
