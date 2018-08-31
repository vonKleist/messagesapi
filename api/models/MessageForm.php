<?php
namespace api\models;

use common\models\Message;
use yii\base\Model;
use common\models\User;

/**
 * Message send form
 */
class MessageForm extends Model
{
    public $email;
    public $title;
    public $text;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['title', 'trim'],
            ['title', 'required'],
            ['title', 'string', 'max' => 255],

            ['text', 'trim'],
            ['text', 'required'],
            ['text', 'string'],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email',
                'exist',
                'targetClass' => '\common\models\User',
                'targetAttribute' => 'email',
                'message' => 'User with such email doesn`t exist'
            ],
        ];
    }

    /**
     * Send message
     *
     * @return bool true if message created
     */
    public function send()
    {
        if (!$this->validate()) {
            return null;
        }

        $receiver = User::findByEmail($this->email);

        $message = new Message();
        $message->senderId = \Yii::$app->user->id;
        $message->receiverId = $receiver->id;
        $message->title = $this->title;
        $message->text = $this->text;

        return $message->save();
    }
}