<?php

namespace app\controllers;

use app\models\ForgotPasswordForm;
use app\models\SignupForm;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'oAuthSuccess'],
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $activeRecord = new User();
                $activeRecord->username = $model->username;
                $activeRecord->email = $model->email;
                $activeRecord->password_hash = $activeRecord->hash($model->password);
                $activeRecord->access = 100;
                $activeRecord->scnt = 0;
                $activeRecord->save();
            if($model->load(Yii::$app->request->post()) && $model->login()) {
                return $this->goBack();
            }
        }
        return $this->render('signup', ['model' => $model]);
    }
    public function actionForgotPassword()
    {
        $model = new ForgotPasswordForm();
        if(Yii::$app->request->get('token') === null) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $user = User::findOne(['email' => $model->email]);
                $user->token = $user->generateRandomString('token');
                $user->save();
                $url = Url::to(['/site/forgot-password', 'token' => $user->token]);
                $model->mail($model->email, $url);
                return $this->goHome();
            }
            return $this->render('forgot-password', ['model' => $model]);
        }
        else {
            $model->token = Yii::$app->request->get('token');
            $tokenUser = User::findOne(['token' => $model->token]);
            if($tokenUser !== null) {
                if($model->load(Yii::$app->request->post()) && $model->validate()) {
                    $tokenUser->password_hash = $tokenUser->hash($model->password);
                    $tokenUser->save();
                    return $this->goHome();
                }
                return $this->render('change-password', ['model' => $model]);
            }
        }

    }


    /**
     * This function will be triggered when user is successfuly authenticated using some oAuth client.
     *
     * @param yii\authclient\ClientInterface $client
     * @return boolean|yii\web\Response
     */
    public function oAuthSuccess($client) {
        // get user data from client
        $userAttributes = $client->getUserAttributes();
        $email = 0;
        $username = 0;
        switch(Yii::$app->request->get('authclient')) {
            case 'facebook':
                $email = $userAttributes['email'];
                $username = $userAttributes['name'];
                break;
            case 'google':
                $email = $userAttributes['emails'][0]['value'];
                $username = $userAttributes['displayName'];
                break;
            case 'vkontakte':
                $email = $userAttributes['email'];
                $username = $userAttributes['screen_name'];
                break;
            case 'twitter':
                $email = $userAttributes['email'];
                $username = $userAttributes['screen_name'];
        }

        $user = User::findOne(['email' => $email]);
        if($user !== null) {
            if($user->scnt === 1) {
                return Yii::$app->user->login($user, 3600 * 24 * 30);
            }
            else {
                //$model->addError($userAttributes['email'], 'This email is already in use');
                //return $this->render('login', ['model' => $model]);
                return $this->action->redirect("/login?error=1");
            }
        }
        else {
                $activeRecord = new User;
                $activeRecord->username = User::findByUsername($username) === null ? str_replace(' ', '_', $username) : str_replace(' ', '_', $userAttributes['name']) . rand(100, 999);
                $activeRecord->email = $email;
                $activeRecord->password_hash = User::hash($activeRecord->generateRandomString('password_hash', 33));
                $activeRecord->access = 100;
                $activeRecord->scnt = 1;
                if($activeRecord->save()) {
                    return Yii::$app->user->login($activeRecord, 3600 * 24 * 30);
                }
        }
    }
}
