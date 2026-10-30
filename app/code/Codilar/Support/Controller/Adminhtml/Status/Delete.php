<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Status;

use Codilar\Support\Model\ResourceModel\Request as RequestResource;
use Codilar\Support\Model\ResourceModel\Status as StatusResource;
use Codilar\Support\Model\StatusFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::statuses';

    private StatusFactory $statusFactory;
    private StatusResource $statusResource;
    private RequestResource $requestResource;

    public function __construct(
        Context $context,
        StatusFactory $statusFactory,
        StatusResource $statusResource,
        RequestResource $requestResource
    ) {
        parent::__construct($context);
        $this->statusFactory = $statusFactory;
        $this->statusResource = $statusResource;
        $this->requestResource = $requestResource;
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/');
        $id = (int)$this->getRequest()->getParam('id');

        $status = $this->statusFactory->create();
        $this->statusResource->load($status, $id);
        if (!$id || !$status->getId()) {
            $this->messageManager->addErrorMessage(__('This status no longer exists.'));
            return $resultRedirect;
        }

        $inUse = $this->requestResource->countByStatus((string)$status->getData('code'));
        if ($inUse > 0) {
            $this->messageManager->addErrorMessage(__(
                'The status "%1" is used by %2 support request(s) and cannot be deleted. '
                . 'Deactivate it instead, or move those requests to another status first.',
                $status->getData('label'),
                $inUse
            ));
            return $resultRedirect;
        }

        try {
            $this->statusResource->delete($status);
            $this->messageManager->addSuccessMessage(__('The status has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the status.'));
        }

        return $resultRedirect;
    }
}
