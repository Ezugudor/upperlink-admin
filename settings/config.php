<?php
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////         This page contains constants that are group dependent               ////////////////
    ///////////////////////   (for more configs which are NOT group dependent, check config_2.php)      ////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	 $base33 = $_SERVER['DOCUMENT_ROOT'];
	 $base = $_SERVER['DOCUMENT_ROOT'];
	 $base2 = "../../../music/";
	 define('PASSPORT_PATH',$base.'/resources/passport/');
	 define('RESUME_PATH',$base.'/resources/resume/');
	 // define('GENERAL_ROOT', '/myprojects/group/yearbook/public/');

	 define('PATH_AUDIO','audio/');
	 define('PATH_IMAGE','images/');
	 define('PATH_AUDIO_FULL',  $base2.'audio/');
	 define('PATH_TRACK_COVER_FULL',  $base2.'images/');

     
     /////////// FOR EXTERNAL ////////////////////

	 define('ROOT', '/');
	 define('IMG_PATH_FULL', $base.'images/');
	 define('MODULE_TYPE', 'dev');
	 define('THUMB_SURFIX', '_thumb');
	 define('AUTHROOT', '/auth/');
	 // define('AUTHROOTFULL',$base. '/auth/');
	 define('FULLROOT', $base33.'/');
	 // define('SYS_ICONS', ROOT.'sys_pics/');

	
?>