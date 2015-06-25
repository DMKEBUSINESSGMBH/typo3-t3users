<?php
/**
 * Backend Modul
 * @package tx_t3users
 * @subpackage tx_t3users_mod
 */

// DO NOT REMOVE OR CHANGE THESE 2 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/t3users/mod/');
$BACK_PATH = '../../../../typo3/';
$MCONF['name'] = 'web_txt3usersM1';

$MCONF['access'] = 'user,group';
$MCONF['script'] = 'index.php';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:t3users/mod/locallang_mod.php';


$REQUIRE_PATH = $BACK_PATH;
if (!@is_readable($REQUIRE_PATH . 'sysext/'))
{
	$PATH_thisScript = str_replace('//', '/', str_replace('\\', '/',
			(PHP_SAPI == 'fpm-fcgi' || PHP_SAPI == 'cgi' || PHP_SAPI == 'isapi' || PHP_SAPI == 'cgi-fcgi') &&
			(isset($_SERVER['ORIG_PATH_TRANSLATED']) ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) ?
			(isset($_SERVER['ORIG_PATH_TRANSLATED']) ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) :
			(isset($_SERVER['ORIG_SCRIPT_FILENAME']) ? $_SERVER['ORIG_SCRIPT_FILENAME'] : $_SERVER['SCRIPT_FILENAME']))
	);

	// Aufruf direkt über das backendmodul
	if ($strpos = strpos($PATH_thisScript, '/typo3conf/'))
	{
		$REQUIRE_PATH = substr($PATH_thisScript, 0, $strpos + 1) .'typo3/';
	}
	// Aufruf direkt aus dem typo3 backend
	elseif($strpos = strpos($PATH_thisScript, '/typo3/'))
	{
		$REQUIRE_PATH = substr($PATH_thisScript, 0, $strpos + 1) .'typo3/';
	}

	if (!is_readable($REQUIRE_PATH))
	{
		echo '<h1>Es konnte kein lesbarer REQUIRE_PATH gefunden werden</h1>';
		echo '<h2>BACK_PATH</h2>'.'<pre>'.$BACK_PATH.'</pre>';
		echo '<h2>REQUIRE_PATH</h2>'.'<pre>'.$REQUIRE_PATH.'</pre>';
		echo '<h2>PATH_THISSCRIPT</h2>'.'<pre>'.$PATH_thisScript.'</pre>';
		exit('EXIT: '.__FILE__.'&'.__METHOD__.' Line: '.__LINE__);
	}
}

define('ICON_OK', -1);
define('ICON_INFO', 1);
define('ICON_WARN', 2);
define('ICON_FATAL', 3);

?>
