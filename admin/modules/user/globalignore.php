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

if($mybb->input['action'] == "template_delete") {
	if(empty($mybb->input['id'])) {
		flash_message($lang->globalignore_no_id, 'error');
		admin_redirect("index.php?module=".MODULE."&action=templates");		
	}
	$id = (int)$mybb->input['id'];
	$query = $db->simple_select("gi_templates", "*", "id='{$id}'");
	if($db->num_rows($query) != 1) {
		flash_message($lang->globalignore_wrong_id, 'error');
		admin_redirect("index.php?module=".MODULE."&action=templates");
	}
	
    if($mybb->input['no'])
        admin_redirect("index.php?module=".MODULE."&action=templates");
    else {
        if($mybb->request_method == "post") {
            $db->delete_query("gi_templates", "id='{$id}'");
            flash_message($lang->template_deleted, 'success');
            admin_redirect("index.php?module=".MODULE."&action=templates");
        } else
            $page->output_confirm_action("index.php?module=".MODULE."&action=template_delete&id={$id}", $lang->template_delete_confirm);
    }
}
if($mybb->input['action'] == "template_post" && $mybb->request_method == "post") {
	if(empty($mybb->input['title']))
	    $errors[] = $lang->no_title;
	if(empty($mybb->input['template']))
	    $errors[] = $lang->no_template;
	
	if(!isset($errors)) {
		$array = array(
			"title" => $db->escape_string($mybb->input['title']),
			"template" => $db->escape_string($mybb->input['template'])
		);
		
		if(isset($mybb->input['id']) && $mybb->input['id'] > 0) {
			$array['id'] = (int)$mybb->input['id'];
			$query = $db->simple_select("gi_templates", "*", "id='{$array['id']}'");
			if($db->num_rows($query) != 1) {
				flash_message($lang->globalignore_wrong_id, 'error');
				admin_redirect("index.php?module=".MODULE."&action=templates");
			}
	
			$db->update_query("gi_templates", $array, "id='{$array['id']}'");
		} else {
			$db->insert_query("gi_templates", $array);
		}	
	
		flash_message($lang->template_added, 'success');
		admin_redirect("index.php?module=".MODULE."&action=templates");
	} else {
		$mybb->input['action'] = "template_view";
	}
}
if($mybb->input['action'] == "template_view") {
	$page->output_header($lang->templates);
	generate_tabs("templates");

	$template = array(
		"id" => -1,
		"title" => "",
		"template" => ""
	);
	
	if(isset($mybb->input['id']) && $mybb->input['id'] > 0) {
		$id = (int)$mybb->input['id'];
		$query = $db->simple_select("gi_templates", "*", "id='{$id}'");
		if($db->num_rows($query) != 1) {
			flash_message($lang->globalignore_wrong_id, 'error');
			admin_redirect("index.php?module=".MODULE."&action=templates");
		}
		
		$template = $db->fetch_array($query);
	}
	
	if(isset($errors)) {
		$page->output_inline_error($errors);
		$template['title'] = htmlspecialchars_uni($mybb->input['title']);
		$template['template'] = htmlspecialchars_uni($mybb->input['template']);
	}
	
	$form = new Form("index.php?module=".MODULE."&amp;action=template_post", "post");
	$form_container = new FormContainer($lang->add);

	$title = $form->generate_text_box("title", $template['title']);
	$form_container->output_row($lang->title, "", $title);

	$template = $form->generate_text_area("template", $template['template'], array("style" => "width: 100%; height: 500px;"));
	$form_container->output_row($lang->template, "", $template);	

	echo $form->generate_hidden_field("id", $id);

	$form_container->end();

	$buttons[] = $form->generate_submit_button($lang->add);
	$form->output_submit_wrapper($buttons);
	$form->end();

}
if($mybb->input['action'] == "templates") {
	$page->output_header($lang->templates);
	generate_tabs("templates");

	$table = new Table;
	$table->construct_header($lang->title);
	$table->construct_header($lang->edit);
	$table->construct_header($lang->delete);

	$query = $db->simple_select("gi_templates");
	if($db->num_rows($query) != 0) {
		while($template = $db->fetch_array($query)) {
			$table->construct_cell($template['title']);
			$table->construct_cell("<a href=\"index.php?module=".MODULE."&action=template_view&id=".$template['id']."\">".$lang->edit."</a>");
			$table->construct_cell("<a href=\"index.php?module=".MODULE."&action=template_delete&id=".$template['id']."\">".$lang->delete."</a>");
			$table->construct_row();
		}
	} else {
		$table->construct_cell($lang->no_templates, array("colspan" => 3));
		$table->construct_row();
	}

	$table->output($lang->templates."<span style=\"float: right;\"><a href=\"index.php?module=".MODULE."&action=template_view\">".$lang->add."</a></span>");
}
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

	$user = $form->generate_text_box("user", "", array("id" => "user"));
	$form_container->output_row($lang->user, "", $user);
	
	$form_container->end();

	$buttons[] = $form->generate_submit_button($lang->add);
	$form->output_submit_wrapper($buttons);
	$form->end();
	
	//Autocomplete
	echo '
		<script type="text/javascript" src="../jscripts/autocomplete.js?ver=140"></script>
		<script type="text/javascript">
		<!--
			new autoComplete("user", "../xmlhttp.php?action=get_users", {valueSpan: "username"});
		// -->
	</script>';
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
        $sub_tabs['templates'] = array(
                'title' => $lang->templates,
                'link' => "index.php?module=".MODULE."&amp;action=templates",
                'description' => $lang->templates_desc
        );

        $page->output_nav_tabs($sub_tabs, $selected);
}
?>