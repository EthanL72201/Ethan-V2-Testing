<?php
/*
	File:		installer.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		The main logic for the Chivalry Engine installer.
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
if (file_exists('./installer.lock'))
{
    exit;
}
$Version=('pre-2.0d');
$Build=('pre-2.0d');
define('MONO_ON', 1);
session_name('CEV2');
session_start();
if (!isset($_SESSION['started']))
{
    session_regenerate_id();
    $_SESSION['started'] = true;
}
require_once('installer_head.php');
require_once('lib/installer_error_handler.php');
set_error_handler('error_php');
if (!isset($_GET['code']))
{
    $_GET['code'] = '';
}
switch ($_GET['code'])
{
case "install":
    install();
    break;
case "config":
    config();
    break;
default:
    diagnostics();
    break;
}

function menuprint($highlight)
{
    $items =
            array('diag' => '1. Diagnostics', 'input' => '2. Configuration',
                    'sql' => '3. Installation & Extras',);
    $c = 0;
    echo "<hr />";
    foreach ($items as $k => $v)
    {
        $c++;
        if ($c > 1)
        {
            echo ' >> ';
        }
        if ($k == $highlight)
        {
            echo '<span class="text-dark">' . $v . '</span>';
        }
        else
        {
            echo '<span class="text-muted">' . $v . '</span>';
        }
    }
    echo '<hr />';
}

function diagnostics()
{
    menuprint("diag");
    if (version_compare(phpversion(), '7.0.0') < 0)
    {
        $pv = '<span class="text-danger">Failed</span>';
        $pvf = 0;
    }
    else
    {
        $pv = "<span class='text-success''>Pass! PHP Version is " . phpversion() . "!</span>";
        $pvf = 1;
    }
    if (is_writable('./'))
    {
        $wv = '<span class="text-success">Pass!</span>';
        $wvf = 1;
    }
    else
    {
        $wv = '<span class="text-danger">Fail!</span>';
        $wvf = 0;
    }
	if (function_exists('openssl_random_pseudo_bytes'))
    {
        $ov = '<span class="text-success">Pass!</span>';
        $ovf = 1;
    }
    else
    {
        $ov = '<span class="text-danger">Fail!</span>';
        $ovf = 0;
    }
	if (function_exists('password_hash'))
    {
        $hv = '<span class="text-success">Pass!</span>';
        $hvf = 1;
    }
    else
    {
        $hv = '<span class="text-danger">Fail!</span>';
        $hvf = 0;
    }
	if (extension_loaded('pdo_mysql'))
    {
        $pdv = '<span class="text-success">PDO detected. Please use PDO!</span>';
        $pdf = 1;
    }
    elseif (function_exists('mysqli_connect'))
    {
        $pdv = '<span class="text-warning">PDO not detected. Use MySQLi!</span>';
        $pdf = 1;
    }
	else
	{
		$pdv = '<span class="text-danger">No acceptable database handler found. Installer will not continue.</span>';
        $pdf = 0;
	}
    if (function_exists('curl_init'))
    {
        $cuv = "<span class='text-success'>Pass!</span>";
        $cuf=1;
    }
    else
     {
        $cuv = "<span class='text-danger'>Fail</span>";
        $cuf=0;
     }
    if (function_exists('fopen'))
    {
        $fov = "<span class='text-success'>Success</span>";
        $fof=1;
    }
    else
     {
        $fov = "<span class='text-danger'>Fail</span>";
        $fof=0;
     }
    echo "
    <h3>Basic Diagnostic Results:</h3>
    <table class='table table-bordered table-hover'>
    		<tr>
    			<td>Is the server's PHP Version greater than 7.0.0?</td>
    			<td>{$pv}</td>
    		</tr>
    		<tr>
    			<td>Is the game folder writable?</td>
    			<td>{$wv}</td>
    		</tr>
			<tr>
    			<td>Database Recommendation?</td>
    			<td>{$pdv}</td>
    		</tr>
			<tr>
    			<td>Password_Hash available?</td>
    			<td>{$hv}</td>
    		</tr>
			<tr>
    			<td>OpenSSL available?</td>
    			<td>{$ov}</td>
    		</tr>
    		<tr>
    			<td>cURL available?</td>
    			<td>{$cuv}</td>
    		</tr>
    		<tr>
    			<td>fopen available?</td>
    			<td>{$fov}</td>
    		</tr>
    		<tr>
    			<td>Is Chivalry Engine up to date?</td>
    			<td>
        			" . getEngineVersion() . "
        		</td>
        	</tr>
    </table>
       ";
    if ($pvf + $pdf + $wvf + $hvf + $ovf + $cuv + $fov < 7)
    {
        echo "
		<hr />
		<span class='text-danger'>
		One of the basic diagnostics failed, so Setup cannot continue.
		Please fix the ones that failed and try again.
		</span>
		<hr />
   		";
    }
    else
    {
        echo "
		<hr />
		&gt; <a href='installer.php?code=config'>Next Step</a>
		<hr />
   		";
    }
}

function config()
{
    menuprint("input");
    echo "
    <h3>Configuration:</h3>
    <form action='installer.php?code=install' method='post'>
    <table class='table table-bordered table-hover'>
    		<tr>
    			<th colp='2'>Database Config</th>
    		</tr>
    		<tr>
    			<th>Database Driver</td>
    			<td>
    				<select name='driver' class='form-control' type='dropdown'>
       ";
    if (function_exists('mysqli_connect'))
    {
        echo '<option value="mysqli">MySQLi Enhanced</option>';
    }
		else
	{
		echo '<option>MySQLi not detected on your server.</option>';
	}
	if (extension_loaded('pdo'))
    {
        echo '<option value="pdo">PHP Data Objects (PDO)</option>';
    }
	else
	{
		echo '<option>No acceptable database handler detected on your server.</option>';
	}
    echo "
    				</select>
    			</th>
    		</tr>
    		<tr>
    			<th>
    				Hostname<br />
    				<small>This is usually localhost</small>
    			</th>
    			<td><input type='text' name='hostname' class='form-control' value='localhost' required='1' /></td>
    		</tr>
    		<tr>
    			<th>
    				Username<br />
    				<small>The user must be able to use the database</small>
    			</th>
    			<td><input type='text' name='username' class='form-control' required='1' /></td>
    		</tr>
    		<tr>
    			<th>Password</th>
    			<td><input type='password' name='password' class='form-control' required='1' value='' /></td>
    		</tr>
    		<tr>
    			<th>
    				Database Name<br />
    				<small>The database should not have any other software using it.</small>
    			</th>
    			<td><input type='text' name='database' class='form-control' required='1' value='' /></td>
    		</tr>
    		<tr>
    			<th>
    				Send Install Info?<br />
    				<small>Just your domain name, codebase version, install date, game name and database type.</small>
    			</th>
    			<td>
    				<select name='analytics' class='form-control' required='1' type='dropdown'>
    					<option value='true'>True</option>
    					<option value='false'>False</option>
    				</select>
    			</td>
    		</tr>
    		<tr>
    			<th colp='2'>Game Config</th>
    		</tr>
    		<tr>
    			<th>Game Name</th>
    			<td><input type='text' name='game_name' class='form-control' required='1' /></td>
    		</tr>
    		<tr>
    			<th>
    				Game Owner<br />
    				<small>This can be your nick, real name, or a company</small>
    			</th>
    			<td><input type='text' name='game_owner' class='form-control' required='1' /></td>
    		</tr>
    		<tr>
    			<th>
    				Game Description<br />
    				<small>This is shown on the login page.</small>
    			</th>
    			<td><textarea name='game_description' class='form-control' required='1'></textarea></td>
    		</tr>
    		<tr>
    			<th>
    				PayPal Address<br />
    				<small>This is where the payments for game DPs go.  Must be at least Premier.</small>
    			</th>
    			<td><input type='email' name='paypal' class='form-control' required='1' /></td>
    		</tr>
			<tr>
    			<th>
    				Password Cost<br />
    				<small>How much resources should you allocate towards generating a user's password?<br /> 
					Benchmark your server <a href='password_benchmark.php'>here</a>.</small>
    			</th>
    			<td><input type='number' class='form-control' value='10' required='1' min='5' max='15' name='password_effort'></td>
    		</tr>
			<tr>
    			<th>
    				ReCaptcha Public Key<br />
    				<small><a href='https://www.google.com/recaptcha/admin'>https://www.google.com/recaptcha/admin</a></small>
    			</th>
    			<td><input type='text' name='recappub' class='form-control' required='1' /></td>
    		</tr>
			<tr>
    			<th>
    				ReCaptcha Private Key<br />
    				<small><a href='https://www.google.com/recaptcha/admin'>https://www.google.com/recaptcha/admin</a></small>
    			</th>
    			<td><input type='password' name='recappriv' class='form-control' required='1' /></td>
    		</tr>
    		<tr>
    			<th colp='2'>Admin User</th>
    		</tr>
    		<tr>
    			<th>Username</th>
    			<td><input type='text' name='a_username' minlength='3' maxlength='20' class='form-control' required='1' /></td>
    		</tr>
    		<tr>
    			<th>Password</th>
    			<td><input type='password' name='a_password' class='form-control' required='1' /></td>
    		</tr>
    		<tr>
    			<th>Confirm Password</th>
    			<td><input type='password' name='a_cpassword' class='form-control' required='1' /></td>
    		</tr>
    		<tr>
    			<th>Email Address</th>
    			<td><input type='email' name='a_email' class='form-control' required='1' /></td>
    		</tr>
    		<tr>
    			<th>Gender</th>
    			<td>
    				<select name='gender' class='form-control' required='1' type='dropdown'>
    					<option value='Male'>Male</option>
    					<option value='Female'>Female</option>
    				</select>
    			</td>
    		</tr>
    		<tr>
    			<td colp='2' align='center'>
    				<input type='submit' value='Install' class='btn btn-primary' />
    			</td>
    		</tr>
    </table>
    </form>
       ";
}
function gpc_cleanup($text)
{
	return strip_tags(stripslashes($text));
}
function install()
{
   global $Version,$Build;
   menuprint('sql');
	$recappriv = (isset($_POST['recappriv'])) ? gpc_cleanup($_POST['recappriv']) : '';
	$recappub = (isset($_POST['recappub'])) ? gpc_cleanup($_POST['recappub']) : '';
    $paypal = (isset($_POST['paypal']) && filter_input(INPUT_POST, 'paypal', FILTER_VALIDATE_EMAIL)) ? gpc_cleanup($_POST['paypal']) : '';
    $adm_email = (isset($_POST['a_email']) && filter_input(INPUT_POST, 'a_email', FILTER_VALIDATE_EMAIL)) ? gpc_cleanup($_POST['a_email']) : '';
    $adm_username = (isset($_POST['a_username']) && strlen($_POST['a_username']) > 3) ? gpc_cleanup($_POST['a_username']) : '';
	$adm_gender = (isset($_POST['gender']) && in_array($_POST['gender'], array('Male', 'Female'), true)) ? $_POST['gender'] : 'Male';
    $description = (isset($_POST['game_description'])) ? gpc_cleanup($_POST['game_description']) : '';
    $owner = (isset($_POST['game_owner']) && strlen($_POST['game_owner']) > 3) ? gpc_cleanup($_POST['game_owner']) : '';
    $game_name = (isset($_POST['game_name'])) ? gpc_cleanup($_POST['game_name']) : '';
    $adm_pswd = (isset($_POST['a_password']) && strlen($_POST['a_password']) > 3) ? gpc_cleanup($_POST['a_password']) : '';
    $adm_cpswd = isset($_POST['a_cpassword']) ? gpc_cleanup($_POST['a_cpassword']) : '';
	$pweffort =  (isset($_POST['password_effort']) && is_numeric($_POST['password_effort'])) ? abs(intval($_POST['password_effort'])) : '10';
    $db_hostname = isset($_POST['hostname']) ? gpc_cleanup($_POST['hostname']) : '';
    $db_username = isset($_POST['username']) ? gpc_cleanup($_POST['username']) : '';
    $db_password = isset($_POST['password']) ? gpc_cleanup($_POST['password']) : '';
    $db_database = isset($_POST['database']) ? gpc_cleanup($_POST['database']) : '';
    $db_driver = (isset($_POST['driver'])  && in_array($_POST['driver'], array('pdo', 'mysqli'), true)) ? $_POST['driver'] : 'mysqli';
    $errors = array();
    if (empty($db_hostname))
    {
        $errors[] = 'No Database hostname specified';
    }
    if (empty($db_username))
    {
        $errors[] = 'No Database username specified';
    }
    if (empty($db_database))
    {
        $errors[] = 'No Database database specified';
    }
	if ($db_driver = 'mysqli')
	{
		if (!function_exists($db_driver . '_connect'))
		{
			$errors[] = 'Invalid database driver specified';
		}
	}
	elseif ($db_driver = 'pdo')
	{
		if (!extension_loaded('pdo_mysql'))
		{
			$errors[] = 'Invalid database driver specified';
		}
	}
	else
	{
		$errors[] = 'Invalid database driver specified';
	}
    if (empty($adm_username) || !preg_match("/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i", $adm_username))
    {
        $errors[] = 'Invalid admin username specified';
    }
    if (empty($adm_pswd))
    {
        $errors[] = 'Invalid admin password specified';
    }
    if ($adm_pswd !== $adm_cpswd)
    {
        $errors[] = 'The admin passwords did not match';
    }
    if (empty($adm_email))
    {
        $errors[] = 'Invalid admin email specified';
    }
    if (empty($owner)  || !preg_match("/^[a-z0-9_]+([\\s]{1}[a-z0-9_]|[a-z0-9_])+$/i", $owner))
    {
        $errors[] = 'Invalid game owner specified';
    }
    if (empty($game_name))
    {
        $errors[] = 'Invalid game name specified';
    }
    if (empty($description))
    {
        $errors[] = 'Invalid game description specified';
    }
    if (empty($paypal))
    {
        $errors[] = 'Invalid game PayPal specified';
    }
	if ((empty($pweffort)) || ($pweffort < 5) || ($pweffort > 20))
	{
		$errors[] = 'Password Effort either blank, lower than five or higher than twenty!';
	}
	if (empty($recappub) || (empty($recappriv)))
	{
		$errors[] = 'ReCaptcha information empty.';
	}
    if (count($errors) > 0)
    {
        echo "Installation failed.<br />
        There were one or more problems with your input.<br />
        <br />
        <b>Problem(s) encountered:</b>
        <ul>";
        foreach ($errors as $error)
        {
            echo "<li><span class='text-danger'>{$error}</span></li>";
        }
        echo "</ul>
        &gt; <a href='installer.php?code=config'>Go back to config</a>";
        require_once('installer_foot.php');
        exit;
    }
    // Try to establish DB connection first...
    echo 'Attempting DB connection...';
    require_once("class/class_db_{$db_driver}.php");
    $db = new database;
    $db->configure($db_hostname, $db_username, $db_password, $db_database, 0);
    $db->connect();
    $c = $db->connection_id;
    // Done, move on
    echo '... Successful.<br />';
    echo 'Writing game config file...';
    echo 'Write Config...';
    $code = sha1(openssl_random_pseudo_bytes(64));
    if (file_exists("config.php"))
    {
        unlink("config.php");
    }
    $e_db_hostname = addslashes($db_hostname);
    $e_db_username = addslashes($db_username);
    $e_db_password = addslashes($db_password);
    $e_db_database = addslashes($db_database);
    $lit_config = '$_CONFIG';
    $config_file =
            <<<EOF
<?php
            {$lit_config} = array(
	'hostname' => '{$e_db_hostname}',
	'username' => '{$e_db_username}',
	'password' => '{$e_db_password}',
	'database' => '{$e_db_database}',
	'persistent' => 0,
	'driver' => '{$db_driver}',
	'code' => '{$code}',
    'primary_currency' => 'Primary Currency',
    'secondary_currency' => 'Secondary Currency',
    'strength_stat' => 'Strength',
    'agility_stat' => 'Agility',
    'guard_stat' => 'Guard',
    'iq_stat' => 'IQ',
    'labor_stat' => 'Labor',
    'item_effects' => 3
);
EOF;
    $f = fopen('config.php', 'w');
    fwrite($f, $config_file);
    fclose($f);
    echo '... file written.<br />';
    echo 'Writing base database schema...';
    $fo = fopen("cengine.sql", "r");
    $query = '';
    $lines = explode("\n", fread($fo, 1024768));
    fclose($fo);
    foreach ($lines as $line)
    {
        if (!(strpos($line, "--") === 0) && trim($line) != '')
        {
            $query .= $line;
            if (!(strpos($line, ";") === FALSE))
            {
                $db->query($query);
                $query = '';
            }
        }
    }
    echo '... done.<br />';
    echo 'Writing game configuration...';
	 $db->query("INSERT INTO `settings` VALUES(NULL, 'Password_Effort', '{$pweffort}')");
    $ins_username = $db->escape(htmlentities($adm_username, ENT_QUOTES, 'ISO-8859-1'));
    $encpsw = password_hash(base64_encode(hash('sha256',$adm_pswd,true)), PASSWORD_DEFAULT);
    $e_encpsw = $db->escape($encpsw);
    $ins_email = $db->escape($adm_email);
	$profilepic="https://www.gravatar.com/avatar/" . md5(strtolower(trim($ins_email))) . "?s=250.jpg";
    $IP = $db->escape($_SERVER['REMOTE_ADDR']);
    $ins_game_name = $db->escape(htmlentities($game_name, ENT_QUOTES, 'ISO-8859-1'));
    $ins_game_desc = $db->escape(htmlentities($description, ENT_QUOTES, 'ISO-8859-1'));
    $ins_paypal = $db->escape($paypal);
    $ins_game_owner = $db->escape(htmlentities($owner, ENT_QUOTES, 'ISO-8859-1'));
	$CurrentTime=time();
	$db->query("INSERT INTO `users` 
	(`username`, `user_level`, `email`, `password`, `gender`,
	 `lastip`, `registerip`, `registertime`,`display_pic`) 
	VALUES ('{$ins_username}', 'Admin', '{$ins_email}', 
	'{$e_encpsw}', '{$adm_gender}', '{$IP}', 
	'{$IP}', '{$CurrentTime}', '{$profilepic}');");
    $i = $db->insert_id();
    $db->query("INSERT INTO `userstats` VALUES($i, 1000, 1000, 1000, 1000, 1000)");
    $db->query("INSERT INTO `settings` VALUES(NULL, 'WebsiteName', '{$ins_game_name}')");
    $db->query("INSERT INTO `settings` VALUES(NULL, 'WebsiteOwner', '{$ins_game_owner}')");
    $db->query("INSERT INTO `settings` VALUES(NULL, 'PaypalEmail', '{$ins_paypal}')");
    $db->query("INSERT INTO `settings` VALUES(NULL, 'Website_Description', '{$ins_game_desc}')");
	$db->query("INSERT INTO `settings` VALUES(NULL, 'Version_Number', '{$Version}')");
	$db->query("INSERT INTO `settings` VALUES(NULL, 'BuildNumber', '{$Build}')");
	$db->query("INSERT INTO `settings` VALUES(NULL, 'reCaptcha_public', '{$recappub}')");
	$db->query("INSERT INTO `settings` VALUES(NULL, 'reCaptcha_private', '{$recappriv}')");
	$db->query("INSERT INTO `infirmary` (`infirmary_user`, `infirmary_reason`, `infirmary_in`, `infirmary_out`) VALUES ('{$i}', 'N/A', '0', '0');");
	$db->query("INSERT INTO `dungeon` (`dungeon_user`, `dungeon_reason`, `dungeon_in`, `dungeon_out`) VALUES ('{$i}', 'N/A', '0', '0');");
    echo '... Done.<br />';
    if ($_POST['analytics'] == 'true')
    {
        echo "Sending install analytics...";
        sendData($ins_game_name,$db_driver);
        echo "Analytics have been sent. TheMasterGeneral thanks you!<br />";
    }
    $path = dirname($_SERVER['SCRIPT_FILENAME']);
    echo "
    <h2>Installation Complete!</h2>
    <hr />
       ";
    echo "<h3>Installer Security</h3>
    Attempting to remove installer... ";
    @unlink('installer.php');
	@unlink('installer_head.php');
    @unlink('installer_foot.php');
	@unlink('password_benchmark.php');
	$CronsStart=strtotime("midnight tomorrow");
	$db->query("INSERT INTO `crons` (`file`, `nextUpdate`) VALUES ('crons/minute.php', $CronsStart),
	('crons/fivemins.php', $CronsStart), ('crons/day.php', $CronsStart), ('crons/hour.php', $CronsStart);");
    if (file_exists('installer.php'))
	{
		$success = false;
		echo "Failed.<br />";
	}
	else
	{
		$success = true;
		echo "Success!<br />";
	}
	@unlink('lib/installer_error_handler.php');
    if ($success == false)
    {
        echo "Attempting to lock installer... ";
        @touch('installer.lock');
        $success2 = file_exists('installer.lock');
        echo "<span style='color: " . ($success2 ? "green;'>Succeeded" : "red;'>Failed")
                . "</span><br />";
        if ($success2)
        {
            echo "<span style='font-weight: bold;'>"
                    . "You should now remove installer.php from your server."
                    . "</span>";
        }
        else
        {
            echo "<span style='font-weight: bold; font-size: 20pt;'>"
                    . "YOU MUST REMOVE installer.php "
                    . "from your server.<br />"
                    . "Failing to do so will allow other people "
                    . "to run the installer again and potentially "
                    . "mess up your game entirely." . "</span>";
        }
    }
	echo "<br />Crons have been set to start tomorrow at midnight.";
}
if ($_GET['code'] != 'install')
{
	require_once('installer_foot.php');
}
/* gets the contents of a file if it exists, otherwise grabs and caches */
function getCachedFile($url,$file,$hours=1)
{
	$current_time = time(); 
	$expire_time = $hours * 60 * 60;
	if(file_exists($file))
	{
		$file_time = filemtime($file);
		if ($current_time - $expire_time < $file_time)
		{
			return file_get_contents($file);
		}
		else
		{
			$content = updateFile($url,$file);
			file_put_contents($file,$content);
			return $content;
		}
	}
	else 
	{
		$content = updateFile($url,$file);
		file_put_contents($file,$content);
		return $content;
	}
}
function updateFile($url)
{
	global $db,$set;
	$content = "404";
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => "{$url}",
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true));
	$content = curl_exec($curl);
	curl_close($curl);
	return $content;
}
/*
 * Function to fetch current version of Chivalry Engine
 */
