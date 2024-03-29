<?php
/*
	File:		index.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		Main landing page for the engine.
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
require_once('globals.php');
//Player is attempting to update their personal notepad.
if (isset($_POST['pn_update'])) {
    //Sanitize the notepad entry
    $_POST['pn_update'] = (isset($_POST['pn_update'])) ? strip_tags(stripslashes($_POST['pn_update'])) : '';
    //Notepad update is too large for the database storage
    if (strlen($_POST['pn_update']) > 65535) {
        alert('danger', "Uh Oh!", "Your notepad is too big to update.", false);
    } else {
        //Update the notepad after escaping the data entered.
        $pn_update_db = $db->escape($_POST['pn_update']);
        $db->query("UPDATE `users`
        			SET `personal_notes` = '{$pn_update_db}'
        			WHERE `userid` = {$userid}");
        $ir['personal_notes'] = $_POST['pn_update'];
        alert('success', "Success!", "Your notepad has been successfully updated.", false);
    }
}
echo "Welcome back, {$ir['username']}!<br />";
echo "Your last visit was on {$lv}.";
echo "<table class='table table-hover table-bordered'>
<tbody>
	<tr>
		<td>
		    Level: " . number_format($ir['level']) . "
		</td>
		<td>
		    Experience: " . number_format($ir['xp']) . " / " . number_format($ir['xp_needed']) . "
		</td>
	</tr>
	<tr>
		<td>
		    VIP Days: " . number_format($ir['vip_days']) . "
		</td>
	</tr>
	<tr>
		<td>
            {$_CONFIG['primary_currency']}: " . number_format($ir['primary_currency']) . "
		</td>
		<td>
            {$_CONFIG['secondary_currency']}: " . number_format($ir['secondary_currency']) . "
		</td>
	</tr>
	<tr>
		<td>
		    Health: " . number_format($ir['hp']) . " / " . number_format($ir['maxhp']) . "
		</td>
		<td>
            Energy: " . number_format($ir['energy']) . " / " . number_format($ir['maxenergy']) . "
		</td>
	</tr>
	<tr>
		<td>
		    Will: " . number_format($ir['will']) . " / " . number_format($ir['maxwill']) . "
		</td>
		<td>
		    Bravery: " . number_format($ir['brave']) . " / " . number_format($ir['maxbrave']) . "
		</td>
	</tr>
</tbody>";

//Get the stat ranks. Players like this apparently.
$StrengthRank = getRank($ir['strength'], 'strength');
$StrengthFormat = number_format($ir['strength']);
$AgilityRank = getRank($ir['agility'], 'agility');
$AgilityFormat = number_format($ir['agility']);
$GuardRank = getRank($ir['guard'], 'guard');
$GuardFormat = number_format($ir['guard']);
$IQRank = getRank($ir['iq'], 'iq');
$IQFormat = number_format($ir['iq']);
$LaborRank = getRank($ir['labor'], 'labor');
$LaborFormat = number_format($ir['labor']);
$AllFourFormat = number_format($ir['strength'] + $ir['agility'] + $ir['guard'] + $ir['labor'] + $ir['iq']);

echo "</table>
<h3>Stats</h3>";
echo "
<table class='table table-bordered'>
    <tr>
        <th width='25%'>
            {$_CONFIG['strength_stat']}
        </th>
        <td>
            {$StrengthFormat} (Ranked: {$StrengthRank})
        </td>
    </tr>
    <tr>
        <th width='25%'>
            {$_CONFIG['agility_stat']}
        </th>
        <td>
            {$AgilityFormat} (Ranked: {$AgilityRank})
        </td>
    </tr>
    <tr>
        <th width='25%'>
            {$_CONFIG['guard_stat']}
        </th>
        <td>
            {$GuardFormat} (Ranked: {$GuardRank})
        </td>
    </tr>
    <tr>
        <th width='25%'>
            {$_CONFIG['labor_stat']}
        </th>
        <td>
            {$LaborFormat} (Ranked: {$LaborRank})
        </td>
    </tr>
    <tr>
        <th width='25%'>
            {$_CONFIG['iq_stat']}
        </th>
        <td>
            {$IQFormat} (Ranked: {$IQRank})
        </td>
    </tr>
    <tr>
        <th width='25%'>
            Total Stats
        </th>
        <td>
            {$AllFourFormat} (Ranked: {$IQRank})
        </td>
    </tr>
</table>
<form method='post'>
    <div class='form-group'>
        <label for='pn_update'>Personal Notepad</label>
        <textarea class='form-control' name='pn_update' id='pn_update'>{$ir['personal_notes']}</textarea>
    </div>
    <button type='submit' class='btn btn-primary'>Update Notepad</button>
</form>";
$h->endpage();