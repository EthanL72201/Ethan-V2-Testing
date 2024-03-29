<?php
/*
	File:		mine.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		Allows players to mine for in-game resources.
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
$macropage = ('mine.php');
require('globals.php');
$UIDB = $db->query("SELECT * FROM `mining` WHERE `userid` = {$userid}");
if (!($db->num_rows($UIDB))) {
    $db->query("INSERT INTO `mining` (`userid`, `max_miningpower`, `miningpower`, `miningxp`, `buyable_power`, `mining_level`) 
    VALUES ('{$userid}', '100', '100', '0', '1', '1');");
}
$MUS = ($db->fetch_row($db->query("SELECT * FROM `mining` WHERE `userid` = {$userid} LIMIT 1")));
mining_levelup();
echo "<h2>Dangerous Mines</h2><hr />";
if ($api->user->inInfirmary($userid)) {
    alert('danger', "Unconscious!", "You cannot go mining if you're in the infirmary.");
    die($h->endpage());
}
if ($api->user->inDungeon($userid)) {
    alert('danger', "Locked Up!", "You cannot go mining if you're in the dungeon.");
    die($h->endpage());
}
if (!isset($_GET['action'])) {
    $_GET['action'] = '';
}
switch ($_GET['action']) {
    case 'mine':
        mine();
        break;
    case 'buypower':
        buypower();
        break;
    default:
        home();
        break;
}
function home()
{
    global $MUS, $db, $api;
    $mineen = min(round($MUS['miningpower'] / $MUS['max_miningpower'] * 100), 100);
    $minexp = min(round($MUS['miningxp'] / $MUS['xp_needed'] * 100), 100);
    $mineenp = 100 - $mineen;
    $minexpp = 100 - $minexp;
    echo "Welcome to the dangerous mines, brainless moron! If you're lucky, you'll strike riches. If not... the mine
        will eat you alive.
    <br />
	<table class='table table-bordered'>
		<tr>
			<th colspan='2'>
				You are mining level {$MUS['mining_level']}.
			</th>
		</tr>
		<tr>
			<th>
				Mining Power
			</th>
			<td>
				<div class='progress'>
					<div class='progress-bar bg-success' role='progressbar' aria-valuenow='{$MUS['miningpower']}' aria-valuemin='0' aria-valuemax='100' style='width:{$mineen}%'>
						{$mineen}% ({$MUS['miningpower']} / {$MUS['max_miningpower']})
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th>
				Mining Experience
			</th>
			<td>
				<div class='progress'>
					<div class='progress-bar bg-success' role='progressbar' aria-valuenow='{$MUS['miningxp']}' aria-valuemin='0' aria-valuemax='100' style='width:{$minexp}%'>
						{$minexp}% ({$MUS['miningxp']} / {$MUS['xp_needed']})
					</div>
				</div>
			</td>
		</tr>
	</table>
    <u>Open Mines</u><br />";
    $minesql = $db->query("SELECT * FROM `mining_data` ORDER BY `mine_level` ASC");
    while ($mines = $db->fetch_row($minesql)) {
        echo "[<a href='?action=mine&spot={$mines['mine_id']}'>" . $api->game->getTownNameFromID($mines['mine_location']) . " - Level {$mines['mine_level']}</a>]<br />";
    }

    echo "<br /><br />
    [<a href='?action=buypower'>Buy Power Sets</a>]";

}

function buypower()
{
    global $userid, $db, $ir, $MUS, $h, $api;
    $CostForPower = $MUS['mining_level'] * 75 + 10 + $MUS['mining_level']; //Cost formula, in IQ.
    if (isset($_POST['sets']) && ($_POST['sets'] > 0)) {
        $sets = abs($_POST['sets']);
        $totalcost = $sets * $CostForPower;
        if ($sets > $MUS['buyable_power']) {
            alert('danger', "Uh Oh!", "You are trying to buy more sets of power than you currently have available to you.");
            die($h->endpage());
        } elseif (($ir['iq'] < $totalcost)) {
            alert('danger', "Uh Oh!", "You need " . number_format($totalcost) . " to buy the amount of sets you want to. You only have " . number_format($ir['iq']));
            die($h->endpage());

        } else {
            $db->query("UPDATE `userstats` SET `iq` = `iq` - '{$totalcost}' WHERE `userid` = {$userid}");
            $db->query("UPDATE `mining` SET `buyable_power` = `buyable_power` - '$sets', 
						`max_miningpower` = `max_miningpower` + ($sets*10) 
						WHERE `userid` = {$userid}");
            $api->game->addLog($userid, 'mining', "Exchanged {$totalcost} IQ for {$sets} sets of mining power.");
            alert('success', "Success!", "You have traded " . number_format($totalcost) . " {$_CONFIG['iq_stat']} for {$sets} of mining power.", true, 'mine.php');
        }
    } else {
        echo "You can buy {$MUS['buyable_power']} sets of mining power. One set is equal to 10 mining power. You unlock
            more sets by leveling your mining level. Each set will cost you " . number_format($CostForPower) . " {$_CONFIG['iq_stat']}.
            How many do you wish to buy?";
        echo "<br />
        <form method='post'>
            <input type='number' class='form-control' value='{$MUS['buyable_power']}' min='1' max='{$MUS['buyable_power']}' name='sets' required='1'>
            <br />
            <input type='submit' class='btn btn-primary' value='Buy Power'>
        </form>";
    }
}

function mine()
{
    global $db, $MUS, $ir, $userid, $api, $h;
    if (!isset($_GET['spot']) || empty($_GET['spot'])) {
        alert('danger', "Uh Oh!", "Please select the mine you wish to mine at.", true, 'mine.php');
        die($h->endpage());
    } else {
        $spot = abs($_GET['spot']);
        $mineinfo = $db->query("SELECT * FROM `mining_data` WHERE `mine_id` = {$spot}");
        if (!($db->num_rows($mineinfo))) {
            alert('danger', "Uh Oh!", "The mine you are trying to mine at does not exist.", true, 'mine.php');
            die($h->endpage());
        } else {
            $MSI = $db->fetch_row($mineinfo);
            $query = $db->query("SELECT `inv_itemid` FROM `inventory` where `inv_itemid` = {$MSI['mine_pickaxe']} && `inv_userid` = {$userid}");
            $i = $db->fetch_row($query);
            if ($MUS['mining_level'] < $MSI['mine_level']) {
                alert('danger', "Uh Oh!", "You are too low level to mine here. You need mining level {$MSI['mine_level']} to mine here.", true, 'mine.php');
                die($h->endpage());
            } elseif ($ir['location'] != $MSI['mine_location']) {
                alert('danger', "Uh Oh!", "To mine at a mine, you need to be in the same town its located.", true, 'mine.php');
                die($h->endpage());
            } elseif ($ir['iq'] < $MSI['mine_iq']) {
                alert('danger', "Uh Oh!", "Your {$_CONFIG['iq_stat']} is too low to mine here. You need {$MSI['mine_iq']} {$_CONFIG['iq_stat']}.", true, 'mine.php');
                die($h->endpage());
            } elseif ($MUS['miningpower'] < $MSI['mine_power_use']) {
                alert('danger', "Uh Oh!", "You do not have enough mining power to mine here. You need {$MSI['mine_power_use']}.", true, 'mine.php');
                die($h->endpage());
            } elseif (!$i['inv_itemid'] == $MSI['mine_pickaxe']) {
                alert('danger', "Uh Oh!", "You do not have the required pickaxe to mine here. You need a " . $api->game->getItemNameFromID($MSI['mine_pickaxe']), true, "mine.php");
                die($h->endpage());
            } else {
                if (!isset($xpgain)) {
                    $xpgain = 0;
                }
                if ($ir['iq'] <= $MSI['mine_iq'] + ($MSI['mine_iq'] * .3)) {
                    $Rolls = randomNumber(1, 5);
                } elseif ($ir['iq'] >= $MSI['mine_iq'] + ($MSI['mine_iq'] * .3) && ($ir['iq'] <= $MSI['mine_iq'] + ($MSI['mine_iq'] * .6))) {
                    $Rolls = randomNumber(1, 10);
                } else {
                    $Rolls = randomNumber(1, 15);
                }
                if ($Rolls <= 3) {
                    $NegRolls = randomNumber(1, 3);
                    $NegTime = randomNumber(5, 25) * ($MUS['mining_level'] * .25);
                    if ($NegRolls == 1) {
                        alert('danger', "Uh Oh!", "You begin to mine and touch off a natural gas leak. Kaboom.", false);
                        $api->game->addLog($userid, 'mining', "Mined at {$api->game->getTownNameFromID($MSI['mine_location'])} [{$MSI['mine_location']}] and was put into the infirmary for {$NegTime} minutes.");
                        $api->user->setInfirmary($userid, $NegTime, "Mining Explosion");
                    } elseif ($NegRolls == 2) {
                        alert('danger', "Uh Oh!", "You hit a vein of gems, except a miner nearby gets jealous and tries to take your gems! You knock them out cold, and a guard arrests you. Wtf.", false);
                        $api->game->addLog($userid, 'mining', "Mined at {$api->game->getTownNameFromID($MSI['mine_location'])} [{$MSI['mine_location']}] and was put into the dungeon for {$NegTime} minutes.");
                        $api->user->setDungeon($userid, $NegTime, "Mining Selfishness");
                    } else {
                        alert('danger', "Uh Oh!", "You failed to mine anything of use.", false);
                        $api->game->addLog($userid, 'mining', "Mined at {$api->game->getTownNameFromID($MSI['mine_location'])} [{$MSI['mine_location']}] and was unsuccessful.");
                    }
                } elseif ($Rolls >= 3 && $Rolls <= 14) {
                    $PosRolls = randomNumber(1, 3);
                    if ($PosRolls == 1) {
                        $flakes = randomNumber($MSI['mine_copper_min'], $MSI['mine_copper_max']);
                        alert('success', "Success!", "You have successfully mined up " . number_format($flakes) . " " . $api->game->getItemNameFromID($MSI['mine_copper_item']), false);
                        $api->user->giveItem($userid, $MSI['mine_copper_item'], $flakes);
                        $api->game->addLog($userid, 'mining', "Mined at {$api->game->getTownNameFromID($MSI['mine_location'])} [{$MSI['mine_location']}] and mined {$flakes}x {$api->game->getItemNameFromID($MSI['mine_copper_item'])}.");
                        $xpgain = $flakes * 0.15;

                    } elseif ($PosRolls == 2) {
                        $flakes = randomNumber($MSI['mine_silver_min'], $MSI['mine_silver_max']);
                        alert('success', "Success!", "You have successfully mined up " . number_format($flakes) . " " . $api->game->getItemNameFromID($MSI['mine_silver_item']), false);
                        $api->user->giveItem($userid, $MSI['mine_silver_item'], $flakes);
                        $api->game->addLog($userid, 'mining', "Mined at {$api->game->getTownNameFromID($MSI['mine_location'])} [{$MSI['mine_location']}] and mined {$flakes}x {$api->game->getItemNameFromID($MSI['mine_silver_item'])}.");
                        $xpgain = $flakes * 0.35;
                    } else {
                        $flakes = randomNumber($MSI['mine_gold_min'], $MSI['mine_gold_max']);
                        alert('success', "Success!", "You have successfully mined up " . number_format($flakes) . " " . $api->game->getItemNameFromID($MSI['mine_gold_item']), false);
                        $api->user->giveItem($userid, $MSI['mine_gold_item'], $flakes);
                        $api->game->addLog($userid, 'mining', "Mined at {$api->game->getTownNameFromID($MSI['mine_location'])} [{$MSI['mine_location']}] and mined {$flakes}x {$api->game->getItemNameFromID($MSI['mine_gold_item'])}.");
                        $xpgain = $flakes * 0.45;
                    }
                } else {
                    alert('success', "Success!", "You have carefully excavated out a single" . $api->game->getItemNameFromID($MSI['mine_gem_item']), false);
                    $api->user->giveItem($userid, $MSI['mine_gem_item'], 1);
                    $api->game->addLog($userid, 'mining', "Mined at {$api->game->getTownNameFromID($MSI['mine_location'])} [{$MSI['mine_location']}] and mined 1x {$api->game->getItemNameFromID($MSI['mine_gem_item'])}.");
                    $xpgain = 3 * $MUS['mining_level'];
                }
                echo "<hr />
                [<a href='?action=mine&spot={$spot}'>Mine Again</a>]<br />
                [<a href='mine.php'>Pack it Up</a>]";
                $db->query("UPDATE `mining` SET `miningxp`=`miningxp`+ {$xpgain}, `miningpower`=`miningpower`-'{$MSI['mine_power_use']}' WHERE `userid` = {$userid}");
            }
        }
    }
}

function mining_levelup()
{
    global $db, $userid, $MUS;
    $MUS['xp_needed'] = round(($MUS['mining_level'] + 0.75) * ($MUS['mining_level'] + 0.75) * ($MUS['mining_level'] + 0.75) * 1);
    if ($MUS['miningxp'] >= $MUS['xp_needed']) {
        $expu = $MUS['miningxp'] - $MUS['xp_needed'];
        $MUS['mining_level'] += 1;
        $MUS['miningxp'] = $expu;
        $MUS['buyable_power'] += 1;
        $MUS['xp_needed'] =
            round(($MUS['mining_level'] + 0.75) * ($MUS['mining_level'] + 0.75) * ($MUS['mining_level'] + 0.75) * 1);
        $db->query("UPDATE `mining` SET `mining_level` = `mining_level` + 1, `miningxp` = {$expu},
                 `buyable_power` = `buyable_power` + 1 WHERE `userid` = {$userid}");
    }
}

$h->endpage();