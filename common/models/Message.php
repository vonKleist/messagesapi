<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\BadRequestHttpException;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "message".
 *
 * @property int $messageId
 * @property int $senderId
 * @property int $receiverId
 * @property string $title
 * @property string $text
 * @property int $isReceived
 * @property int $isDeleted
 * @property int $createdAt
 * @property int $updatedAt
 *
 * @property User $receiver
 * @property User $sender
 */
class Message extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 1;
    const STATUS_NOT_DELETED = 0;

    const STATUS_RECEIVED = 1;
    const STATUS_NOT_RECEIVED = 0;

    const TYPE_PARAMS = ['sent', 'received'];
    const STAUTS_PARAMS = ['sent', 'received', 'delivered'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['senderId', 'receiverId', 'title', 'text'], 'required'],
            [['senderId', 'receiverId', 'isReceived', 'isDeleted', 'createdAt', 'updatedAt'], 'integer'],
            [['text'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['receiverId'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['receiverId' => 'id']],
            [['senderId'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['senderId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'messageId' => 'Message ID',
            'senderId' => 'Sender ID',
            'receiverId' => 'Receiver ID',
            'title' => 'Title',
            'text' => 'Text',
            'isReceived' => 'Is Received',
            'isDeleted' => 'Is Deleted',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'updatedAt',
            ],
            [
                'class' => SoftDeleteBehavior::className(),
                'softDeleteAttributeValues' => [
                    'isDeleted' => self::STATUS_DELETED
                ]
            ]
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceiver()
    {
        return $this->hasOne(User::className(), ['id' => 'receiverId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::className(), ['id' => 'senderId']);
    }

    /**
     * Get messages of current user
     *
     * @param string $type type of message for user sent or received
     * @param string $receivingStatus delivering status of message, delivered, received or sent
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public static function getUserMessages($type, $receivingStatus)
    {
        // Check if filters are correct
        if (
            !in_array($type, static::TYPE_PARAMS) ||
            !in_array($receivingStatus, static::STAUTS_PARAMS)
        ) {
            throw new BadRequestHttpException('Incorrect type or status');
        }

        // Set column to filter by sender or receiver
        $userType = $type == 'sent' ? 'senderId' : 'receiverId';
        $query = static::find()->where([ $userType => Yii::$app->user->id]);

        // For snder show only not deleted messages
        if ($type == 'sent') {
            $query->andWhere(['isDeleted' => static::STATUS_NOT_DELETED]);
        }

        // If 'sent'- get both types
        if ($receivingStatus != 'sent') {
            $status = $receivingStatus == 'received' ? self::STATUS_RECEIVED : self::STATUS_NOT_RECEIVED;
            $query->andWhere(['isReceived' => $status]);
        }

        $messages = $query->all();

        // Update received status for first time selected messages
        if ($type == 'received' && $receivingStatus != 'received') {
            static::updateAll(
                ['isReceived' => static::STATUS_RECEIVED],
                ['receiverId' => Yii::$app->user->id, 'isReceived' => static::STATUS_NOT_RECEIVED]
            );
        }

        return $messages;
    }
}
