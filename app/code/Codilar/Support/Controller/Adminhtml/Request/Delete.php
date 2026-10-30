<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Request;

use Codilar\Support\Model\RequestFactory;
use Codilar\Support\Model\ResourceModel\Request as RequestResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::request_delete';

    private RequestFactory $requestFactory;
    private RequestResource $requestResource;

    public function __construct(
        Context $context,
        RequestFactory $requestFactory,
        RequestResource $requestResource
    ) {
        parent::__construct($context);
        $this->requestFactory = $requestFactory;
        $this->requestResource = $requestResource;
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/');
        $id = (int)$this->getRequest()->getParam('id');

        $request = $this->requestFactory->create();
        $this->requestResource->load($request, $id);
        if (!$id || !$request->getId()) {
            $this->messageManager->addErrorMessage(__('This support request no longer exists.'));
            return $resultRedirect;
        }

        try {
            $this->requestResource->delete($request);
            $this->messageManager->addSuccessMessage(__('The support request has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while deleting the support request.')
            );
        }

        return $resultRedirect;
    }
}
