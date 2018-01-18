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

namespace app\models;

use Yii;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "{{%job_global_variable}}".
 *
 * @property integer $id
 * @property string $var_name
 * @property string $var_value
 * @property string $description
 */
class JobGlobalVariable extends ActiveRecord
{

    /**
     * @var array
     */
    public $var_dependencies = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%job_global_variable}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['var_name', 'var_value'], 'required'],
            [['var_name'], 'unique'],
            [['var_name'], 'filter', 'filter' => 'strtoupper'],
            [['var_name'], 'match', 'pattern' => '/^%%\w+%%$/', 'message' => Yii::t('network', 'Command variable must start and end with - %%. Example %%TEST%%')],
            [['var_name'], 'in', 'not' => true, 'range' => \Y::param('system_variables'),
                'message' => Yii::t('network', 'Command variable <b>{value}</b> is system reserved variable.')
            ],
            [['var_name'], 'in', 'not' => true, 'range' => Job::find()->select('command_var')->column(),
                'message' => Yii::t('network', 'Variable <b>{value}</b> is worker variable.')
            ],
            [['var_name'], 'string', 'max' => 128],
            [['var_value', 'description'], 'string', 'max' => 255],
            [['description'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'var_name'    => Yii::t('network', 'Variable Name'),
            'var_value'   => Yii::t('network', 'Variable Value'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * Check variable dependencies before delete
     *
     * @return bool
     */
    public function beforeDelete()
    {
        $delete = true;

        $query = Job::find()->joinWith('worker')->asArray()->all();

        foreach ($query as $item) {
            if (strpos($item['command_value'], $this->var_name)) {
                array_push($this->var_dependencies, $item['worker']['name'] . ' - ' .$item['name']);
                $delete = false;
            }
        }

        return $delete;
    }

}
