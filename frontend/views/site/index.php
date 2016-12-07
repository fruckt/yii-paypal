<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Congratulations!</h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <?php if (Yii::$app->user->isGuest) { ?>
            <p><a class="btn btn-lg btn-success" href="<?= \yii\helpers\Url::to('login') ?>">Sign In To Download .zip</a></p>
        <?php } elseif (Yii::$app->user->identity->hasSuccesPayments()) { ?>
            <p><a class="btn btn-lg btn-success" href="<?= Yii::$app->urlManager->createAbsoluteUrl(['uploads/success.zip']) ?>">Download .zip</a></p>
        <?php } else { ?>
            <p><a class="btn btn-lg btn-success" href="<?= \yii\helpers\Url::to('payment/create') ?>">Buy .zip for $10</a></p>
        <?php } ?>
    </div>
</div>
