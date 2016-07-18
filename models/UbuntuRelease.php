<?php


namespace app\models;

use yii\helpers\StringHelper;

class UbuntuRelease extends LinuxRelease
{
    private static $phpReleases = [];
    protected $_distribution = 'Ubuntu';

    protected function phpReleases()
    {
        if (empty(self::$phpReleases)) {
            // Get releases.
            $urls = [
                "https://api.launchpad.net/1.0/ubuntu/+archive/primary?ws.op=getPublishedSources&source_name=php5&exact_match=true&ws.size=300",
                "https://api.launchpad.net/1.0/ubuntu/+archive/primary?ws.op=getPublishedSources&source_name=php7.0&exact_match=true&ws.size=300"
            ];
            $entries = [];
            do {
                $json = json_decode(file_get_contents(array_pop($urls)), true);
                if (isset($json['next_collection_link'])) {
                    $urls[] = $json['next_collection_link'];
                }
                foreach($json['entries'] as $entry) {
                    $release = [
                        'key' => $entry['self_link'],
                        'status' => $entry['status'],
                        'version' => $entry['source_package_version'],
                        'name' => $entry['display_name']
                    ];
                    $entries[StringHelper::basename($entry['distro_series_link'])][] = $release;
                }
            } while (!empty($urls));

            self::$phpReleases = $entries;
        }
        if (isset(self::$phpReleases[$this->key])) {
            foreach (self::$phpReleases[$this->key] as &$release) {
                if (is_array($release)) {
                    $release = new PhpRelease($this, $release);
                }
            }
            return self::$phpReleases[$this->key];
        }
        return [];
    }
}