<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Request;

use Codilar\Support\Model\Request;
use Codilar\Support\Model\Request\DataProvider;
use Codilar\Support\Model\Request\Validator;
use Codilar\Support\Model\RequestFactory;
use Codilar\Support\Model\ResourceModel\Request as RequestResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Saves a support request (POST only; form key is checked by the backend request validator)
 */
class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::request_save';

    private const EDITABLE_FIELDS = ['customer_name', 'email', 'subject', 'message', 'status'];

    private RequestFactory $requestFactory;
    private RequestResource $requestResource;
    private Validator $validator;
    private DataPersistorInterface $dataPersistor;

    public function __construct(
        Context $context,
        RequestFactory $requestFactory,
        RequestResource $requestResource,
        Validator $validator,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->requestFactory = $requestFactory;
        $this->requestResource = $requestResource;
        $this->validator = $validator;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id = (int)($data['request_id'] ?? 0);
        /** @var Request $request */
        $request = $this->requestFactory->create();
        if ($id) {
            $this->requestResource->load($request, $id);
            if (!$request->getId()) {
                $this->messageManager->addErrorMessage(__('This support request no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }
        $originalStatus = $id ? (string)$request->getData('status') : null;

        foreach (self::EDITABLE_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $request->setData($field, trim((string)$data[$field]));
            }
        }
        if (!$request->getData('status')) {
            $request->setData('status', Request::DEFAULT_STATUS);
        }

        $errors = $this->validator->validate($request, $originalStatus);
        if ($errors) {
            foreach ($errors as $error) {
                $this->messageManager->addErrorMessage($error);
            }
            return $this->redirectToForm($resultRedirect, $data, $id);
        }

        try {
            $this->requestResource->save($request);
            $this->messageManager->addSuccessMessage(__('The support request has been saved.'));
            $this->dataPersistor->clear(DataProvider::PERSISTOR_KEY);

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $request->getId()]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving the support request.')
            );
        }

        return $this->redirectToForm($resultRedirect, $data, $id);
    }

    private function redirectToForm(Redirect $resultRedirect, array $data, int $id): Redirect
    {
        $this->dataPersistor->set(DataProvider::PERSISTOR_KEY, $data);

        return $id
            ? $resultRedirect->setPath('*/*/edit', ['id' => $id])
            : $resultRedirect->setPath('*/*/new');
    }
}
