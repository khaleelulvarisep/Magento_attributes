<?php
declare(strict_types=1);

namespace Codilar\Support\Model\Request;

use Codilar\Support\Model\Request;
use Codilar\Support\Model\ResourceModel\Request\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Supplies data to codilar_support_request_form
 */
class DataProvider extends AbstractDataProvider
{
    public const PERSISTOR_KEY = 'codilar_support_request';

    private DataPersistorInterface $dataPersistor;
    private ?array $loadedData = null;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        /** @var Request $request */
        foreach ($this->collection->getItems() as $request) {
            $this->loadedData[$request->getId()] = $request->getData();
        }

        // Re-populate the form with the submitted values after a failed save
        $persisted = $this->dataPersistor->get(self::PERSISTOR_KEY);
        if (!empty($persisted)) {
            $request = $this->collection->getNewEmptyItem();
            $request->setData($persisted);
            $this->loadedData[$request->getId()] = $request->getData();
            $this->dataPersistor->clear(self::PERSISTOR_KEY);
        }

        return $this->loadedData;
    }
}
