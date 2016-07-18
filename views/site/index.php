<?php
use app\models\LinuxRelease;
use app\models\PhpRelease;
use \yii\helpers\StringHelper;
/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <?php
return;
    $models = [];
    foreach(LinuxRelease::findAll() as $linux) {
        foreach($linux->phpReleases() as $php) {
            $models[] = $php;
        }
    }


//    $models = array_filter($entries, function(PhpRelease $release) {
//        return !in_array($release->status, ['Deleted', 'Superseded', 'Obsolete']);
//    });
//    usort($models, function(PhpRelease $r1, PhpRelease $r2) {
//        return version_compare($r1->getPHPVersion(), $r2->getPHPVersion());
//    });

    echo \yii\grid\GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'models' => $models
        ]),
        'columns' => [
            'PHPVersion',
            'version',
            'linux.distribution',
            'linux.codeName',
            'linux.version',
            [
                'attribute' => 'linux.supported',
                'format' => 'boolean'
            ], [
                'attribute' => 'linux.releaseDate',
                'format' => 'date'
            ],[
                'attribute' => 'linux.endOfLifeDate',
                'format' => 'date'
            ],
            [
                'attribute' => 'supported',
                'format' => 'boolean'
            ],

        ],
        'rowOptions' => function (PhpRelease $model, $key, $index, $grid) {
            if (!$model->linux->supported && !$model->supported) {
                return [
                    'style' => [
                        'background-color' => "red"
                    ]
                ];
            } elseif (!$model->supported) {
                return [
                    'style' => [
                        'background-color' => "orange"
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