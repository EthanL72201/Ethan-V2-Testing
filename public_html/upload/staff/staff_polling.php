<?php
/*
	File: 		staff/staff_polling.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		Allows staff to do actions relating to the in-game polls.
	Author: 	TheMasterGeneral
	Website: 	https://github.com/MasterGeneral156/chivalry-engine/
	
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
require('sglobals.php');
echo "<h3>Staff Polling</h3><hr />";
if (!$api->user->getStaffLevel($userid, 'Admin')) {
    alert('danger', "Uh Oh!", "You do not have permission to be here.");
    die($h->endpage());
}
if (!isset($_GET['action'])) {
    $_GET['action'] = '';
}
switch ($_GET['action']) {
    case "addpoll":
        add();
        break;
    case "closepoll":
        close();
        break;
    default:
        alert('danger', "Uh Oh!", "Please select a valid action to perform.", true, 'index.php');
        die($h->endpage());
        break;
}
function add()
{
    global $db, $h, $userid, $api;
    if (isset($_POST['question'])) {
        if (!isset($_POST['verf']) || !checkCSRF('staff_startpoll', stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "We have blocked this action for your security. Please fill out the form quickly next time.");
            die($h->endpage());
        }
        $question = (isset($_POST['question'])) ? $db->escape(strip_tags(stripslashes($_POST['question']))) : '';
        $choice1 = (isset($_POST['choice1'])) ? $db->escape(strip_tags(stripslashes($_POST['choice1']))) : '';
        $choice2 = (isset($_POST['choice2'])) ? $db->escape(strip_tags(stripslashes($_POST['choice2']))) : '';
        $choice3 = (isset($_POST['choice3'])) ? $db->escape(strip_tags(stripslashes($_POST['choice3']))) : '';
        $choice4 = (isset($_POST['choice4'])) ? $db->escape(strip_tags(stripslashes($_POST['choice4']))) : '';
        $choice5 = (isset($_POST['choice5'])) ? $db->escape(strip_tags(stripslashes($_POST['choice5']))) : '';
        $choice6 = (isset($_POST['choice6'])) ? $db->escape(strip_tags(stripslashes($_POST['choice6']))) : '';
        $choice7 = (isset($_POST['choice7'])) ? $db->escape(strip_tags(stripslashes($_POST['choice7']))) : '';
        $choice8 = (isset($_POST['choice8'])) ? $db->escape(strip_tags(stripslashes($_POST['choice8']))) : '';
        $choice9 = (isset($_POST['choice9'])) ? $db->escape(strip_tags(stripslashes($_POST['choice9']))) : '';
        $choice10 = (isset($_POST['choice10'])) ? $db->escape(strip_tags(stripslashes($_POST['choice10']))) : '';
        $hidden = (isset($_POST['hidden']) && is_numeric($_POST['hidden'])) ? abs(intval($_POST['hidden'])) : '';
        if (empty($question) || empty($choice1) || empty($choice2)) {
            alert('danger', "Uh Oh!", "Please be sure to fill out the question, and two polling options. Thank you.");
            die($h->endpage());
        }
        $db->query("INSERT INTO `polls` (`active`, `question`, `choice1`,
					`choice2`, `choice3`,`choice4`, `choice5`, `choice6`, 
					`choice7`, `choice8`,`choice9`, `choice10`, `hidden`)
                     VALUES
					 ('1', '$question', '$choice1', '$choice2',
                     '$choice3', '$choice4', '$choice5', '$choice6',
                     '$choice7', '$choice8', '$choice9' ,'$choice10',
                     '{$_POST['hidden']}')");
        alert('success', "Success!", "You have successfully created a poll.", true, 'index.php');
        $api->game->addLog($userid, 'staff', "Started a game poll.");
        $q = $db->query("SELECT `userid`, `username` FROM `users`");
        while ($r = $db->fetch_row($q)) {
            addNotification($r['userid'], "The game administration has added a poll for you to vote in. Please do so by visiting <a href='polling.php'>here</a>.");
        }
        die($h->endpage());
    } else {
        echo "Start a Poll";
        $csrf = getHtmlCSRF('staff_startpoll');
        echo "<hr />
		<form method='post'>
		<table class='table table-bordered'>
			<tr>
				<th width='33%'>
					Question
				</th>
				<td>
					<input type='text' required='1' class='form-control' name='question' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 1
				</th>
				<td>
					<input type='text' required='1' class='form-control' name='choice1' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 2
				</th>
				<td>
					<input type='text' required='1' class='form-control' name='choice2' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 3
				</th>
				<td>
					<input type='text' class='form-control' name='choice3' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 4
				</th>
				<td>
					<input type='text' class='form-control' name='choice4' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 5
				</th>
				<td>
					<input type='text' class='form-control' name='choice5' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 6
				</th>
				<td>
					<input type='text' class='form-control' name='choice6' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 7
				</th>
				<td>
					<input type='text' class='form-control' name='choice7' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 8
				</th>
				<td>
					<input type='text' class='form-control' name='choice8' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 9
				</th>
				<td>
					<input type='text' class='form-control' name='choice9' />
				</td>
			</tr>
			<tr>
				<th>
					Choice 10
				</th>
				<td>
					<input type='text' class='form-control' name='choice10' />
				</td>
			</tr>
			<tr>
				<th>
					Hide results until poll is closed?
				</th>
				<td>
					<select name='hidden' class='form-control' type='dropdown'>
						<option value='0'>No</option>
						<option value='1'>Yes</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<input type='submit' class='btn btn-primary' value='Create Poll'>
				</td>
			</tr>
		</table>
		{$csrf}
		</form>";
    }
}

function close()
{
    global $db, $h, $api, $userid;
    $_POST['poll'] = (isset($_POST['poll']) && is_numeric($_POST['poll'])) ? abs(intval($_POST['poll'])) : '';
    if (empty($_POST['poll'])) {
        $csrf = getHtmlCSRF('staff_endpoll');
        echo "
        Select the poll you wish to end.
        <br />
        <form method='post'>
           ";
        $q =
            $db->query(
                "SELECT `id`, `question`
                         FROM `polls`
                         WHERE `active` = '1'");
        echo "<select name='poll' class='form-control' type='dropdown'>";
        while ($r = $db->fetch_row($q)) {
            echo "<option value='{$r['id']}'>Poll ID: {$r['id']} - {$r['question']}</option>";
        }
        $db->free_result($q);
        echo "</select>" . $csrf . "
			<br /><input type='submit' class='btn btn-primary' value='End Poll' />
		</form>
   		";
        $h->endpage();
    } else {
        if (!isset($_POST['verf']) || !checkCSRF('staff_endpoll', stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "We have blocked this action for your security. Please fill out the form quickly next time.");
            die($h->endpage());
        }
        $q = $db->query("SELECT COUNT(`id`) FROM `polls` WHERE `id` = {$_POST['poll']}");
        if ($db->fetch_single($q) == 0) {
            $db->free_result($q);
            alert('danger', "Uh Oh!", "This poll does not exist, and thus, cannot be ended.");
            die($h->endpage());
        }
        $db->free_result($q);
        $db->query("UPDATE `polls` SET `active` = '0' WHERE `id` = {$_POST['poll']}");
        alert('success', "Success!", "You have closed this poll to respones.", true, 'index.php');
        $api->game->addLog($userid, 'staff', "Closed a game poll.");
        $q = $db->query("SELECT `userid`, `username` FROM `users`");
        while ($r = $db->fetch_row($q)) {
            addNotification($r['userid'], "The game administration has closed a recent poll. View the results <a href='polling.php?action=viewpolls'>here</a>.");
        }
        die($h->endpage());
    }
}
$h->endpage();