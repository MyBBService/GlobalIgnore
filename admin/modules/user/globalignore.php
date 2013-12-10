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

generate_tabs("list");
echo "Folgt noch";

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