<?php

namespace common\models;

use common\interfaces\Gateway;
use Exception;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Yii;
use yii\helpers\Url;
use yii\web\Request;

class Paypal implements Gateway
{
    public static function getApiContext()
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                Yii::$app->params['paypal']['client_id'],
                Yii::$app->params['paypal']['client_secret']
            )
        );

        $apiContext->setConfig(
            array(
                'mode' => 'sandbox',
                // 'log.LogEnabled' => true,
                // 'log.FileName' => '../PayPal.log',
                // 'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                // 'cache.enabled' => true,
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            )
        );

        return $apiContext;
    }

    public static function create(Payment $model)
    {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $item = new Item();
        $item->setName('Download link for .zip')
            ->setCurrency($model->currency)
            ->setQuantity(1)
            ->setSku('9999')
            ->setPrice($model->amount);

        $itemList = new ItemList();
        $itemList->setItems(array($item));

        $amount = new Amount();
        $amount->setCurrency($model->currency)
            ->setTotal($model->amount);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(Url::to(['payment/execute', 'success' => true], true))
            ->setCancelUrl(Url::to(['payment/execute', 'success' => false, 'modelId' => $model->id], true));

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        try {
            $payment->create(self::getApiContext());
        } catch (Exception $ex) {
            Yii::$app->getSession()->setFlash('error', 'Payment failed.');
            $model->status = Payment::STATUS_FAIL;
            $model->save();

            return false;
        }

        $model->gateway_id = $payment->getId();
        $model->save();

        return $payment->getApprovalLink();
    }

    public static function execute(Request $request)
    {
        if (Yii::$app->request->get('success')) {
            $paymentId = Yii::$app->request->get('paymentId');
            if ($model = Payment::findOne(['gateway_id' => $paymentId])) {
                $payment = \PayPal\Api\Payment::get($paymentId, self::getApiContext());

                $execution = new PaymentExecution();
                $execution->setPayerId(Yii::$app->request->get('PayerID'));

                try {
                    $result = $payment->execute($execution, self::getApiContext());

                    try {
                        $payment = \PayPal\Api\Payment::get($paymentId, self::getApiContext());
                    } catch (Exception $ex) {
                        Yii::$app->getSession()->setFlash('error', 'Payment failed.');
                        $model->status = Payment::STATUS_FAIL;
                        $model->save();

                        return false;
                    }
                } catch (Exception $ex) {
                    Yii::$app->getSession()->setFlash('error', 'Payment failed.');
                    $model->status = Payment::STATUS_FAIL;
                    $model->save();

                    return false;
                }

                $model->status = Payment::STATUS_SUCCESS;
                $model->save();

                return true;
            }
        } else {
            if ($model = Payment::findOne(['id' => Yii::$app->request->get('modelId'), 'status' => Payment::STATUS_NEW])) {
                Yii::$app->getSession()->setFlash('error', 'Payment canceled.');
                $model->status = Payment::STATUS_CANCELED;
                $model->save();

                return false;
            }
        }

        Yii::$app->getSession()->setFlash('error', 'Payment failed.');

        return false;
    }
}