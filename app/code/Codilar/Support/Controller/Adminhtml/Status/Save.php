<?php
declare(strict_types=1);

namespace Codilar\Support\Controller\Adminhtml\Status;

use Codilar\Support\Model\ResourceModel\Status as StatusResource;
use Codilar\Support\Model\Status;
use Codilar\Support\Model\Status\DataProvider;
use Codilar\Support\Model\StatusFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Support::statuses';

    private const CODE_PATTERN = '/^[a-z][a-z0-9_]{0,31}$/';

    private StatusFactory $statusFactory;
    private StatusResource $statusResource;
    private DataPersistorInterface $dataPersistor;

    public function __construct(
        Context $context,
        StatusFactory $statusFactory,
        StatusResource $statusResource,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->statusFactory = $statusFactory;
        $this->statusResource = $statusResource;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id = (int)($data['status_id'] ?? 0);
        /** @var Status $status */
        $status = $this->statusFactory->create();
        if ($id) {
            $this->statusResource->load($status, $id);
            if (!$status->getId()) {
                $this->messageManager->addErrorMessage(__('This status no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            // The code is only set on creation; requests reference it, so it never changes afterwards
            $status->setData('code', strtolower(trim((string)($data['code'] ?? ''))));
        }
        $status->setData('label', trim((string)($data['label'] ?? '')));
        $status->setData('sort_order', max(0, (int)($data['sort_order'] ?? 0)));
        $status->setData('is_active', (int)!empty($data['is_active']));

        try {
            $this->validate($status);
            $this->statusResource->save($status);
            $this->messageManager->addSuccessMessage(__('The status has been saved.'));
            $this->dataPersistor->clear(DataProvider::PERSISTOR_KEY);

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $status->getId()]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the status.'));
        }

        $this->dataPersistor->set(DataProvider::PERSISTOR_KEY, $data);
        return $id
            ? $resultRedirect->setPath('*/*/edit', ['id' => $id])
            : $resultRedirect->setPath('*/*/new');
    }

    /**
     * @throws LocalizedException
     */
    private function validate(Status $status): void
    {
        $code = (string)$status->getData('code');
        if (!preg_match(self::CODE_PATTERN, $code)) {
            throw new LocalizedException(__(
                'Status code must start with a letter and contain only lowercase letters, numbers '
                . 'and underscores (max 32 characters).'
            ));
        }
        $label = (string)$status->getData('label');
        if ($label === '' || mb_strlen($label) > 255) {
            throw new LocalizedException(__('Status label is required and cannot exceed 255 characters.'));
        }

        if (!$status->getId()) {
            $existing = $this->statusFactory->create();
            $this->statusResource->load($existing, $code, 'code');
            if ($existing->getId()) {
                throw new LocalizedException(__('A status with code "%1" already exists.', $code));
            }
        }
    }
}
