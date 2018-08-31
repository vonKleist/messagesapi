<?php
namespace api\controllers;

use api\models\MessageForm;
use common\models\Message;
use yii\rest\ActiveController;
use yii;

/**
 * Message controller
 */
class MessageController extends ActiveController
{
    public $modelClass = 'common\models\Message';

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
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'bearerAuth' => [
                'class' => yii\filters\auth\HttpBearerAuth::className()
            ]
        ]);
    }

    /**
     * Shows list of users messages
     *
     * @return array|yii\db\ActiveRecord[]
     */
    public function actionIndex()
    {
        return Message::getUserMessages(Yii::$app->request->get('type'), Yii::$app->request->get('status'));
    }

    /**
     * Create new message
     *
     * @return bool
     * @throws yii\web\BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new MessageForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->send()) {
            return true;
        } else {
            throw new yii\web\BadRequestHttpException(current($model->getFirstErrors()));
        }
    }

    /**
     * Delete message, user can delete only own, not deleted message, otherwise throws exception. Soft delete used,
     * because message
     *
     * @param $id
     * @return bool
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $message = Message::findOne(['messageId' => $id, 'isDeleted' => Message::STATUS_NOT_DELETED]);

        if (!$message || $message->senderId !== \Yii::$app->user->id) {
            throw new yii\web\ForbiddenHttpException('You can delete only messages that you\'ve created.');
        }

        return boolval($message->softDelete());
    }
}
