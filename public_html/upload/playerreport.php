<?php
/*
	File:		playerreport.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		Allows players to report other players for suspected game rule breakage.
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
require("globals.php");
function csrf_error()
{
    global $h;
    alert('danger', "Action Blocked!", "The action you were trying to do was blocked. It was blocked because you loaded
        another page on the game. If you have not loaded a different page during this time, change your password
        immediately, as another person may have access to your account!");
    die($h->endpage());
}

echo "<h3>Player Report</h3><hr />";
if (empty($_POST['userid'])) {
    $code = getCodeCSRF('report_form');
    echo "Know someone who broke the rules, or is just being dishonorable? This is the place to report them. Report the
        user just once. Reporting the same user multiple times will slow down the process. If you are found to be
        abusing the player report system, you will be placed away in federal jail. Information you enter here will
        remain confidential and will only be read by senior staff members. If you wish to confess to a crime, this is
        also a great place to do so.<br />
	 <form method='post'>
	 <table class='table table-bordered'>
		<tr>
			<th>
				User
			</th>
			<td>
				<input type='number' min='1' required='1' name='userid' class='form-control'>
			</td>
		</tr>
		<tr>
			<th>
			    Report Text
			</th>
			<td>
				<textarea class='form-control' required='1' maxlength='1250' name='reason' rows='5'></textarea>
			</td>
		</tr>
		<tr>
			
			<td colspan='2'>
				<input type='submit' value='Submit Report' class='btn btn-primary'>
			</td>
		</tr>
	</table>
	<input type='hidden' name='verf' value='{$code}' />
	</form>";
} else {
    $_POST['reason'] = (isset($_POST['reason']) && is_string($_POST['reason'])) ? $db->escape(strip_tags(stripslashes($_POST['reason']))) : '';
    $_POST['userid'] = (isset($_POST['userid']) && is_numeric($_POST['userid'])) ? abs($_POST['userid']) : '';
    if (!isset($_POST['verf']) || !checkCSRF('report_form', stripslashes($_POST['verf']))) {
        csrf_error();
    }
    if (strlen($_POST['reason']) > 30000) {
        alert('danger', "Uh Oh!", "Player reports can only be, at maximum, 30,000 characters in length.");
        die($h->endpage());
    }
    $q = $db->query("SELECT COUNT(`userid`) FROM `users` WHERE `userid` = {$_POST['userid']}");
    if ($db->fetch_single($q) == 0) {
        $db->free_result($q);
        alert('danger', "Uh Oh!", "You are trying to report a non-existent user.");
        die($h->endpage());
    }
    $db->free_result($q);
    $db->query("INSERT INTO `reports` VALUES(NULL, $userid, {$_POST['userid']}, '{$_POST['reason']}')");
    alert('success', "Success!", "You have successfully reported the user. Staff may send you a message asking
		    questions about the report you just sent. Please answer them to the best of your ability.", true, 'index.php');

}
$h->endpage();