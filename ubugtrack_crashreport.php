<?php
/********************************************************************************
 * www.ubugtrack.com
 * Send App Error to uBugtrack crashes service
 * Created by Guillaume
 * ******************************************************************************
 */

$UBUGTRACK_CRASH_KEY="";
$UBUGTRACK_APP_VERSION="";

function ubugtrack_shutdown_handler()
{
	global $UBUGTRACK_CRASH_KEY;
	global $UBUGTRACK_APP_VERSION;
	
	$error = error_get_last();

	if( $error !== NULL)
	{	
		$errnoCode = '';
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];
		
		switch ($errno)
		{
			case E_ERROR: 			$errnoCode = 'E_ERROR';break;
			case E_PARSE:			$errnoCode = 'E_PARSE';break;
			case E_CORE_ERROR:		$errnoCode = 'E_CORE_ERROR';break;
			case E_CORE_WARNING:	$errnoCode = 'E_CORE_WARNING';break;
			case E_COMPILE_ERROR:	$errnoCode = 'E_COMPILE_ERROR';break;
			case E_COMPILE_WARNING:	$errnoCode = 'E_COMPILE_WARNING';break;
			case E_USER_ERROR:		$errnoCode = 'E_USER_ERROR';break;
		}
		
		if (!empty($errnoCode))
		{	
			$appKey = $UBUGTRACK_CRASH_KEY;
			$manufacturer = 'PHP';
			$model = phpversion();
			$appversion = $UBUGTRACK_APP_VERSION;
			
			$logtitle = $errnoCode.' '.$errstr;
			$logfull = $errnoCode.' '.$errstr.' in file : '.$errfile.' - Line : '.$errline;
			
			$url = 'https://ubugtrack.com/crashdump.php';
			$data = array('project_key' => $appKey ,'manufacturer' => $manufacturer, 'model' => $model,'appversion' => $appversion, 'logtitle' => $logtitle, 'logfull' => $logfull);
	
			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query($data)));
			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			return $result;
		}
	}
}

/*
 * Call this function to init the Crash Repporter
 */
function initCrashReporter($crashKey,$version)
{
	global $UBUGTRACK_CRASH_KEY;
	global $UBUGTRACK_APP_VERSION;
	
	$UBUGTRACK_CRASH_KEY = $crashKey;
	$UBUGTRACK_APP_VERSION = $version;
	register_shutdown_function("ubugtrack_shutdown_handler");
}


?>