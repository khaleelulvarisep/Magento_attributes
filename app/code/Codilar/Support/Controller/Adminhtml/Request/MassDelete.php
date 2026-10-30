<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Request;

use Codilar\Support\Model\ResourceModel\Request as RequestResource;
use Codilar\Support\Model\ResourceModel\Request\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::request_delete';

    private Filter $filter;
    private CollectionFactory $collectionFactory;
    private RequestResource $requestResource;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        RequestResource $requestResource
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->requestResource = $requestResource;
    }

    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        foreach ($collection as $request) {
            $this->requestResource->delete($request);
            $deleted++;
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $deleted));

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
