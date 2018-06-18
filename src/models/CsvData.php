<?php

namespace dastin\csv\models;

use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;

class CsvData extends BaseDataProvider
{
    /**
     * @var string name of the CSV file to read
     */
    public $filename;
    
    /**
     * @var string|callable name of the key row
     */
    public $key;
    
    /**
     * @var SplFileObject
     */
    protected $fileObject; // SplFileObject is very convenient for seeking to particular line in a file

    /**
     * @var array names of the key cols
     */
    private $_colNames;  

    /**
     * @var array filter parameters
     */
    private $_filterArray;  
 
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->fileObject = new \SplFileObject($this->filename);
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        $models = [];
        $this->fileObject->rewind();
        $this->_colNames = $this->fileObject->fgetcsv();
        $this->fileObject->next();

        if ($this->key !== null) {
            $this->fileObject->seek($this->key);
            if ($this->fileObject->ftell() === 0) {
                $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
            $csvarr = $this->fileObject->fgetcsv();
            $models = array_combine($this->_colNames, $csvarr);

            return $models;
        }

        for ($count = 0; $this->fileObject->valid(); ++$count) {
            $csvarr = $this->fileObject->fgetcsv();
            $this->fileObject->next();
            if ($csvarr === [null]) continue;
            $models[] = array_combine($this->_colNames, $csvarr) + [$count];
        }
        $this->sort = ['attributes' => $this->_colNames];
        if (($sort = $this->getSort()) !== false) {
            $models = $this->sortModels($models, $sort);
        }
        $count = $this->filter($models);
        $pagination = $this->getPagination();
        $pagination->totalCount = $count;
        if ($pagination->getPageSize() > 0) {
            $models = array_slice($models, $pagination->getOffset(), $pagination->getLimit(), true);
        }

        return $models;
    }

    /**
     * Set filter to the model data
     * @param array $models the Models data
     * @return int the row count after set filter
     */
    private function filter(&$models)
    {
        if ($this->_filterArray === null) return count($models);

        foreach ($this->_filterArray as $key => $value) {
            if ($value) {
                foreach ($models as $index => $data) {
                    if (stripos($models[$index][$key], $value) === false)
                        unset($models[$index]);
                }
            }
        }

        return count($models);
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) return [$this->key];
        $keys = array_keys($models);
        $pagination = $this->getPagination()->getOffset() + 1;
        array_walk($keys, function(&$value) use (&$pagination) {
            $value += $pagination;
        });
        return $keys;
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        $count = 0;

        $this->fileObject->rewind();
        while ($this->fileObject->valid()) {
            $this->fileObject->next();
            $this->fileObject->current();
            ++$count;
        }

        return $count-1;
    }

    public function getColNames()
    {
        return $this->_colNames;
    }

    /**
     * Read all file
     * @return array strings from file
     */
    private function getFile()
    {
        $strings = [];
        $this->fileObject->rewind();
        while ($this->fileObject->valid()) {
            $strings[] = $this->fileObject->fgets();
        }

        return $strings;
    }

    /**
     * Update the string in the file 
     * @param int $id the updatable string
     * @param array $str the new data
     * @return bool 
     */
    public function save($id, $str)
    {
        $strings = $this->getFile();
        $file = new \SplFileObject($this->filename, 'w');
        for ($i = 0; $i <= $id; ++$i)
            $file->fwrite($strings[$i]);
        $file->fputcsv($str['DynamicModel']);
        for ($i = $id + 2; $i < count($strings); ++$i)
            $file->fwrite($strings[$i]);

        return true;
    }

    /**
     * Delete the string from the file 
     * @param int $id of deleting string
     * @return bool 
     */
    public function delete($id)
    {
        $strings = $this->getFile();
        $file = new \SplFileObject($this->filename, 'w');
        for ($i = 0; $i <= $id; ++$i)
            $file->fwrite($strings[$i]);
        for ($i = $id + 2; $i < count($strings); ++$i)
            $file->fwrite($strings[$i]);

        return true;
    }

    public function setSearch($params)
    {
        if (isset($params['DynamicModel']))
            $this->_filterArray = $params['DynamicModel'];

        return $this;
    }

    /**
     * Sorts the data models according to the given sort definition.
     * @param array $models the models to be sorted
     * @param Sort $sort the sort definition
     * @return array the sorted data models
     */
    protected function sortModels($models, $sort)
    {
        $orders = $sort->getOrders();
        if (!empty($orders)) {
            ArrayHelper::multisort($models, array_keys($orders), array_values($orders));
        }

        return $models;
    }
}

?>