function getEngineVersion($url = 'https://raw.githubusercontent.com/MasterGeneral156/Version/master/chivalry-engine.json')
{
    global $set;
    $engine_version = $set['Version_Number'];
    $json = json_decode(getCachedFile($url, __DIR__ . "/cache/update_check.txt"), true);
    if (is_null($json))
        return "Update checker failed.";
    if (version_compare($engine_version, $json['latest-v2']) == 0 || version_compare($engine_version, $json['latest-v2']) == 1)
        return "Chivalry Engine is up to date.";
    else
        return "Chivalry Engine update available. Download it <a href='{$json['download-latest']}'>here</a>.";
}

/*
 * Function to send analytical data to TheMasterGeneral, if the installer chooses to.
 */
function sendData($gamename, $dbtype, $url='https://chivalryisdeadgame.com/chivalry-engine-analytics.php')
{
    global $Version;
    $postdata = "domain=" . getGameURL() . "&install=" . time() ."&gamename={$gamename}&dbtype={$dbtype}&version={$Version}";
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
    curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_REFERER, $url);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt ($ch, CURLOPT_POST, 1);
    $result = curl_exec ($ch);
    curl_close($ch);
}
function getGameURL()
{
    $domain = $_SERVER['HTTP_HOST'];
    $turi = $_SERVER['REQUEST_URI'];
    $turiq = '';
    for ($t = strlen($turi) - 1; $t >= 0; $t--) {
        if ($turi[$t] != '/') {
            $turiq = $turi[$t] . $turiq;
        } else {
            break;
        }
    }
    $turiq = '/' . $turiq;
    if ($turiq == '/') {
        $domain .= substr($turi, 0, -1);
    } else {
        $domain .= str_replace($turiq, '', $turi);
    }
    return $domain;
}