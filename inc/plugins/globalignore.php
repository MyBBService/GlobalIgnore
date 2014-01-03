<?php 
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

global $cache;
if(!isset($pluginlist))
    $pluginlist = $cache->read("plugins");

//ACP Hooks
if(is_array($pluginlist['active']) && in_array("mybbservice", $pluginlist['active'])) {
        $plugins->add_hook("mybbservice_actions", "globalignore_mybbservice_actions");
        $plugins->add_hook("mybbservice_permission", "globalignore_admin_user_permissions");
} else {
        $plugins->add_hook("admin_user_menu", "globalignore_admin_user_menu");
        $plugins->add_hook("admin_user_action_handler", "globalignore_admin_user_action_handler");
        $plugins->add_hook("admin_user_permissions", "globalignore_admin_user_permissions");
}

//Neuer Thread
$plugins->add_hook("newthread_do_newthread_start", "gi_run");
//Neuer Post
$plugins->add_hook("newreply_do_newreply_start", "gi_run");
//Beitrag bearbeiten
$plugins->add_hook("editpost_do_editpost_start", "gi_run");
//Beitrag löschen
$plugins->add_hook("editpost_deletepost", "gi_run");
//Quick edit
$plugins->add_hook("xmlhttp", "gi_ajax");
//PN senden
$plugins->add_hook("private_send_do_send", "gi_run");

function globalignore_info()
{
	return array(
		"name"			=> "Global Ignore",
		"description"	=> "Ermöglicht es Nutzer global zu ignorieren",
		"website"		=> "http://mybbservice.de",
		"author"		=> "MyBBService",
		"authorsite"	=> "http://mybbservice.de",
		"version"		=> "1.0",
		"guid" 			=> "",
		"compatibility" => "*",
		"dlcid"			=> "36"
	);
}

