<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Support Requests grid page
 */
class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::requests';

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
        $resultPage->setActiveMenu('Codilar_Support::requests');
        $resultPage->addBreadcrumb(__('Support Management'), __('Support Management'));
        $resultPage->addBreadcrumb(__('Support Requests'), __('Support Requests'));
        $resultPage->getConfig()->getTitle()->prepend(__('Support Requests'));

        return $resultPage;
    }
}
