<?php
/**
 * Created by PhpStorm.
 * User: rusty_b0lt
 * Date: 13/01/18
 * Time: 00:34
 */

namespace app\models;


use yii\base\Model;
use Yii;
class SignupForm extends Model
{
    public $username;
    public $password;
    public $email;
    public $rememberMe = true;

    private $_user = false;

    public function rules()
    {
        return [
            [['email', 'username', 'password'], 'required'],
            [['username', 'email'], 'unique', 'targetClass' => User::class],
            ['email', 'string', 'max' => 128],
            ['username', 'string', 'max' => 30],
            ['email', 'email'],
            ['username', 'match', 'pattern' => '/[a-zA-Z_\-\.0-9]+$/'],
            //['password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\!\"\#\$\%\&\'\(\)\*\+\,\-\.\/\:\;\<\ =\>\?\@\[\\\]\^\_\`\{\|\}\~])[A-Za-z\d\!\"\#\$\%\&\'\(\)\*\+\,\-\.\/\:\;\<\ =\>\?\@\[\\\]\^\_\`\{\|\}\~]{8,}$/'],

        ];
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

}