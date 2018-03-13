<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ForgotPasswordForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'password')->passwordInput(['autofocus' => true]) ?>

<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <?= Html::submitButton('Send', ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

