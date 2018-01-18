<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use app\models\Setting;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use app\helpers\GridHelper;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $searchModel   app\models\search\LogNodeSearch
 * @var $users         array
 * @var $severities    array
 * @var $actions       array
 */
app\assets\Select2Asset::register($this);
app\assets\LogAsset::register($this);
app\assets\LaddaAsset::register($this);
app\assets\DatetimepickerAsset::register($this);

$this->title = Yii::t('app', 'Logs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Logs' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('node', 'Nodes')];

$this->registerJs(
/** @lang JavaScript */
    "
       /** Select2 init */
       $('.select2').select2({
           minimumResultsForSearch: -1,
           width : '100%'
       });
       
       /** Select2 with search */
       $('.select2-search').select2({
           width : '100%'
       });
    "
);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('log', 'Node logs') ?></h3>
                <div class="pull-right">
                    <?= Html::a('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), 'javascript:;', ['class' => 'btn btn-sm bg-light-black search-button']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <div class="node-log-search" style="display: none;">
                    <?php
                        echo $this->render('_search', [
                            'model'      => $searchModel,
                            'users'      => $users,
                            'severities' => $severities,
                            'actions'    => $actions,
                        ]);
                    ?>
                </div>

                <?php Pjax::begin(['id' => 'node-log-pjax']); ?>
                    <?php
                        /** @noinspection MissedFieldInspection, PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'node-log-grid',
                            'tableOptions' => ['class' => 'table table-bordered log-table'],
                            'dataProvider' => $dataProvider,
                            'afterRow'     => function($model) { /** @var $model \app\models\LogNode */
                                $id = 'message_' . $model->id;
                                return '<tr><td class="grid-expand-row" colspan="8"><div class="grid-expand-div" id="'.$id.'">'.nl2br($model->message).'</div></td></tr>';
                            },
                            'layout'  => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'         => 'raw',
                                    'options'        => ['style' => 'width:3%'],
                                    'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                                    'value'          => function($model) { /** @var $model \app\models\LogNode */
                                        return Html::a('<i class="fa fa-caret-square-o-down"></i>', 'javascript:;', [
                                            'class'         => 'gridExpand',
                                            'title'         => Yii::t('log', 'Show full message'),
                                            'data-div-id'   => '#message_' . $model->id,
                                            'data-multiple' => 'true'
                                        ]);
                                    },
                                ],
                                [
                                    'attribute' => 'time',
                                    'value'     => function($data) {
                                        return Yii::$app->formatter->asDatetime($data->time, 'php:'.Setting::get('datetime'));
                                    },
                                    'options'   => ['style' => 'width:10%']
                                ],
                                [
                                    'attribute'     => 'userid',
                                    'value'         => 'user.fullname',
                                    'enableSorting' => false,
                                    'options'       => ['style' => 'width:10%']
                                ],
                                [
                                    'format'        => 'raw',
                                    'attribute'     => 'severity',
                                    'enableSorting' => false,
                                    'options'       => ['style' => 'width:7%'],
                                    'value'         => function($data) { /** @var $data \app\models\LogNode */
                                        return GridHelper::colorSeverity($data->severity);
                                    }
                                ],
                                [
                                    'attribute'     => 'node_params',
                                    'format'        => 'raw',
                                    'enableSorting' => false,
                                    'options'       => ['style' => 'width:15%'],
                                    'value'         => function($data) { /** @var  $data app\models\LogNode */
                                        $link = Yii::t('yii', '(not set)');
                                        if (!is_null($data->node_id)) {
                                            $text = (empty($data->node->hostname)) ? $data->node->ip : $data->node->hostname;
                                            $link = Html::a($text, ['/node/view', 'id' => $data->node->id], ['data-pjax' => '0']);
                                        }
                                        return $link;
                                    }
                                ],
                                [
                                    'attribute'     => 'action',
                                    'enableSorting' => false,
                                    'options'       => ['style' => 'width:8%']
                                ],
                                [
                                    'format'         => 'raw',
                                    'attribute'      => 'message',
                                    'enableSorting'  => false,
                                    'contentOptions' => ['class' => 'hide-overflow', 'style' => 'max-width: 0;'],
                                    'value'          => function($model) {/** @var $model \app\models\LogNode */
                                        return Html::tag('div', $model->message);
                                    },
                                ]
                            ],
                        ]);
                    ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>

