<?php
/*
	File:		header.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		Loads the in-game template for users who are logged in.
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
class headers
{
    function startheaders()
    {
        global $ir, $set, $h, $db, $menuhide, $userid, $macropage, $api, $time, $_CONFIG;
        //Load the meta headers.
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <meta name="description" content="<?php echo $set['Website_Description']; ?>">
                <meta property="og:title" content="<?php echo $set['WebsiteName']; ?>"/>
                <meta property="og:description" content="<?php echo $set['Website_Description']; ?>"/>
                <meta property="og:image" content=""/>
                <link rel="shortcut icon" href="" type="image/x-icon"/>
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
                <link rel="stylesheet" href="//malihu.github.io/custom-scrollbar/jquery.mCustomScrollbar.min.css">
                <link rel="stylesheet" href="css/sidebar-themes.css">
                <meta name="theme-color" content="#343a40">
                <meta name="author" content="<?php echo $set['WebsiteOwner']; ?>">
                <?php echo "<title>{$set['WebsiteName']}</title>"; ?>
        </head>
		<?php
		//If the called script wants the menu hidden.
		if (empty($menuhide))
		{
			//Select count of user's unread messages.
			$ir['mail'] = $db->fetch_single($db->query("SELECT COUNT(`mail_id`) FROM `mail` WHERE `mail_to` = {$ir['userid']} AND `mail_status` = 'unread'"));
			//Select count of user's unread notifications.
			$ir['notifications'] = $db->fetch_single($db->query("SELECT COUNT(`notif_id`) FROM `notifications` WHERE `notif_user` = {$ir['userid']} AND `notif_status` = 'unread'"));
			$energy = $api->user->getInfoPercent($userid, 'energy');
			$brave = $api->user->getInfoPercent($userid, 'brave');
			$will = $api->user->getInfoPercent($userid, 'will');
			$xp = round($ir['xp'] / $ir['xp_needed'] * 100);
			$hp = $api->user->getInfoPercent($userid, 'hp');
			?>
				<body>
				<div class="page-wrapper default-theme sidebar-bg toggled">
				<div id="show-sidebar" class="btn btn-sm btn-dark">
					<i class="fas fa-bars"></i>
				</div>
				<nav id="sidebar" class="sidebar-wrapper">
					<div class="sidebar-content">
						<!-- sidebar-brand  -->
						<div class="sidebar-item sidebar-brand">
							<a href="index.php"><?php echo $set['WebsiteName']; ?></a>
							<div id='close-sidebar'>
								<i class='fas fa-times'></i>
							</div>
						</div>
						<div class=" sidebar-item sidebar-menu">
							<ul>
								<li class="header-menu">
									<span>
									<?php  
									echo "{$ir['username']} [{$userid}]<br />
									Level {$ir['level']}<br />
									Energy {$energy}%<br />
									Brave {$brave}%<br />
									Will {$will}%<br />
									XP {$xp}%<br />
									HP {$hp}%<br />
									{$_CONFIG['primary_currency']}: " . number_format($ir['primary_currency']) . "<br />
									{$_CONFIG['secondary_currency']}: " . number_format($ir['secondary_currency']); ?>
									</span>
								</li>
							</ul>
						</div>
						<!-- sidebar-menu  -->
						<div class=" sidebar-item sidebar-menu">
							<ul>
								<li class="header-menu">
									<span>General</span>
								</li>
								<li>
									<a href="inventory.php">
										<span class="menu-text">Inventory</span>
									</a>
								</li>
								<li>
									<a href="explore.php">
										<span class="menu-text">Explore</span>
									</a>
								</li>
								<li class="header-menu">
									<span>Activities</span>
								</li>
								<li>
									<a href="gym.php">
										<span class="menu-text">Gym</span>
									</a>
								</li>
								<li>
									<a href="criminal.php">
										<span class="menu-text">Crimes</span>
									</a>
								</li>
								<li>
									<a href="academy.php">
										<span class="menu-text">Academy</span>
									</a>
								</li>
								<li>
									<a href="dungeon.php">
										<span class="menu-text">Dungeon</span>
									</a>
								</li>
								<li>
									<a href="infirmary.php">
										<span class="menu-text">Infirmary</span>
									</a>
								</li>
								<li class="header-menu">
									<span>Social</span>
								</li>
								<li>
									<a href="forums.php">
										<span class="menu-text">Forums</span>
									</a>
								</li>
								<li>
									<a href="newspaper.php">
										<span class="menu-text">Newspaper</span>
									</a>
								</li>
								<li>
									<a href="profile.php?user=<?php echo $userid; ?>">
										<span class="menu-text">Your Profile</span>
									</a>
								</li>
								<li>
									<a href="announcements.php">
										<span class="menu-text">Announcements <span class='badge badge-pill badge-primary'><?php echo $ir['announcements']; ?></span></span>
									</a>
								</li>
								<?php if ($api->user->getStaffLevel($userid,"forum moderator"))
								{
									?>
								<li class="header-menu">
									<span>Staff</span>
								</li>
								<li>
									<a href="staff/index.php">
										<span class="menu-text">Staff Panel</span>
									</a>
								</li>
								<?php } ?>
								<li class="header-menu">
									<span><?php echo date('F j, Y') . " " . date('g:i:s a'); ?></span>
								</li>
							</ul>
						</div>
						<!-- sidebar-menu  -->
					</div>
					<!-- sidebar-footer  -->
					<div class="sidebar-footer">
						<div class="dropdown">
							<a href="notifications.php">
								<i class="fa fa-bell"></i>
								<span class="badge badge-pill badge-success notification"><?php echo $ir['notifications']; ?></span>
							</a>
						</div>
						<div class="dropdown">
							<a href="inbox.php">
								<i class="fa fa-envelope"></i>
								<span class="badge badge-pill badge-success notification"><?php echo $ir['mail']; ?></span>
							</a>
						</div>
						<div class="dropdown">
							<a href="preferences.php">
								<i class="fa fa-cog"></i>
							</a>
						</div>
						<div>
							<a href="logout.php">
								<i class="fa fa-power-off"></i>
							</a>
						</div>
						<div class="pinned-footer">
							<a href="#">
								<i class="fas fa-ellipsis-h"></i>
							</a>
						</div>
					</div>
				</nav>

				<!-- Page Content -->
				<main class="page-content pt-2">
					<div id="overlay" class="overlay"></div>
					<div class="container-fluid p-5">
				<noscript>
					<?php
					//User doesn't have javascript turned on, so lets tell them.
					alert('info', "Uh Oh!", "Please enable Javascript. Many features of the game will not work without it.", false);
					?>
				</noscript>
			<?php
            //Dev help document.
            require "lib/dev_help.php";
			$IP = $db->escape($_SERVER['REMOTE_ADDR']);
			$ipq = $db->query("SELECT `ip_id` FROM `ipban` WHERE `ip_ip` = '{$IP}'");
			//User's IP is banned, so lets stop access.
			if ($db->num_rows($ipq) > 0) {
				alert('danger', "Uh Oh!", "You have been IP banned.", false);
				die($h->endpage());
			}
			$fed = $db->fetch_row($db->query("SELECT * FROM `fedjail` WHERE `fed_userid` = {$userid}"));
			echo "<b><a href='donator.php' class='text-danger'>Donate to {$set['WebsiteName']} and you'll receive many cool perks!</a></b><br />";
			//User's federal jail sentence is completed. Let them play again.
			if ($fed['fed_out'] < $time) {
				$db->query("UPDATE `users` SET `fedjail` = 0 WHERE `userid` = {$userid}");
				$db->query("DELETE FROM `fedjail` WHERE `fed_userid` = {$userid}");
			}
			//User is in federal jail. Stop their access.
			if ($ir['fedjail'] > 0) {
				alert('info', "Federal Dungeon!", "You are locked away in Federal Dungeon for the next
								" . timeUntilParse($fed['fed_out']) . ". You were placed in here for <b>{$fed['fed_reason']}</b>", false);
				die($h->endpage());
			}
			//Tell user when they have unread messages, when they do.
			if ($ir['mail'] > 0) {
				alert('info', "New Mail!", "You have {$ir['mail']} unread messages.", true, 'inbox.php',"View Inbox");
			}
			//Tell user they have unread notifcations when they do.
			if ($ir['notifications'] > 0) {
				alert('info', "New Notifications!", "You have {$ir['notifications']} unread notifications.", true, 'notifications.php', "View Notifications");
			}
			//Tell user they have unread game announcements when they do.
			if ($ir['announcements'] > 0) {
				alert('info', "New Announcements!", "You have {$ir['announcements']} unread announcements.", true, 'announcements.php', "View Announcements");
			}
			//User is in the infirmary, tell them for how long.
			if ($api->user->inInfirmary($userid)) {
				$InfirmaryOut = $db->fetch_single($db->query("SELECT `infirmary_out` FROM `infirmary` WHERE `infirmary_user` = {$ir['userid']}"));
				$InfirmaryRemain = timeUntilParse($InfirmaryOut);
				alert('info', "Unconscious!", "You are in the Infirmary for the next {$InfirmaryRemain}.", true, "inventory.php", "View Inventory");
			}
			//User is in the dungeon, tell them how long.
			if ($api->user->inDungeon($userid)) {
				$DungeonOut = $db->fetch_single($db->query("SELECT `dungeon_out` FROM `dungeon` WHERE `dungeon_user` = {$ir['userid']}"));
				$DungeonRemain = timeUntilParse($DungeonOut);
				alert('info', "Locked Up!", "You are in the dungeon for the next {$DungeonRemain}.", true, "inventory.php", "View Inventory");
			}
			//User needs to reverify with reCaptcha
			if (($ir['last_verified'] < ($time - $set['Revalidate_Time'])) || ($ir['need_verify'] == 1))
			{
				//ReCaptcha public or private key(s) are unspecifed in the game settings.
				if (empty($set['reCaptcha_public']) || empty($set['reCaptcha_private']))
				{
					?>
					<script>alert('Please add the reCaptcha private and public keys.');</script>
					<?php
					die($h->endpage());
				}
				//Script calls for reCaptcha to be loaded.
				if (isset($macropage))
				{
				//Set User to need verified.
				$db->query("UPDATE `users` SET `need_verify` = 1 WHERE `userid` = {$userid}");
				echo "This is a needed evil. Please confirm you are not a bot."; ?>
					<form action='macro.php' method='post'>
							<div class='g-recaptcha' data-theme='light'
								 data-sitekey='<?php echo $set['reCaptcha_public']; ?>'></div>
						<input type='hidden' value='<?php echo $macropage; ?>' name='page'>
						<input type='submit' value="<?php echo "Confirm"; ?>" class="btn btn-primary" data-dismiss="modal">
					</form>
					<?php
					die($h->endpage());
				}
			}
		}
    }

    function userdata($ir, $dosessh = 1)
    {
        global $db, $userid, $api;
        $IP = $db->escape($_SERVER['REMOTE_ADDR']);
        //Update the user as they browse the game.
        $db->query("UPDATE `users`
                    SET `laston` = {$_SERVER['REQUEST_TIME']}, 
                    `lastip` = '{$IP}' 
                    WHERE `userid` = {$userid}");
        //User's account does not have an email address.
        if (!$ir['email']) {
            global $domain;
            die("<body>Your account is broken. Please contact admin@{$domain} for assistance.");
        }
        //If the user's attacking is not stored in session.
        if (!isset($_SESSION['attacking']))
            $_SESSION['attacking'] = 0;
        //If user does not end a fight correctly, take their XP and warn them.
        if ($dosessh && ($_SESSION['attacking'] || $ir['attacking'])) {
            $hosptime = randomNumber(10, 50);
            $api->user->setInfirmary($userid, $hosptime, "Ran from a fight");
            alert("warning", "Uh Oh!", "For leaving your previous fight, you were placed in the Infirmary for {$hosptime}
            minutes, and lost all your experience.", false);
            $db->query("UPDATE `users` SET `xp` = 0, `attacking` = 0 WHERE `userid` = $userid");
            $_SESSION['attacking'] = 0;
        }
    }

    function endpage()
    {
        global $db, $ir, $StartTime, $set;
        $query_extra = '';
        //Set mysqldebug in the URL to get query debugging as an admin.
    if (isset($_GET['mysqldebug']) && $ir['user_level'] == 'Admin')
    {
        ?>
        <pre class='pre-scrollable'>
                  <?php
                  var_dump($db->queries)
                  ?>
              </pre>
    <?php
    }
    ?>
        </div>
        </div>
        <!-- /.row -->

        </div>
        <!-- /.container -->
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css">
        <link rel="stylesheet" href="css/game.css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
        <!-- jQuery Version 3.4.0 -->
        <script src="https://code.jquery.com/jquery-3.4.0.min.js"></script>
        
        <!-- Core Bootstrap Javascript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

        <!-- Other JavaScript -->
        <script src="js/game.js"></script>
        <script src="js/sidemenu.js"></script>
        <script src='https://www.google.com/recaptcha/api.js' async defer></script>
        <script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js"></script>
        <script src="https://cdn.rawgit.com/tonystar/bootstrap-hover-tabs/v3.1.1/bootstrap-hover-tabs.js" async defer></script>
        <script src="https://malihu.github.io/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
        <script type="text/javascript">
            jQuery(function ($) {
            $("#close-sidebar").click(function() {
              $(".page-wrapper").removeClass("toggled");
                localStorage.setItem("toggle", "toggled");
            });
            $("#show-sidebar").click(function() {
              $(".page-wrapper").addClass("toggled");
                localStorage.setItem("toggle", "");
            });
           
        });	
        </script>
        </body>
        </html>
    <?php
    }
}
