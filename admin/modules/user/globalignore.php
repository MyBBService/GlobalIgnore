<?php
if(!defined("IN_MYBB"))
{
        header("HTTP/1.0 404 Not Found");
        exit;
}

if(function_exists("mybbservice_info"))
    define(MODULE, "mybbservice-globalignore");
else
    define(MODULE, "user-globalignore");

$page->add_breadcrumb_item($lang->globalignore, "index.php?module=".MODULE);

if($mybb->input['action'] == "add") {
	$query = $db->simple_select("users", "uid", "username='".$db->escape_string($mybb->input['user'])."'");
	if($db->num_rows($query) != 1) {
		flash_message($lang->globalignore_wrong_id, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	
	$uid = (int) $db->fetch_field($query, "uid");
	
	$db->update_query("users", array("is_ignored" => 1), "uid='{$uid}'");
	
	flash_message($lang->user_added, 'success');
	admin_redirect("index.php?module=".MODULE);
}
if($mybb->input['action'] == "liftban") {
	if(!strlen(trim($mybb->input['uid'])))
	{
		flash_message($lang->globalignore_no_id, 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$uid=(int)$mybb->input['uid'];
	$user = get_user($uid);
	if(empty($user))
	{
		flash_message($lang->globalignore_wrong_id, 'error');
		admin_redirect("index.php?module=".MODULE);
	}

	$db->update_query("users", array("is_ignored" => 0), "uid='{$uid}'");
	
	flash_message($lang->user_lifted, 'success');
	admin_redirect("index.php?module=".MODULE);
}
if($mybb->input['action'] == "") {
	$page->output_header($lang->globalignore);
	generate_tabs("list");
	
	$table = new Table;
	$table->construct_header($lang->username);
	$table->construct_header($lang->liftban);

	$query = $db->simple_select("users", "*", "is_ignored='1'");
	if($db->num_rows($query) != 0) {
		while($user = $db->fetch_array($query)) {
			$table->construct_cell($user['username']);
			$table->construct_cell("<a href=\"index.php?module=".MODULE."&action=liftban&uid=".$user['uid']."\">".$lang->liftban."</a>");
			$table->construct_row();
		}
	} else {
		$table->construct_cell($lang->no_ignored, array("colspan" => 2));
		$table->construct_row();
	}

	$table->output($lang->ignored);
	
	echo "<br />";
	
	$form = new Form("index.php?module=".MODULE."&amp;action=add", "post");
	$form_container = new FormContainer($lang->add);

	$user = $form->generate_text_box("user");
	$form_container->output_row($lang->user, "", $user);
	
	$form_container->end();

	$buttons[] = $form->generate_submit_button($lang->add);
	$form->output_submit_wrapper($buttons);
	$form->end();
}

$page->output_footer();

function generate_tabs($selected)
{
        global $lang, $page;

        $sub_tabs = array();
        $sub_tabs['list'] = array(
                'title' => $lang->list,
                'link' => "index.php?module=".MODULE,
                'description' => $lang->list_desc
        );
/*        $sub_tabs['add'] = array(
                'title' => $lang->add,
                'link' => "index.php?module=".MODULE."&amp;action=add",
                'description' => $lang->add_desc
        );
*/
        $page->output_nav_tabs($sub_tabs, $selected);
}
?>