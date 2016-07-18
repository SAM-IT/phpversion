<?php


namespace app\models;


class DebianRelease extends LinuxRelease
{
    private static $phpReleases = [];


    protected $_distribution = 'Debian';

    protected function phpReleases()
    {
        if (empty(self::$phpReleases)) {
            $entries = [];
            $urls = [
                "https://sources.debian.net/api/src/php5",
                "https://sources.debian.net/api/src/php7.0",
            ];
            do {
                $json = json_decode(file_get_contents(array_pop($urls)), true);
                if (isset($json['next_collection_link'])) {
                    $urls[] = $json['next_collection_link'];
                }
                foreach ($json['versions'] as $entry) {
                    foreach ($entry['suites'] as $suite) {
                        if (null !== $linux = LinuxRelease::get($suite)) {
                            $release = new PhpRelease($linux);
                            $release->name = $json['package'];
                            $release->status = "Unknown (Debian)";
                            $release->version = $entry['version'];
                            $release->key = $suite . $release->version;
                            $entries[] = $release;
                            // If this release is for a normal version it also applies to the LTS version.
                            $suite .= '-lts';
                            if (null !== $linux = LinuxRelease::get($suite)) {
                                $ltsRelease = clone $release;
                                $ltsRelease->linux = $linux;
                                $ltsRelease->key = $suite . $release->version;
                                $entries[] = $ltsRelease;
                            }
                        }
                    }
                }
            } while (!empty($urls));

            self::$phpReleases = $entries;
        }
        return array_filter(self::$phpReleases, function(PhpRelease $release) {
            return $release->linux->key === $this->key;
        });
    }
}