<?php


namespace app\models;


use app\helpers\DomTableHelper;
use app\helpers\Http;
use Carbon\Carbon;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\validators\SafeValidator;

/**
 * Class LinuxRelease
 * @package app\models
 * @property string $key The unique identifier for this release.
 * @property PhpRelease $mostRecentPhp The most recent php release.
 */
class LinuxRelease extends Model
{
    private static $cache = [];
    protected $_distribution;
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
        return !isset($this->endOfLifeDate) || $this->endOfLifeDate->isFuture();
    }
    /**
     * @return static[]
     */
    public static function findDebianReleases()
    {
        if (!isset(self::$cache['debian'])) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML(Http::get('https://wiki.debian.org/DebianReleases'));
            $records = [];
            /** @var DOMNode $node */
            foreach ($dom->getElementsByTagName('table')->item(0)->getElementsByTagName('tr') as $row) {
                if (!isset($headers)) {
                    $headers = [];
                    foreach ($row->getElementsByTagName('td') as $field) {
                        $headers[] = Inflector::variablize(trim($field->textContent));
                    }
                    $headers[] = "endOfLifeLTS";

                } else {
                    $normal = new DebianRelease();
                    foreach ($row->getElementsByTagName('td') as $i => $field) {
                        $value = trim($field->textContent);
                        if (stripos($headers[$i], 'endoflife') !== false) {
                            // Parse end of life.
                            if (empty($value)) {
                                $value = null;
                            } elseif (preg_match_all("/(\w+.*?\d{4})\s*\((\w+)\)/", $value, $matches, PREG_SET_ORDER)) {
                                $value = null;
                                foreach ($matches as $match) {
                                    if ($match[2] === 'LTS') {
                                        $lts = new DebianRelease();
                                        $lts->endOfLifeDate = new \Carbon\Carbon($match[1]);
                                    } else {
                                        $value = new Carbon($match[1]);
                                    }
                                }
                            } else {
                                try {
                                    $value = new Carbon($value);
                                } catch (\Exception $e) {
                                    $value = $normal->releaseDate;
                                }
                            }
                        } elseif (stripos($headers[$i], 'releasedate') !== false) {
                            try {
                                $value = !empty($value) ? new Carbon($value) : null;
                            } catch (\Exception $e) {
                                $value = null;
                            }
                        }
                        $normal->{$headers[$i]} = empty($value) ? null : $value;

                    }
                    $records[$normal->key] = $normal;
                    if (isset($lts)) {
                        foreach ($normal->attributes as $attribute => $value) {
                            if (!isset($lts->$attribute)) {
                                $lts->$attribute = $value;
                            }
                        }
                        $lts->codeName .= " LTS";
                        $records[$lts->key] = $lts;
                        unset($lts);
                    }

                }

            }

            self::$cache['debian'] = $records;
        }
        return self::$cache['debian'];
    }

    public static function findUbuntuReleases() {
        if (!isset(self::$cache['ubuntu'])) {
            $result = [];
            foreach (json_decode(file_get_contents('https://api.launchpad.net/1.0/ubuntu/series'),
                true)['entries'] as $e) {
                $release = new UbuntuRelease();

//            var_dump($e); die();
                $release->codeName = $e['displayname'];
                $release->releaseDate = new Carbon($e['datereleased']);
//            $result->supported = $e['supported'];
//            $result->key = $e['self_link'];
                $release->version = $e['version'];
                // Set end of life date, use heuristics: 5 years for LTS, 9 months otherwise: http://www.ubuntu.com/info/release-end-of-life
                if (strpos($e['description'], 'LTS') !== false) {
                    $release->endOfLifeDate = (new Carbon($release->releaseDate))->addYears(5);
                } else {
                    $release->endOfLifeDate = (new Carbon($release->releaseDate))->addMonths(9);
                }
                $result[$release->key] = $release;
            }

            self::$cache['ubuntu'] = $result;
        }
        return self::$cache['ubuntu'];
    }


    public static function get($key)
    {
        if ($key === 'sid') {
            return self::get('stretch');
        }
        $releases = static::findAll();
        return isset($releases[$key]) ? $releases[$key] : null;

    }
    public function getKey()
    {
        return Inflector::camel2id(strtr(!empty($this->codeName) ? $this->codeName : $this->_distribution . $this->version, [' ' => '']));
    }

    public function attributes()
    {
        $result = parent::attributes();
        $result[] = 'key';
        $result[] = 'distribution';
        $result[] = 'version';
        return $result;
    }


    public static function findCentOSReleases()
    {
        return [];
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(Http::get("https://en.wikipedia.org/wiki/CentOS"));
        foreach($dom->getElementsByTagName('table') as $table) {
            if ($table->getElementsByTagName('th')->item(1)->textContent == "Release date") {
                echo '<pre>';
                var_dump(DomTableHelper::parseTable($table));
                die();
                if (preg_match("/life-cycle\s*dates/i", $table->getAttribute("summary"))) {
                    $result = [];
                    foreach (DomTableHelper::parseTable($table) as $row) {
                        $result[] = new RedHatRelease([
                            'version' => $row["Version"],
                            'codeName' => "RHEL {$row["Version"]}",
                            'releaseDate' => new Carbon($row["General Availability"]),
                            'endOfLifeDate' => new Carbon($row["End of Production 3 (End of Production Phase)"])
                        ]);
                    }
                    return $result;
                }
            }

        }


    }

    public static function findRedHatReleases()
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(Http::get("https://access.redhat.com/support/policy/updates/errata/"));
        // find the table.
        /**
         * \DOMNode $table
         */
        foreach($dom->getElementsByTagName('table') as $table) {
            if (preg_match("/life-cycle\s*dates/i", $table->getAttribute("summary"))) {
                $result = [];
                foreach (DomTableHelper::parseTable($table) as $row) {
                    $result[] = new RedHatRelease([
                        'version' => $row["Version"],
                        'codeName' => "RHEL {$row["Version"]}",
                        'releaseDate' => new Carbon($row["General Availability"]),
                        'endOfLifeDate' => new Carbon($row["End of Production 3 (End of Production Phase)"])
                    ]);
                }
                return $result;
            }
        }

    }


    private $_phpReleases;
    final public function getPhpReleases()
    {
        if (!isset($this->_phpReleases)) {
            $this->_phpReleases = $this->phpReleases();
        }

        return $this->_phpReleases;

    }
    /**
     * @return PhpRelease[]
     */
    protected function phpReleases()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getDistribution()
    {
        return $this->_distribution;
    }

    /**
     * @return static[]
     */
    public static function findAll()
    {
        return array_merge(
            static::findUbuntuReleases(),
            static::findDebianReleases(),
            static::findRedHatReleases(),
            static::findCentOSReleases()
        );
    }

    private $_recentPhp;
    public function getMostRecentPhp()
    {
        if (!isset($this->_recentPhp)) {
            $all = $this->phpReleases();
            ArrayHelper::multisort($all, 'PHPVersion');

            $this->_recentPhp = array_pop($all);
        }
        return $this->_recentPhp;
    }

    public function getPhpSupported()
    {
        return isset($this->mostRecentPhp) && $this->mostRecentPhp->supported;
    }

}
