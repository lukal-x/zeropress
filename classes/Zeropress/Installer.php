<?php

namespace Zeropress;

use Exception;
use InvalidArgumentException;
use zdb;

/**
 * effectively a migration system for the Zeropress system itself
 */
class Installer {
    private string $migrationTable = "migration";

    private bool $dryRun = false;
    private $versions = [];

    public function __construct() {
        $files = glob("migrations/*.sql");

        $this->versions = array_map(function($fn) {
            return str_replace(".sql", "", basename($fn));
        }, $files);

        usort($this->versions, "version_compare");
    } 
    
    /**
     * check if any migration was ran
     */
    public function isInstalled() : bool {
        $results = zdb::getArraySafe("SELECT version FROM {$this->migrationTable};");
        return 0 < count($results);
    }

    public function getCurrent() : string {
        if (! $this->isInstalled()) {
            throw new Exception('System not installed!');
        }

        $results = zdb::getArraySafe(
            "SELECT version FROM {$this->migrationTable};"
        );

        return end($results)["version"];
    }
    
    private function getLatest() : string { 
        return end($this->versions);
    }
    
    /**
     * check if there are newer migrations to run
     */
    public function isLatest() : bool {
        if (! $this->isInstalled()) return false;
        return version_compare($this->getCurrent(), $this->getLatest(), '>=');
    }

    /**
     * upgrade/downgrade to match exact version
     */
    public function installVersion(string $version) : bool {
        if (! in_array($version, $this->versions)) {
            throw new InvalidArgumentException("Invalid target version: {$version}");
        }
        
        if (! $this->isInstalled()) {
            zdb::writeSql(
                "CREATE TABLE IF NOT EXISTS {$this->migrationTable} (version VARCHAR(255) PRIMARY KEY);"
            );
            zdb::writeRow("INSERT", $this->migrationTable, ["version" => "v0.0.0"]);
        }

        $current = $this->getCurrent();

        if ($current == $version) {
            return true;
        }

        $a = array_search($version, $this->versions);
        $b = array_search($current, $this->versions);

        if ($a === false || $b === false) {
            throw new Exception("Version mismatch! {$a} - {$b}");
        }

        $steps = $b > $a ? range($a, $b) : range($b, $a);
        
        zdb::writeSql("START TRANSACTION;");
        try {
            foreach($steps as $index) {
                $change = $this->versions[$index];

                # @todo remove comments from SQL files
                $sql = file_get_contents("migrations/{$change}.sql");

                $statements = explode("\n\n", $sql);
                foreach($statements as $statement) {
                    if (! $this->dryRun) {
                        zdb::writeSql($statement);

                        $results = zdb::getArraySafe("SELECT version FROM {$this->migrationTable};");

                        zdb::writeRow(
                            "UPDATE",
                            $this->migrationTable,
                            ["version" => $change],
                            ["version" => end($results)["version"]]
                        );
                    }
                }
            }
            zdb::writeSql("COMMIT;");
        } catch(Exception $ex) {
            zdb::writeSql("ROLLBACK;");
            throw $ex;
        }
        
        return true;
    }

}
