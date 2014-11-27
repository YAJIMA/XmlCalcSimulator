<?php
/**
 * 簡易見積シミュレータ - XmlSimulater
 * 
 * @package     XmlSimulater
 * @author      Y.Yajima <yajima@hatchbit.jp>
 * @copyright   2014, HatchBit & Co.
 * @license     http://www.hatchbit.jp/resource/license.html
 * @link        http://www.hatchbit.jp
 * @since       Version 2.0
 * @filesource
 */

// SESSION
if(!isset($_SESSION)){
	session_name("SESSION_SITE_NAME");
	session_start();
}
require dirname(__FILE__).'/core.php';

if(isset($_POST)){
	FormEncode($_POST);
}
?>