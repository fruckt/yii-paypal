<?php

namespace common\interfaces;

use common\models\Payment;
use yii\web\Request;

interface Gateway
{
    public static function create(Payment $payment);
    public static function execute(Request $request);
}