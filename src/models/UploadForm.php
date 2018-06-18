<?php

namespace dastin\csv\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

class UploadForm extends Model
{
    const FILE_NAME = 'csv.csv';
    public $csvFile;

    public function rules()
    {
        return [
            [['csvFile'], 'file', 'skipOnEmpty' => false],
        ];
    }
    
    public function upload()
    {
        if ($this->validate()) {
            FileHelper::createDirectory('uploads');
            $this->csvFile->saveAs(Yii::getAlias('@web/uploads/' . self::FILE_NAME));
            return true;
        }
        return false;
    }

    static public function hasFile() {
        $urlHeaders = @get_headers(Yii::getAlias('@web/uploads/' . self::FILE_NAME));
        if (strpos($urlHeaders[0], '200')) return true;
        return false;
    }
}

?>