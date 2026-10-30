<?php
declare(strict_types=1);

namespace Codilar\Support\Model\Status;

use Codilar\Support\Model\ResourceModel\Status\CollectionFactory;
use Codilar\Support\Model\Status;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Supplies data to codilar_support_status_form
 */
class DataProvider extends AbstractDataProvider
{
    public const PERSISTOR_KEY = 'codilar_support_status';

    private DataPersistorInterface $dataPersistor;
    private RequestInterface $request;
    private ?array $loadedData = null;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        /** @var Status $status */
        foreach ($this->collection->getItems() as $status) {
            $this->loadedData[$status->getId()] = $status->getData();
        }

        $persisted = $this->dataPersistor->get(self::PERSISTOR_KEY);
        if (!empty($persisted)) {
            $status = $this->collection->getNewEmptyItem();
            $status->setData($persisted);
            $this->loadedData[$status->getId()] = $status->getData();
            $this->dataPersistor->clear(self::PERSISTOR_KEY);
        }

        return $this->loadedData;
    }

    /**
     * The status code is referenced by requests, so it is read-only once created
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();
        if ($this->request->getParam($this->getRequestFieldName())) {
            $meta['general']['children']['code']['arguments']['data']['config']['disabled'] = true;
        }

        return $meta;
    }
}
