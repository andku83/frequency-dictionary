<?php

namespace app\controllers;

use app\components\dictionary\Dictionary;
use app\models\Glossary;
use app\models\search\WordSearch;
use app\models\Text;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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

    public function actionDictionary()
    {
        return $this->render('dictionary');
    }

    public function actionGlossary()
    {
        return $this->render('glossary');
    }

    public function actionHelp()
    {
        return $this->render('help');
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionShowText($id)
    {
        $text = Text::findOne(['id' => $id]);

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }

        if (!$text) throw new NotFoundHttpException();

        $data = file_get_contents($text->file_path);

        return '<pre style="white-space:pre-wrap;word-break:break-word;">'. $data .'</pre>';
    }

    public function actionTextLoad()
    {
        if (Yii::$app->request->isAjax) {
            $this->layout = false;
            return $this->renderAjax('text-load');
        }
        return $this->render('text-load');
    }

    public function actionProcess()
    {
        if (Yii::$app->request->isAjax) {
            $this->layout = false;
            return $this->renderAjax('process', ['result' => Dictionary::getAnswer()]);
        }
        return $this->render('process', ['result' => Dictionary::getAnswer()]);
    }

    public function actionProcessing()
    {
        $this->layout = false;

        $result = Dictionary::calculateMultiple();

        $result['html'] = $this->render('_processing', ['result' => $result]);

        return $this->asJson($result);
    }

    public function actionReset($reset = 0)
    {
        if ($reset) Dictionary::truncate();

        return $this->asJson(true);
    }

    public function actionLoadGlossary()
    {
        Dictionary::loadGlossary();

        return $this->asJson(true);
    }

    public function actionGlossaryEdit($headword = null)
    {
        $model = Glossary::findOne(['headword' => $headword]);
        if (!$model) {
            $model = new Glossary(['headword' => $headword]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('glossary-edit', [
                'model' => $model,
            ]);
        }
        return $this->render('glossary-edit', [
            'model' => $model,
        ]);
    }

    /**
     * Lists all Word models.
     * @return mixed
     */
    public function actionWordList()
    {
        $searchModel = new WordSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('_words', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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

        $model->password = '';
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

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
