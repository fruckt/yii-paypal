<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "payment".
 *
 * @property integer $id
 * @property integer $user_id
 * @property double $amount
 * @property string $currency
 * @property string $gateway_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $user
 */
class Payment extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;
    const STATUS_CANCELED = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'currency', 'status'], 'required'],
            [['user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['currency'], 'string', 'max' => 3],
            [['gateway_id'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'amount' => Yii::t('app', 'Amount'),
            'currency' => Yii::t('app', 'Currency'),
            'gateway_id' => Yii::t('app', 'Gateway ID'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
