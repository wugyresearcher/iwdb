<?php
/**
 * bootstrapfile der iwdb <br>
 * for help see:
 *
 * @link       https://handels-gilde.org/?www/forum/index.php;board=1099.0 Entwicklerforum
 * @link       https://github.com/iwdb/iwdb github repo
 *
 * @author     masel <masel789@googlemail.com>
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL version 2 or any later version
 * @package    iwdb
 * @subpackage Bootstrap
 */

//ToDo: checkinstall
if (version_compare(PHP_VERSION, '5.3', '<')) {
    echo "Die Serversoftware php in der Version " . PHP_VERSION . " wird leider nicht mehr unterstützt.<br>\n";
    echo "Benötigt wird mindestens die Version 5.3<br>\n";
    exit;
}

//test the bcrypt hashing, should work php >5.3.7 and backported versions
$hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
$test = crypt("password", $hash);
if ($test !== $hash) {
    exit ('bcrypt is not working properly!');
}

//all errors on
error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", '1');
libxml_use_internal_errors(true);
$error = '';                 //veraltet

//set some standards
date_default_timezone_set('Europe/Berlin');
mb_internal_encoding("UTF-8"); //just to make sure we are talking the same language
mb_http_output("UTF-8");
header('Content-Type: text/html; charset=UTF-8');
header('X-XSS-Protection: 1; mode=block');

// Das aktuelle Datum wird pro Skriptaufruf nur einmal geholt, +-x kann
// entsprechend hier geändert werden
define("CURRENT_UNIX_TIME", time());

// Basisdefinitionen für Zeiträume.
define("MINUTE", 60);
define("HOUR", 60 * MINUTE);
define("DAY", 24 * HOUR);

// veraltet
$config_date = CURRENT_UNIX_TIME;
$MINUTES = MINUTE;
$HOURS = HOUR;
$DAYS = DAY;

// some other constants
// ToDo: clean them up
define('DEBUG', true);
define('LOG_DB_QUERIES', false);
define('IRA', true);
define('NEBULA', true);
define('SPECIALSEARCH', true);
define('ALLY_MEMBERS_ON_MAP', true);
define('GENERAL_ERROR', 'GENERAL_ERROR'); //veraltet
define("DB_MAX_INSERTS", 1000);
define('SITTEN_DISABLED', 2);
define('SITTEN_ONLY_NEWTASKS', 0);
define('SITTEN_ONLY_LOGINS', 3);
define('SITTEN_BOTH', 1);

require_once 'includes/dBug.php'; //bessere Debugausgabe
require_once 'includes/debug.php'; //Debug Funktionen
require_once 'includes/function.php'; //sonstige Funktionen
require_once 'includes/db_mysql.php';
require_once 'parser/parser_help.php'; //ausgelagerte Parserhilfsfunktionen
require_once 'config/configsql.php'; //Datenbank Zugangsdaten laden

//DB Verbindung herstellen
$db = new db();
$link_id = $db->db_connect($db_host, $db_user, $db_pass, $db_name)
    or error(GENERAL_ERROR, 'Could not connect to database.', '', __FILE__, __LINE__);

// Tabellennamen - Definition des Einstiegsnamens
$db_tb_iwdbtabellen = $db_prefix . "iwdbtabellen";

// Die restlichen Tabellennamen werden aus der DB gelesen.
$sql    = "SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = '$db_name' AND table_name LIKE '$db_prefix%'";
$result = $db->db_query($sql);
while ($row = $db->db_fetch_array($result)) {
    $tbname    = "db_tb_" . mb_substr($row['table_name'], mb_strlen($db_prefix));
    ${$tbname} = $row['table_name'];
}

require_once 'config/config.php'; //IWDB Einstellungen
require_once 'config/configally.php'; //Allianzeinstellungen

$action = preg_replace('/[^a-zA-Z0-9_-]/', '', mb_substr(getVar('action'), 0, 100)); //get and filter actionstring (limited to 100 chars)
if (empty($action)) {
    $action = $config_default_action;
}

require_once("includes/sid.php");

$sql = "SELECT gesperrt FROM " . $db_tb_user . " WHERE id = '" . $user_id . "'";
$result_g = $db->db_query($sql)
    or error(GENERAL_ERROR, 'Could not query config information.', '', __FILE__, __LINE__, $sql);
$row_g = $db->db_fetch_array($result_g);
if ($row_g['gesperrt'] == 1) {
    die ('<div style="text-align:center;color:red">ihr Account ist gesperrt worden!</div>');
}