<?php
/*
	File:		globals.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		Handles the main logic when a user is logged into the game.
	Author:		TheMasterGeneral
	Website: 	https://github.com/MasterGeneral156/chivalry-engine
	MIT License

	Copyright (c) 2019 TheMasterGeneral

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
*/
//Profiler start time
$StartTime = microtime();
//If file is loaded directly.
if (strpos($_SERVER['PHP_SELF'], "globals.php") !== false) {
    exit;
}
//Set session name, then start session.
session_name('CEV2');
session_start();
$time = time();
header('X-Frame-Options: SAMEORIGIN');
//If session has not started, regenerate session ID, then start it.
if (!isset($_SESSION['started'])) {
    session_regenerate_id();
    $_SESSION['started'] = true;
}
ob_start();
//Require the error handler and developer helper files.
require "lib/basic_error_handler.php";
set_error_handler('error_php');
//Require main functions file.
require "global_func.php";
$domain = getGameURL();
//If user is not logged in, redirect to login page.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] == 0) {
    $login_url = "login.php";
    header("Location: {$login_url}");
    exit;
}
//If user was last active over 15 minutes ago, redirect to login to keep account safe.
if (isset($_SESSION['last_active']) && ($time - $_SESSION['last_active'] > 1800)) {
    header("Location: logout.php");
    exit;
}
//Update last active time.
$_SESSION['last_active'] = $time;
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : 0;
require "header.php";
include "config.php";
define("MONO_ON", 1);
//Require the database wrapper and connect to database.
require "class/class_db_{$_CONFIG['driver']}.php";
$db = new database;
$db->configure($_CONFIG['hostname'], $_CONFIG['username'], $_CONFIG['password'], $_CONFIG['database'], $_CONFIG['persistent']);
$db->connect();
$c = $db->connection_id;
$set = array();
$settq = $db->query("SELECT * FROM `settings`");
//Settings get resolved to be used easily elsewhere.
while ($r = $db->fetch_row($settq)) {
    $set[$r['setting_name']] = $r['setting_value'];
}
global $jobquery, $housequery, $voterquery;
if (isset($jobquery) && $jobquery) {
    $is =
        $db->query(
            "SELECT `u`.*, `us`.*, `j`.*, `jr`.*
                     FROM `users` AS `u`
                     INNER JOIN `userstats` AS `us`
                     ON `u`.`userid`=`us`.`userid`
                     LEFT JOIN `jobs` AS `j` ON `j`.`jRANK` = `u`.`job`
                     LEFT JOIN `job_ranks` AS `jr`
                     ON `jr`.`jrID` = `u`.`jobrank`
                     WHERE `u`.`userid` = {$userid}
                     LIMIT 1");
} else if (isset($housequery) && $housequery) {
    $is =
        $db->query(
            "SELECT `u`.*, `us`.*, `e`.*
                     FROM `users` AS `u`
                     INNER JOIN `userstats` AS `us`
                     ON `u`.`userid`=`us`.`userid`
                     LEFT JOIN `estates` AS `e` ON `e`.`house_will` = `u`.`maxwill`
                     WHERE `u`.`userid` = {$userid}
                     LIMIT 1");
} else if (isset($voterquery) && $voterquery) {
    $UIDB = $db->query("SELECT * FROM `uservotes` WHERE `userid` = {$userid}");
    if (!($db->num_rows($UIDB))) {
        $db->query("INSERT INTO `uservotes` (`userid`, `voted`) VALUES ('{$userid}', '');");
    }
    $is =
        $db->query(
            "SELECT `u`.*, `us`.*, `uv`.*
                     FROM `users` AS `u`
                     INNER JOIN `userstats` AS `us`
                     ON `u`.`userid`=`us`.`userid`
					 INNER JOIN `uservotes` AS `uv`
                     ON `u`.`userid`=`uv`.`userid`
                     WHERE `u`.`userid` = {$userid}
                     LIMIT 1");
} else {
    $is =
        $db->query(
            "SELECT `u`.*, `us`.*
                     FROM `users` AS `u`
                     INNER JOIN `userstats` AS `us`
                     ON `u`.`userid`=`us`.`userid`
                     WHERE `u`.`userid` = {$userid}
                     LIMIT 1");
}
//Put user's data into friendly variable.
$ir = $db->fetch_row($is);
//If user's account is forced to log out, close session.
if ($ir['force_logout'] != 'false') {
    $db->query("UPDATE `users` SET `force_logout` = 'false' WHERE `userid` = {$userid}");
    session_unset();
    session_destroy();
    $login_url = "login.php";
    header("Location: {$login_url}");
    exit;
}
//If the user's account has been logged in elsewhere, terminate current session.
if (($ir['last_login'] > $_SESSION['last_login']) && !($ir['last_login'] == $_SESSION['last_login'])) {
    session_unset();
    session_destroy();
    $login_url = "login.php";
    header("Location: {$login_url}");
    exit;
}
//Basic chceks around the game.
checkLevel();
checkData();
$h = new headers;
//Include API file.
include("class/class_api.php");
$api = new api;
$api->user = new user;
$api->guild = new guild;
$api->game = new game;
//If requested file doesn't want the header hidden.
if (isset($nohdr) == false || !$nohdr) {
    $h->startheaders();
    $fm = number_format($ir['primary_currency']);
    $cm = number_format($ir['secondary_currency']);
    $lv = date('F j, Y, g:i a', $ir['laston']);
    global $atkpage;
    if ($atkpage) {
        $h->userdata($ir, 0);
    } else {
        $h->userdata($ir);
    }
    global $menuhide;
}
//Run the crons if possible.
foreach (glob("crons/*.php") as $filename) {
    include $filename;
}
$get = $db->query("SELECT `sip_recipe`,`sip_user` FROM `smelt_inprogress` WHERE `sip_time` < {$time}");
//Select completed smelting recipes and give to the user.
if ($db->num_rows($get)) {
    $r = $db->fetch_row($get);
    $r2 = $db->fetch_row($db->query("SELECT * FROM `smelt_recipes` WHERE `smelt_id` = {$r}"));
    $api->user->giveItem($r['user'], $r2['smelt_output'], $r2['smelt_qty_output']);
    $api->user->addNotification($r['user'], "You have successfully smelted your {$r2['smelt_qty_output']} " . $api->SystemItemIDtoName($r2['smelt_output']) . "(s).");
    $db->query("DELETE FROM `smelt_inprogress` WHERE `sip_user`={$r['user']} AND `sip_time` < {$time}");
}