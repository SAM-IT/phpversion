<?php
use app\models\LinuxRelease;
use app\models\PhpRelease;
use \yii\helpers\StringHelper;
/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <?php

    //    $models = array_filter($entries, function(PhpRelease $release) {
    //        return !in_array($release->status, ['Deleted', 'Superseded', 'Obsolete']);
    //    });
    //    usort($models, function(PhpRelease $r1, PhpRelease $r2) {
    //        return version_compare($r1->getPHPVersion(), $r2->getPHPVersion());
    //    });

    echo \yii\grid\GridView::widget([
        'filterModel' => $filterModel,
        'dataProvider' => $dataProvider,
        'columns' => [
            'distribution',
            'codeName',
            'version',
            [
                'attribute' => 'supported',
                'format' => 'boolean',
                'filter' => \yii\bootstrap\Html::activeCheckboxList($filterModel, 'supported', [
                    true => "Yes",
                ], [
                    'class' => 'form-control'
                ])
            ], [
                'attribute' => 'releaseDate',
                'format' => 'date'
            ],[
                'attribute' => 'endOfLifeDate',
                'format' => 'date'
            ],
            'mostRecentPhp.PHPVersion'

        ],
        'rowOptions' => function (LinuxRelease $model, $key, $index, $grid) {
            if (!$model->supported) {
                return [
                    'style' => [
                        'background-color' => "red"
                    ]
                ];
            } else {
                return [
                    'style' => [
                        'background-color' => "green"
                    ]
                ];
            }

        }
    ]);
    //    echo \yii\grid\GridView::widget([
    //        'dataProvider' => new \yii\data\ArrayDataProvider([
    //            'models' =>
    //        ]),
    //        'columns' => [
    //            'fullseriesname',
    //            'status',
    //
    //        ]
    //    ]);



    ?>
</div>