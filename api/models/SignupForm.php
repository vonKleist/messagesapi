<?php
namespace api\models;

use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $phoneNumber;
    public $email;
    public $password;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['phoneNumber', 'trim'],
            ['phoneNumber', 'required'],
            ['phoneNumber', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This phone number has already been taken.'],
            ['phoneNumber', 'integer'],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->phoneNumber = $this->phoneNumber;
        $user->email = $this->email;
        $user->setPassword($this->password);

        return $user->save() ? $user : null;
    }
}