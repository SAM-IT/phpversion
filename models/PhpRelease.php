<?php


namespace app\models;


use yii\base\Model;
use \DateTime;

class PhpRelease extends Model
{
    public $version;

    public $name;
    /**
     * @var LinuxRelease
     */
    public $linux;
    public $status;
    public $key;

    /**
     * PhpRelease constructor.
     * @param LinuxRelease $linux
     * @param array|null $config
     */
    public function __construct(LinuxRelease $linux, array $config = null)
    {
        $this->linux = $linux;
        parent::__construct($config);
    }


    public function getPHPVersion($patch = true)
    {
        if ($patch) {
            preg_match("/^(\d+\.\d+\.\d+)/", $this->version, $matches);
        } else {
            preg_match("/^(\d+\.\d+)/", $this->version, $matches);
        }

        return $matches[1];
    }

    /**
     * @return boolean Whether this version of PHP is still supported.
     */
    public function getSupported()
    {
        $eol = [
            '5.5' => new DateTime('2016-07-10'),
            '5.6' => new DateTime('2018-12-31'),
            '7.0' => new DateTime('2018-12-31')
        ];
        $version = $this->getPHPVersion(false);
        $now = new DateTime();
        foreach($eol as $test => $endDate) {
            if (version_compare($version, $test) <= 0
                && $endDate < $now) {
                return false;
            }
        }
        return true;
    }
    
    
    
}