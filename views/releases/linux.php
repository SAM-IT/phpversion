<?php
use app\models\LinuxRelease;
use app\models\PhpRelease;
use \yii\helpers\StringHelper;
/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <h3>This page should help you to decide what minimum PHP version you should require for your project!</h3>
    <p>


        Currently the following linux distributions are checked: Ubuntu, Debian and RHEL. Note that since CentOS has
        the same packages as RHEL, you can use it for CentOS as well.
    </p>
    <ul>
        <li>Rows marked in <b style="color: green;">green</b> are currently supported linux versions that offer a currently supported PHP version by default.</li>
        <li>Rows marked in <b style="color: orange;">orange</b> are currently supported linux versions that offer an out of date PHP version.
        They are relevant because for those distributions security patches are backported and thus security is no argument for increasing the minimum required PHP version.</li>
        <li>Rows marked in <b style="color: red;">red</b> are linux distributions that are no longer supported. Regardless of the shipped PHP version, these distributions no longer receive security upgrades and should not influence your choice.</li>
    </ul>

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
            ], [
                'attribute' => 'mostRecentPhp.PHPVersion',
                'label' => 'PHP Version',
                'filter' => \yii\bootstrap\Html::activeCheckboxList($filterModel, 'phpSupported', [
                    true => "Active",
                ], [
                    'class' => 'form-control',
                    'style' => [
                        'font-size' => '0.8em'
                    ]
                ])
            ]

        ],
        'rowOptions' => function (LinuxRelease $model, $key, $index, $grid) {
            if (!$model->supported) {
                return [
                    'style' => [
                        'background-color' => "red"
                    ]
                ];
            } elseif (isset($model->mostRecentPhp) && $model->mostRecentPhp->supported) {
                return [
                    'style' => [
                        'background-color' => "green"
                    ]
                ];
            } else {
                return [
                    'style' => [
                        'background-color' => "orange"
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