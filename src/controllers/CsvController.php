<?php

namespace dastin\csv\controllers;

use Yii;
use yii\base\DynamicModel;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;
use dastin\csv\models\UploadForm;
use dastin\csv\models\CsvData;

class CsvController extends Controller
{
    public function actionIndex()
    {
        Url::remember();
        $provider = new CsvData([
            'filename' => Yii::getAlias('@web/uploads/' . UploadForm::FILE_NAME),
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);
        $provider->setSearch(Yii::$app->request->queryParams)->getModels();
        $searchModel = new DynamicModel($provider->getColNames());
        $searchModel->addRule($provider->getColNames(), 'safe');
        if (isset(Yii::$app->request->queryParams['DynamicModel'])) {
            foreach (Yii::$app->request->queryParams['DynamicModel'] as $key => $value)
                $searchModel->defineAttribute($key, $value);
        }
        return $this->render('index', [
            'provider' => $provider,
            'searchModel' => $searchModel
        ]);
    }

    public function actionUpdate($id)
    {
        if (Yii::$app->request->isPost)
        {
            $provider = new CsvData([
                'filename' => Yii::getAlias('@web/uploads/' . UploadForm::FILE_NAME)
            ]);
            $provider->save($id, Yii::$app->request->post());
            Yii::$app->getResponse()->redirect(Url::previous());
        }
        $provider = new CsvData([
            'filename' => Yii::getAlias('@web/uploads/' . UploadForm::FILE_NAME),
            'key' => $id
        ]);
        $provider->getModels();
        $model = new DynamicModel($provider->getColNames());
        $model->addRule($provider->getColNames(), 'required');

        return $this->render('edit', [
            'provider' => $provider,
            'model' => $model
        ]);
    }

    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->csvFile = UploadedFile::getInstance($model, 'csvFile');
            if ($model->upload())
                Yii::$app->getResponse()->redirect(['csv/csv/index']);
        }

        return $this->render('upload', ['model' => $model]);
    }

    public function actionDelete($id)
    {  
        $provider = new CsvData([
            'filename' => Yii::getAlias('@web/uploads/' . UploadForm::FILE_NAME)
        ]);
        $provider->delete($id);
        Yii::$app->getResponse()->redirect(Url::previous());
    }
}
?>