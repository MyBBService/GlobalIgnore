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
//		"dlcid"			=> "36"
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
//	header("Location: {$mybb->settings['bburl']}/member.php?action=profile&uid={$mybb->user['uid']}");
	exit();
}
?>