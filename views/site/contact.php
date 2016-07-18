<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Contact';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

        <p>
            If you have business inquiries or other questions, please get my contact information via <?= Html::a("LinkedIn", "//www.linkedin.com/in/sam-mousa-16333820")?>.

            Thank you.
        </p>

</div>
