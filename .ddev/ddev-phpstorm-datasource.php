#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * USAGE
 *
 * run this script on the post start hook of ddev:
 *
 * hooks:
 *     post-start:
 *         - exec-host: "ddev describe -j | ddev exec vendor/bin/ddev-phpstorm-datasource.php"
 *
 * It uses the environment variables DDEV_APPROOT and DDEV_PROJECT
 * to setup or update a DB connection (data source) in PHPStorm.
 *
 * The script is limited to resource URLs looking like this:
 *
 * jdbc:mariadb://localhost:49166/db_name
 *
 * the scheme (mariadb), host, port and path might vary but jdbc has to be in place
 * and no other URL parts are considered
 */

namespace Inpsyde\DdevTools;

const FILE_DATA_SOURCES = '/dataSources.xml';
const FILE_TEMPLATE_DATA_SOURCES = '/dataSources.xml.dist';

// incomplete reverse part of parse_url
function build_url(array $urlParts): string
{
    $scheme = $urlParts['scheme'] ?? 'mariadb';
    $host = $urlParts['host'] ?? 'localhost';
    $port = $urlParts['port'] ?? '3306';
    $path = $urlParts['path'] ?? '';

    return $scheme . '://' . $host . ':' . $port . $path;
}

/**
 * @link https://stackoverflow.com/a/44858669/2169046
 */
function generateUuid(): string {

    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);


    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

try {
    $appRoot = getenv("DDEV_APPROOT");
    if (!$appRoot && getenv('IS_DDEV_PROJECT')) {
        $appRoot = '/var/www/html';
    }

    if(!$appRoot) {
        throw new \RuntimeException("DDEV_APPROOT not defined. Run this script as DDEV hook or inside DDEV container");
    }

    $sourceName = getenv('DDEV_PROJECT');
    if (!$sourceName) {
        throw new \RuntimeException("DDEV_PROJECT not defined. Run this script as DDEV hook");
    }

    $ideaDir = $appRoot . '/.idea';
    if(!is_dir($ideaDir)) {
        echo "No PHPStorm directory (.idea) found. Aborting" . PHP_EOL;
        exit;
    }
    $relIdeDir = basename($ideaDir);
    $templateDir = dirname(__DIR__) . '/templates';

    if (!file_exists($ideaDir . FILE_DATA_SOURCES)
        && file_exists($templateDir . FILE_TEMPLATE_DATA_SOURCES)) {
        $templateXml = new \SimpleXMLElement(file_get_contents($templateDir . FILE_TEMPLATE_DATA_SOURCES));
        $templateSourceNode = $templateXml->xpath('//data-source[@name="DDEV_PROJECT"]')[0];
        $templateSourceNode['name'] = $sourceName;
        $templateXml->asXML($ideaDir . FILE_DATA_SOURCES);

        echo "Placed {$relIdeDir}" . FILE_DATA_SOURCES . " as it did not exist yet" . PHP_EOL;
    }

    $stdin = '';
    while ($line = fgets(STDIN)) {
        $stdin .= $line;
    }

    $ddevParameter = json_decode($stdin, true);
    if (!$ddevParameter) {
        throw new \RuntimeException("Could not decode input from STDIN. Make sure its valid JSON");
    }

    if (!isset($ddevParameter['raw']['dbinfo']['published_port'])) {
        throw new \RuntimeException("Missing path .raw.dbinfo.published_port");
    }

    $dbPort = (int)$ddevParameter['raw']['dbinfo']['published_port'];

    $dataSource = new \SimpleXMLElement(file_get_contents($ideaDir . FILE_DATA_SOURCES));
    $dbUrlNodes = $dataSource->xpath("//data-source[@name='{$sourceName}']/jdbc-url");

    if (empty($dbUrlNodes)) {
        throw new \RuntimeException(
            "Could not find xPath //data-source[@name='" . $sourceName . "']/jdbc-url from .idea/dataSource.xml"
        );
    }
    $dbUrlNode = $dbUrlNodes[0];
    // URL is actually invalid 'jdbc:mariadb://localhost:49160/db_test'
    $dbUrl = str_replace('jdbc:', '', (string)$dbUrlNode);
    $dbUrlParts = parse_url((string)$dbUrl);

    $recentPort = (int)$dbUrlParts['port'];
    if ($recentPort === $dbPort) {
        echo "DB ports did not change" . PHP_EOL;
        exit(0);
    }
    $dbUrlParts['port'] = $dbPort;
    $dbUrlNode[0] = 'jdbc:' . build_url($dbUrlParts);

    //Add a valid UUID
    $dataSourceNode = $dataSource->xpath("//data-source[@name='{$sourceName}']");
    if (! (string)$dataSourceNode[0]['uuid']) {
        $dataSourceNode[0]['uuid'] = generateUuid();
        echo "Set UUID for data source {$sourceName} in {$relIdeDir}" . FILE_DATA_SOURCES . PHP_EOL;
    }

    $dataSource->asXML($ideaDir . FILE_DATA_SOURCES);

    echo "DB Port changed from {$recentPort} to {$dbPort} for data source {$sourceName} in {$relIdeDir}" . FILE_DATA_SOURCES . PHP_EOL;

} catch (\Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}

