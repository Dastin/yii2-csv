<?php

namespace dastin\csv;

use Yii;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            'csv' => 'csv/csv/index',
        ], false);
        $app->setModule('csv', 'dastin\csv\Module');
        $app->setModule('gridview', 'kartik\grid\Module');
    }
}

?>