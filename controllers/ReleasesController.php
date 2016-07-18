<?php


namespace app\controllers;


use app\models\LinuxRelease;
use app\models\LinuxReleaseFilter;
use SamIT\Yii2\Traits\ActionInjectionTrait;
use yii\base\Controller;
use yii\db\ActiveQueryTrait;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class ReleasesController extends Controller
{
    use ActionInjectionTrait;
    public function actionLinux(Request $request)
    {
        $filterModel = new LinuxReleaseFilter();
        $filterModel->load($request->getQueryParams());

        $dataProvider = $filterModel->search();
        return $this->render('linux', [
            'dataProvider' => $dataProvider,
            'filterModel' => $filterModel
        ]);
    }
}