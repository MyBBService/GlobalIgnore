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
//		"dlcid"			=> "34"
	);
}

function globalignore_install()
{
	global $db;
	
    $db->add_column('users', 'is_ignored', "int(1) NOT NULL default '0'");
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
?>