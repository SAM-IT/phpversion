<?php


namespace app\models;


use app\helpers\DomTableHelper;

class RedHatRelease extends LinuxRelease
{
    private static $releaseInfo = [];
    protected $_distribution = 'RHEL';

    protected function phpReleases()
    {
        if (empty(self::$releaseInfo)) {
            /** @var \DOMDocument $dom */
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTMLFile("https://distrowatch.com/table.php?distribution=redhat");
            // find the table.
            /**
             * \DOMNode $table
             */
            $tables = $dom->getElementsByTagName('table');
            foreach(DomTableHelper::parseTable($tables->item($tables->length - 2)) as $row) {
                if (preg_match("/php/i", $row["Feature"])) {
                    foreach($row as $key => $value) {
                        if (preg_match("/rhel.*(\d+)\.\d+/i", $key, $matches)) {
                            self::$releaseInfo[$matches[1]] = $value;
                        }
                    }
                    break;
                }
            }
        }

        if (isset(self::$releaseInfo[$this->version]) && is_string(self::$releaseInfo[$this->version])) {
            self::$releaseInfo[$this->version] = new PhpRelease($this, [
                'version' => self::$releaseInfo[$this->version],
                'name' => self::$releaseInfo[$this->version]
            ]);
        }

        return [self::$releaseInfo[$this->version]];
    }
}