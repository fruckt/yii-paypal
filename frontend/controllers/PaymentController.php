<?php

namespace frontend\controllers;

use common\models\Paypal;
use Yii;
use common\models\Payment;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['get'],
                    'execute' => ['get'],
                ],
            ],
        ];
    }

    /**
     * Creates a new Payment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Payment();
        $model->user_id = Yii::$app->user->identity->id;
        $model->amount = 10;
        $model->currency = 'USD';
        $model->status = Payment::STATUS_NEW;
        $model->save();

        if ($link = Paypal::create($model)) {
            return $this->redirect($link);
        }

        return $this->redirect('/');
    }


    public function actionExecute()
    {
        Paypal::execute(Yii::$app->request);

        return $this->redirect('/');
    }

    /**
     * Finds the Payment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Payment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
