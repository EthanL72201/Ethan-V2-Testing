<?php
/*
	File: 		staff/staff_towns.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		Allows staff to do actions relating to the in-game towns.
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
if ($api->user->getStaffLevel($userid, 'Admin') == false) {
    alert('danger', "Uh Oh!", "You do not have permission to be here.");
    die($h->endpage());
}
if (!isset($_GET['action'])) {
    $_GET['action'] = '';
}
switch ($_GET['action']) {
    case "addtown":
        addtown();
        break;
    case "edittown":
        edittown();
        break;
    case "deltown":
        deltown();
        break;
    default:
        alert('danger', "Uh Oh!", "Please select a valid action to perform.", true, 'index.php');
        die($h->endpage());
        break;
}
function addtown()
{
    global $db, $userid, $h, $api;
    echo "<h3>Add Town</h3><hr />";
    if (isset($_POST['name'])) {
        $level = (isset($_POST['minlevel']) && is_numeric($_POST['minlevel'])) ? abs(intval($_POST['minlevel'])) : 1;
        $name = (isset($_POST['name']) && is_string($_POST['name'])) ? $db->escape(htmlentities($_POST['name'])) : '';
        if (!isset($_POST['verf']) || !checkCSRF('staff_addtown', stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "This action has been blocked for your security. Please submit the form quickly");
            die($h->endpage());
        }
        $q = $db->query("SELECT COUNT(`town_id`) FROM `town` WHERE `town_name` = '{$name}'");
        if ($db->fetch_single($q) > 0) {
            $db->free_result($q);
            alert('danger', "Uh Oh!", "The town name you've chosen is already in use.");
            die($h->endpage());
        }
        if ($level < 0) {
            alert('danger', "Uh Oh!", "Please specify a minimum level requirement.");
            die($h->endpage());
        }
        $db->free_result($q);
        $db->query("INSERT INTO `town` (`town_name`, `town_min_level`) VALUES ('{$name}', '{$level}');");
        $api->game->addLog($userid, 'staff', "Created a town named {$name}.");
        alert('success', "Success!", "You have successfully created the {$name} town.", true, 'index.php');
    } else {
        $csrf = getHtmlCSRF('staff_addtown');
        echo "<form action='?action=addtown' method='post'>
		<table class='table table-bordered'>
			<tr>
				<th colspan='2'>
					You can add towns to the game using this form.
				</th>
			</tr>
			<tr>
				<th>
					Town Name
				</th>
				<td>
					<input type='text' name='name' required='1' class='form-control'>
				</td>
			</tr>
			<tr>
				<th>
					Level Requirement
				</th>
				<td>
					<input type='number' name='minlevel' min='0' required='1' class='form-control'>
				</td>
			</tr>
			<tr>
				<th>
					Taxation Level
				</th>
				<td>
					<input type='number' name='tax' min='0' max='20' required='1' class='form-control'>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<input type='submit' class='btn btn-primary' value='Create Town'>
				</td>
			</tr>
			{$csrf}
		</table>
		</form>";
    }
}

function deltown()
{
    global $db, $userid, $api, $h;
    echo "<h3>Removing a Town</h3><hr />";
    if (isset($_POST['town'])) {
        $town = (isset($_POST['town']) && is_numeric($_POST['town'])) ? abs(intval($_POST['town'])) : 0;
        $q = $db->query("SELECT `town_id`, `town_name` FROM `town` WHERE `town_id` = {$town}");
        if (!isset($_POST['verf']) || !checkCSRF('staff_deltown', stripslashes($_POST['verf']))) {
            alert('danger', "Action Blocked!", "This action has been blocked for your security. Please submit the form quickly");
            die($h->endpage());
        }
        if ($db->num_rows($q) == 0) {
            $db->free_result($q);
            alert('danger', "Uh Oh!", "You are trying to remove a town that doesn't exist.");
            die($h->endpage());
        }
        $old = $db->fetch_row($q);
        $db->free_result($q);
        if ($old['town_id'] == 1) {
            alert('danger', "Uh Oh!", "You cannot delete the starter town.");
            die($h->endpage());
        }
        $db->query("UPDATE `users` SET `location` = 1 WHERE `location` = {$old['town_id']}");
        $db->query("UPDATE `shops` SET `shopLOCATION` = 1 WHERE `shopLOCATION` = {$old['town_id']}");
        $db->query("DELETE FROM `town` WHERE `town_id` = {$old['town_id']}");
        alert('success', "Success!", "You have successfully removed the {$old['town_name']} town from the game.", true, 'index.php');
        $api->game->addLog($userid, 'staff', "Deleted the town called {$old['town_name']}.");
    } else {
        $csrf = getHtmlCSRF('staff_deltown');
        echo "
		<form action='?action=deltown' method='post'>
			<table class='table table-bordered'>
				<tr>
					<th colspan='2'>
						Select the town you wish to delete.
					</th>
				</tr>
				<tr>
					<th>
						Town
					</th>
					<td>
						" . dropdownLocation("town") . "
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' class='btn btn-primary' value='Delete Town'>
					</td>
				</tr>
				{$csrf}
			</table>
		</form>";
    }
}

function edittown()
{
    global $api, $userid, $db, $h;
    if (!isset($_POST['step'])) {
        $_POST['step'] = '0';
    }
    switch ($_POST['step']) {
        case 2:
            $level = (isset($_POST['minlevel']) && is_numeric($_POST['minlevel'])) ? abs(intval($_POST['minlevel'])) : 1;
            $name = (isset($_POST['name']) && is_string($_POST['name'])) ? $db->escape(htmlentities($_POST['name'])) : '';
            $id = (isset($_POST['id']) && is_numeric($_POST['id'])) ? abs(intval($_POST['id'])) : 0;
            $q = $db->query("SELECT * FROM `town` WHERE `town_id` = {$id}");
            if ($db->num_rows($q) == 0) {
                $db->free_result($q);
                alert("danger", "Uh Oh!", "The town you are wishing to edit does not exist, or is invalid.");
                die($h->endpage());
            }
            if (!isset($_POST['verf']) || !checkCSRF('staff_edittown2', stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "This action has been blocked for your security. Please submit the form quickly");
                die($h->endpage());
            }
            $q = $db->query("SELECT COUNT(`town_id`) FROM `town` WHERE `town_name` = '{$name}' && `town_id` != {$id}");
            if ($db->fetch_single($q) > 0) {
                $db->free_result($q);
                alert('danger', "Uh Oh!", "The town name you've chosen is already in use.");
                die($h->endpage());
            }
            if ($level < 0) {
                alert('danger', "Uh Oh!", "Please specify a minimum level requirement.");
                die($h->endpage());
            }
            $db->free_result($q);
            $db->query("UPDATE `town`
                        SET `town_name` = '{$name}', `town_min_level` = {$level}
                        WHERE `town_id` = {$id}");
            alert("success", "Success!", "You have successfully edited the {$name} town.", true, 'index.php');
            $api->game->addLog($userid, 'staff', "Edited the {$name} town.");
            break;
        case 1:
            $_POST['location'] = (isset($_POST['location']) && is_numeric($_POST['location'])) ? abs(intval($_POST['location'])) : 0;
            $q = $db->query("SELECT * FROM `town` WHERE `town_id` = {$_POST['location']}");
            if ($db->num_rows($q) == 0) {
                $db->free_result($q);
                alert("danger", "Uh Oh!", "The town you are wishing to edit does not exist, or is invalid.");
                die($h->endpage());
            }
            if (!isset($_POST['verf']) || !checkCSRF('staff_edittown1', stripslashes($_POST['verf']))) {
                alert('danger', "Action Blocked!", "Forms expire fairly quickly. Go back and submit it quicker!");
                die($h->endpage());
            }
            $r = $db->fetch_row($q);
            $csrf = getHtmlCSRF('staff_edittown2');
            echo "<form method='post'>
                <input type='hidden' name='step' value='2' />
        	    <input type='hidden' name='id' value='{$_POST['location']}' />
                <table class='table table-bordered'>
                    <tr>
                        <th colspan='2'>
                            Edit the town using this form.
                        </th>
                    </tr>
                    <tr>
                        <th>
                            Town Name
                        </th>
                        <td>
                            <input type='text' name='name' required='1' class='form-control' value='{$r['town_name']}'>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Level Requirement
                        </th>
                        <td>
                            <input type='number' name='minlevel' min='0' required='1' class='form-control' value='{$r['town_min_level']}'>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <input type='submit' class='btn btn-primary' value='Edit Town'>
                        </td>
                    </tr>
                    {$csrf}
                </table>
                </form>";
            break;
        default:
            $csrf = getHtmlCSRF('staff_edittown1');
            echo "<h3>Edit a Town</h3><hr />
            Please select the town you wish to edit.<br />
            <form method='post'>
                <input type='hidden' name='step' value='1'>
                " . dropdownLocation() . " <br />
                {$csrf}
                <input type='submit' value='Edit Town' class='btn btn-primary'>
            </form>";
            break;
    }
}

$h->endpage();
