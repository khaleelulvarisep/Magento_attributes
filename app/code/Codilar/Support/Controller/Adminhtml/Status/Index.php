<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Status;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Manage Statuses grid page
 */
class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::statuses';

    private PageFactory $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codilar_Support::statuses');
        $resultPage->addBreadcrumb(__('Support Management'), __('Support Management'));
        $resultPage->addBreadcrumb(__('Manage Statuses'), __('Manage Statuses'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Statuses'));

        return $resultPage;
    }
}
