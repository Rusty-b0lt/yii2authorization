<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\bootstrap\ActiveForm;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['username', 'string', 'max' => 30],
            ['username', 'match', 'pattern' => '/^[a-zA-Z_\-\.0-9]+$/'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            //['password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\!\"\#\$\%\&\'\(\)\*\+\,\-\.\/\:\;\<\ =\>\?\@\[\\\]\^\_\`\{\|\}\~])[A-Za-z\d\!\"\#\$\%\&\'\(\)\*\+\,\-\.\/\:\;\<\ =\>\?\@\[\\\]\^\_\`\{\|\}\~]{8,}$/'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
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


