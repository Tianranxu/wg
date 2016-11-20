<?php


class Migrator {

    public static function finished() {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'migration';
        if (file_exists($filePath)) {
            return static::load($filePath);
        } else {
            $f = fopen($filePath, "a+");
            fclose($f);
            return array();
        }
    }

    public static function load($file) {
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        ini_set('auto_detect_line_endings', $autodetect);
        return $lines;
    }

    public static function endWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, -$length) === $needle);
    }

    public static function unmigrated() {
        $sqls = array();
        $finished = static::finished();
        foreach ($finished as $value) {
            echo "Migration: {$value} is done before " .PHP_EOL;
        }
        foreach (scandir(__DIR__) as $file) {
            if (static::endWith($file, '.sql') && !in_array($file, $finished)){
                $sqls[] = $file;
            }
        }
        sort($sqls);
        return $sqls;
    }

    public static function append($migrateName) {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'migration';
        $f = fopen($filePath, "a+");
        fwrite($f, $migrateName.PHP_EOL);
        fclose($f);
        echo "Migration: {$migrateName} is finished".PHP_EOL.PHP_EOL;
    }

    public static function migrate($mysqli, $name) {
        $lines = static::load(__DIR__. DIRECTORY_SEPARATOR. $name);
        $lines = explode(";", implode("\n", $lines));
        echo "Running Migration {$name}, Total SQL: ". count($lines) . PHP_EOL;
        foreach ($lines as $line) {
            @$res = $mysqli->query($line);
        }
        static::append($name);
    }

    public static function run() {
        echo "请注意:sql文件中,‘;’只能出现在每一句SQL的结尾,不能出现在SQL语句中间(比如字段的comments当中)".PHP_EOL;
        $unmigrated = static::unmigrated();

        $config = require dirname(__DIR__) . '/Application/Common/Conf/config.php';
        $mysqli = new mysqli($config['DB_HOST'],
            $config['DB_USER'], $config['DB_PWD'], $config['DB_NAME']);
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
        }
        $mysqli->set_charset("utf8");
        echo PHP_EOL."************** Now Migrate      ****************" .PHP_EOL;
        foreach ($unmigrated as $value) {
            static::migrate($mysqli, $value);
        }
        echo PHP_EOL."************** Migrate Finished ****************" .PHP_EOL.PHP_EOL;
        $mysqli->close();
    }


}

Migrator::run();

?>
