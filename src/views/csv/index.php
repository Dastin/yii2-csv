<?php

use kartik\grid\GridView;

echo GridView::widget([
    'dataProvider' => $provider,
    'filterModel' => $searchModel,
    'columns' => array_merge($provider->getColNames(), [
        [
            'class' => 'yii\grid\ActionColumn',
            'urlCreator' => function($action, $model, $key, $index) {
                return [$action, 'id' => $model[0]];
            },
            'template'=>'{update} {delete}',
        ],
    ]),
]);

?>