function globalignore_install()
{
	global $db;
	
    $db->add_column('users', 'is_ignored', "int(1) NOT NULL default '0'");

	$col = $db->build_create_table_collation();
	$db->query("CREATE TABLE `".TABLE_PREFIX."gi_templates` (
				`id`			int(11)			NOT NULL AUTO_INCREMENT,
				`title`			varchar(50)		NOT NULL,
				`template`		text			NOT NULL,
	PRIMARY KEY (`id`) ) ENGINE=MyISAM {$col}");
	
	$error[] = array(
		"id" => 1,
		"title" => "SQL Error",
		"template" => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" >
<head profile=\"http://gmpg.org/xfn/11\">
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	<title>MyBB - Internal Error</title>
	<style type=\"text/css\">
		body { background: #efefef; color: #000; font-family: Verdana; font-size: 12px; text-align: center; line-height: 1.4; }
		a:link { color: #026CB1; text-decoration: none;	}
		a:visited {	color: #026CB1;	text-decoration: none; }
		a:hover, a:active {	color: #000; text-decoration: underline; }
		#container { width: 600px; padding: 20px; background: #fff;	border: 1px solid #e4e4e4; margin: 100px auto; text-align: left; }
		h1 { margin: 0; background: url(/index.php?action=mybb_logo) no-repeat;	height: 82px; width: 248px; }
		#content { border: 1px solid #B60101; background: #fff; }
		h2 { font-size: 12px; padding: 4px; background: #B60101; color: #fff; margin: 0; }
		.invisible { display: none; }
		#error { padding: 6px; }
		#footer { font-size: 11px; border-top: 1px solid #ccc; padding-top: 10px; }
		dt { font-weight: bold; }
	</style>
</head>
<body>
	<div id=\"container\">
		<div id=\"logo\">
			<h1><a href=\"http://mybb.com/\" title=\"MyBulletinBoard\"><span class=\"invisible\">MyBB</span></a></h1>
		</div>

		<div id=\"content\">
			<h2>MyBB SQL Error</h2>

			<div id=\"error\">
				<p>MyBB has experienced an internal SQL error and cannot continue.</p><dl>
<dt>SQL Error:</dt>
<dd>2006 - MySQL Server has gone away</dd>
<dt>Query:</dt>
<dd></dd>
</dl>

				<p id=\"footer\">Please contact the <a href=\"http://mybb.com\">MyBB Group</a> for support.</p>
			</div>
		</div>
	</div>
</body>
</html>"
	);

	$error[] = array(
		"id" => 2,
		"title" => "PHP Error",
		"template" => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" >
<head profile=\"http://gmpg.org/xfn/11\">
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	<title>Internal Error</title>
	<style type=\"text/css\">
		body { background: #efefef; color: #000; font-family: Verdana; font-size: 12px; text-align: center; line-height: 1.4; }
		a:link { color: #026CB1; text-decoration: none;	}
		a:visited {	color: #026CB1;	text-decoration: none; }
		a:hover, a:active {	color: #000; text-decoration: underline; }
		#container { width: 600px; padding: 20px; background: #fff;	border: 1px solid #e4e4e4; margin: 100px auto; text-align: left; }
		h1 { margin: 0; background: url(/newreply.php?action=mybb_logo) no-repeat;	height: 82px; width: 248px; }
		#content { border: 1px solid #B60101; background: #fff; }
		h2 { font-size: 12px; padding: 4px; background: #B60101; color: #fff; margin: 0; }
		.invisible { display: none; }
		#error { padding: 6px; }
		#footer { font-size: 11px; border-top: 1px solid #ccc; padding-top: 10px; }
		dt { font-weight: bold; }
	</style>
</head>
<body>
	<div id=\"container\">
		<div id=\"logo\">
			<h1><a href=\"http://mybb.com/\" title=\"MyBulletinBoard\"><span class=\"invisible\">MyBB</span></a></h1>
		</div>

		<div id=\"content\">
			<h2>MyBB Internal Error</h2>

			<div id=\"error\">
				<p>MyBB has experienced an internal error and cannot continue.</p><dl>
<dt>Error Type:</dt>
<dd>MyBB Error (40)</dd>
<dt>Error Message:</dt>
<dd>An unknown error occured</dd>
<dt>Location:</dt><dd>File: member.php<br />Line: 1022</dd>
</dl>

				<p id=\"footer\">Please contact the <a href=\"http://mybb.com\">MyBB Group</a> for support.</p>
			</div>
		</div>
	</div>
</body>
</html>"
	);
	
	foreach($error as $e) {
		$db->insert_query("gi_templates", $e);
	}
}

function globalignore_is_installed()
{
	global $mybb;
	
	return isset($mybb->user['is_ignored']);
}

function globalignore_uninstall()
{
	global $db;

	$db->drop_column('users', 'is_ignored');
	$db->drop_table('gi_templates');
}

function globalignore_activate(){}

function globalignore_deactivate(){}

function globalignore_mybbservice_actions($actions)
{
    global $page, $lang, $info;
    $lang->load("globalignore");

    $actions['globalignore'] = array(
            "active" => "globalignore",
            "file" => "../user/globalignore.php"
    );

    $sub_menu = array();
    $sub_menu['10'] = array("id" => "globalignore", "title" => $lang->globalignore, "link" => "index.php?module=mybbservice-globalignore");
    $sidebar = new SidebarItem($lang->globalignore);
    $sidebar->add_menu_items($sub_menu, $actions[$info]['active']);

    $page->sidebar .= $sidebar->get_markup();

    return $actions;
}

function globalignore_admin_user_menu($sub_menu)
{
    global $lang;

    $lang->load("globalignore");

    $sub_menu[] = array("id" => "globalignore", "title" => $lang->globalignore, "link" => "index.php?module=user-globalignore");

    return $sub_menu;
}

function globalignore_admin_user_action_handler($actions)
{
    $actions['globalignore'] = array(
            "active" => "globalignore",
            "file" => "globalignore.php"
    );

    return $actions;
}

function globalignore_admin_user_permissions($admin_permissions)
{
    global $lang;

    $lang->load("globalignore");

    $admin_permissions['globalignore'] = $lang->globalignore_permissions;

    return $admin_permissions;
}

function gi_run()
{
	global $mybb, $db;
	
	if(!$mybb->user['is_ignored'])
	    return;

	$query = $db->simple_select("gi_templates", "template", "", array("order_by" => "RAND()", "limit" => 1));
	if($db->num_rows($query) != 0) {
		echo $db->fetch_field($query, "template");
		exit();
	}
	
	// Kein Template vorhanden? Na dann...
	my_unsetcookie("mybbuser");
	my_unsetcookie("sid");
	if($mybb->user['uid'])
	{
		$time = TIME_NOW;
		// Run this after the shutdown query from session system
		$db->shutdown_query("UPDATE ".TABLE_PREFIX."users SET lastvisit='{$time}', lastactive='{$time}' WHERE uid='{$mybb->user['uid']}'");
		$db->delete_query("sessions", "sid='".$session->sid."'");
	}
	
	header("Location: {$mybb->settings['bburl']}/member.php?action=login");
	exit();
}

function gi_ajax()
{
	global $mybb;

	if(!$mybb->user['is_ignored'])
	    return;
	
	if($mybb->input['action'] != "edit_post" || $mybb->input['do'] != "update_post")
	    return;

echo "<html>
<head>
<meta http-equiv=\"refresh\" content=\"0;URL={$mybb->settings['bburl']}/member.php?action=profile&uid={$mybb->user['uid']}\" />
</head>
<body>
</body>
</html>";
	exit();
}
?>