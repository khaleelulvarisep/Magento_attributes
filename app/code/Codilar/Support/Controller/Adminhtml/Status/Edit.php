<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Status;

use Codilar\Support\Model\ResourceModel\Status as StatusResource;
use Codilar\Support\Model\StatusFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::statuses';

    private PageFactory $resultPageFactory;
    private StatusFactory $statusFactory;
    private StatusResource $statusResource;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        StatusFactory $statusFactory,
        StatusResource $statusResource
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->statusFactory = $statusFactory;
        $this->statusResource = $statusResource;
    }

    public function execute(): ResultInterface
    {
        $id = (int)$this->getRequest()->getParam('id');
        $status = $this->statusFactory->create();
        if ($id) {
            $this->statusResource->load($status, $id);
            if (!$status->getId()) {
                $this->messageManager->addErrorMessage(__('This status no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codilar_Support::statuses');
        $resultPage->addBreadcrumb(__('Support Management'), __('Support Management'));
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Status "%1"', $status->getData('label')) : __('New Status')
        );

        return $resultPage;
    }
}
