<?php
namespace api\controllers;

use api\models\LoginForm;
use api\models\SignupForm;
use yii\rest\ActiveController;
use yii;

/**
 * User controller
 */
class UserController extends ActiveController
{
    public $modelClass = 'common\models\User';

    /**
     * Removed unused actions
     *
     * @return array
     */
    public function actions()
    {
        return [];
    }

    /**
     * Sign up new user, return token or throws validation errors
     *
     * @return string JWT token
     * @throws yii\web\BadRequestHttpException
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        $model->load(Yii::$app->request->post(), '');

        if ($model->validate() && $user = $model->signup()) {
            return $user->getToken();
        } else {
            throw new yii\web\BadRequestHttpException(current($model->getFirstErrors()));
        }
    }

    /**
     * Authenticates user, return token or throws validation errors
     *
     * @return string JWT token
     * @throws yii\web\BadRequestHttpException
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            return $model->getUser()->getToken();
        } else {
            throw new yii\web\BadRequestHttpException(current($model->getFirstErrors()));
        }
    }
}
