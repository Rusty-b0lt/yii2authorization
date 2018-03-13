<?php
/**
 * Created by PhpStorm.
 * User: rusty_b0lt
 * Date: 21/02/18
 * Time: 21:33
 */

namespace app\models;


use yii\base\Model;
use Yii;

class ForgotPasswordForm extends Model
{
    public $email;
    public $password;
    public $token;

    public function rules()
    {
       return [
           ['email', 'email'],
           ['token', 'string', 'max' => 128],
           ['email', 'validateEmail'],
           ['password', 'validatePassword'],
       ];
    }

    public function validateEmail($attribute, $params)
    {
        if(User::findOne(['email' => $this->email]) == null) {
            $this->addError($attribute, 'There is no user with this email registered');
        }
    }

    public function validatePassword($attribute, $params)
    {
        $user = User::findOne(['token' => $this->token]);
        if($user !== null && Yii::$app->getSecurity()->validatePassword($this->password, $user->password_hash)) {
            $this->addError($attribute, 'New password can\'t be the same as the old');
        }
    }

    public function mail($email, $text) {
        Yii::$app->mailer->compose()
            ->setFrom('supportblablabla@yii2.com')
            ->setTo($email)
            ->setSubject('Your password')
            ->setTextBody($text)
            ->send();
    }

}