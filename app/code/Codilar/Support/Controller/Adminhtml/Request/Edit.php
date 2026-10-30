<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Request;

use Codilar\Support\Model\RequestFactory;
use Codilar\Support\Model\ResourceModel\Request as RequestResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Add / edit support request form page
 */
class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::request_save';

    private PageFactory $resultPageFactory;
    private RequestFactory $requestFactory;
    private RequestResource $requestResource;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RequestFactory $requestFactory,
        RequestResource $requestResource
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->requestFactory = $requestFactory;
        $this->requestResource = $requestResource;
    }

    public function execute(): ResultInterface
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            $request = $this->requestFactory->create();
            $this->requestResource->load($request, $id);
            if (!$request->getId()) {
                $this->messageManager->addErrorMessage(__('This support request no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        // New records highlight "Add New Request"; existing ones stay under "Support Requests"
        $resultPage->setActiveMenu($id ? 'Codilar_Support::requests' : 'Codilar_Support::request_add');
        $resultPage->addBreadcrumb(__('Support Management'), __('Support Management'));
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Support Request #%1', $id) : __('Add New Request')
        );

        return $resultPage;
    }
}
