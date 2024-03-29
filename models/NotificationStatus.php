<?php

namespace hossein142001\notification\models;

use Yii;
use hiiran\api\v1\modules\user\models\User;
/**
 * This is the model class for table "notification_status".
 *
 * @property integer $id
 * @property string $provider
 * @property string $event
 * @property string $params
 * @property string $status
 * @property string $update_at
 * @property string $create_at
 */
class NotificationStatus extends \hiiran\components\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['params'], 'string'],
            [['update_at', 'create_at'], 'safe'],
            [['provider', 'event'], 'string', 'max' => 255],
            [['status'], 'string'],
            [['created_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_user_id' => 'id']],
            [['updated_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_user_id' => 'id']],
            [['deleted_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['deleted_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider' => 'Provider',
            'event' => 'Event',
            'params' => 'Params',
            'status' => 'Status',
            'update_at' => 'Update At',
            'create_at' => 'Create At',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'deleted_user_id']);
    }
}