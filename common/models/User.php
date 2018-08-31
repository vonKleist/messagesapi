<?php
namespace common\models;

use Firebase\JWT\JWT;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\UnauthorizedHttpException;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $phoneNumber
 * @property string $passwordHash
 * @property string $email
 * @property int $createdAt
 * @property int $updatedAt
 *
 * @property Message[] $messages
 * @property Message[] $messages0
 */
class User extends ActiveRecord implements IdentityInterface
{
    use \damirka\JWT\UserTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
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
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phoneNumber', 'passwordHash', 'email'], 'required'],
            [['createdAt', 'updatedAt'], 'integer'],
            [['phoneNumber', 'passwordHash', 'email'], 'string', 'max' => 255],
            [['phoneNumber'], 'unique'],
            [['email'], 'unique'],
        ];
    }

    protected static function getSecretKey()
    {
        return Yii::$app->params['JWTSecretKey'];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Generate JWT token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->getJWT();
    }

    /**
     * {@inheritdoc}
     *
     * Customized method for handle expiration of tokens
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $secret = static::getSecretKey();

        // Decode token and transform it into array.
        // Firebase\JWT\JWT throws exception if token can not be decoded
        try {
            $decoded = JWT::decode($token, $secret, [static::getAlgo()]);
        } catch (\Exception $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        }

        static::$decodedToken = (array) $decoded;

        // If there's no jti param - exception
        if (!isset(static::$decodedToken['jti'])) {
            throw new UnauthorizedHttpException('Incorrect token');
        }

        // JTI is unique identifier of user.
        // For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
        $id = static::$decodedToken['jti'];

        return static::findByJTI($id);
    }

    /**
     * {@inheritdoc}
     *
     * Customized method for handle expiration of tokens
     */
    public function getJWT()
    {
        // Collect all the data
        $secret      = static::getSecretKey();
        $currentTime = time();
        $request     = Yii::$app->request;
        $hostInfo    = '';

        // There is also a \yii\console\Request that doesn't have this property
        if ($request instanceof Request) {
            $hostInfo = $request->hostInfo;
        }

        // Merge token with presets not to miss any params in custom
        // configuration
        $token = array_merge([
            'iss' => $hostInfo,
            'aud' => $hostInfo,
            'iat' => $currentTime,
            'exp' => $currentTime + Yii::$app->params['tokenExpirationTime'],
            'nbf' => $currentTime
        ], static::getHeaderToken());

        // Set up id
        $token['jti'] = $this->getJTI();

        return JWT::encode($token, $secret, static::getAlgo());
    }
}
