<?php


namespace app\models;


use app\helpers\DomTableHelper;
use Carbon\Carbon;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\validators\SafeValidator;

/**
 * Class LinuxRelease
 * @package app\models
 * @property string key The unique identifier for this release.
 */
class LinuxReleaseFilter extends LinuxRelease
{
    protected $_supported;
    protected $_phpSupported;
    public $codeName;
    /**
     * @var Carbon
     */
    public $releaseDate;
    /**
     * @var Carbon
     */
    public $endOfLifeDate;


    private $_version;

    public function getVersion()
    {
        return $this->_version;
    }

    public function setVersion($value)
    {
        $this->_version = $value;
    }

    public function getSupported()
    {
        return $this->_supported;
    }

    public function getPhpSupported()
    {
        return $this->_phpSupported;
    }

    public function setDistribution($value) {
        $this->_distribution = $value;
    }

    public function setSupported($value) {
        $this->_supported = $value === "" ? null : (bool) $value;
    }

    public function setPhpSupported($value) {
        $this->_phpSupported = $value === "" ? null : (bool) $value;
    }

    public function rules()
    {
        return [
            [$this->attributes(), SafeValidator::class]

        ];
    }

    public function attributes()
    {
        $result = parent::attributes();
        $result[] = 'key';
        $result[] = 'distribution';
        $result[] = 'version';
        $result[] = 'supported';
        $result[] = 'phpSupported';
        return $result;
    }

    protected function filter(array $models)
    {
        return array_filter($models, function(LinuxRelease $model) {
//            if (empty($model->getPhpReleases())) {
//                return false;
//            }
            foreach($this->attributes as $key => $value) {
                if (isset($value) && $value !== "" && $model->$key !== $value) {
                    return false;
                }
            }
            return true;
        });
    }
    public function search()
    {
        $models = $this->filter(LinuxRelease::findAll());
        $sortAttributes = ArrayHelper::map($this->attributes(), function($attribute) { return $attribute; }, function($attribute) {
            return [
                'asc' => [$attribute => SORT_ASC],
                'desc' => [$attribute => SORT_DESC],
                'label' => $attribute
            ];
        });
        $sortAttributes['mostRecentPhp.PHPVersion'] = [
            'asc' => ['mostRecentPhp.PHPVersion' => SORT_ASC],
            'desc' => ['mostRecentPhp.PHPVersion' => SORT_DESC],
            'label' => 'mostRecentPhp.PHPVersion'
        ];
        $result = new \yii\data\ArrayDataProvider([
            'key' => 'key',
            'allModels' => $models,
            'sort' => [
                'attributes' => $sortAttributes
            ]
        ]);
        return $result;


    }


}
