<?php 

use kartik\form\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin();
$data = $provider->getModels();
?>
    <?php foreach ($provider->getColNames() as $key): ?>
        <?= $form->field($model, $key)->textInput(['value' => $data[$key]]) ?>
    <?php endforeach; ?>
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end(); ?>