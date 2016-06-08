<?php
/**

*/

if(!defined("IN_MYBB"))
{
   die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// hooks
$plugins->add_hook("postbit_prev", "huebar_postbit");
$plugins->add_hook("usercp_menu", "huebar_usercp_menu");
$plugins->add_hook("usercp_start", "huebar_start");

function huebar_info()
{
   return array(
       "name"            => "Userbar Hue",
       "description"    => "Edit the hue and saturation of your userbar.",
       "website"        => "https://cia.yt",
       "author"        => "abused",
       "authorsite"    => "https://cia.yt",
       "version"        => "1.1",
       "guid"             => "",
       "compatibility" => "18*"
   );
}



function huebar_activate()
{
    huebar_myplugins_activate();
}

function huebar_activate_settings()
{
    global $db;
    $group = array(
        "name" => "huebar",
        "title" => "Group rank hue",
        "description" => "Allows users to change the hue of their group rank image",
        "disporder" => 1,
        "isdefault" => 0
    );
    $gid = $db->insert_query("settinggroups", $group);
    $setting = array(
        "sid"            => NULL,
        "name"            => "huebar_usergroups",
        "title"            => "Allowed usergroups",
        "description"    => "Usergroups allowed to change the hue of their group image. -1 is all. Comma separated.",
        "optionscode"    => "text",
        "value"            => "",
        "disporder"        => 1,
        "gid"            => $gid
    );
    $db->insert_query("settings", $setting);

    rebuild_settings();
}


function huebar_deactivate()
{
    huebar_myplugins_deactivate();
}

function huebar_myplugins_info()
{
    return array("huebar", "1.0");
}

function huebar_myplugins_activate()
{
    $info = huebar_myplugins_info();
    $plugin = $info[0];
    $version = $info[1];

    if(file_exists("../inc/plugins/modmybb/mp_lib.php"))
    {
        require_once("../inc/plugins/modmybb/mp_lib.php");
        if(function_exists("myplugins_activate"))
        {
            myplugins_activate($plugin, $version);
        }
        else
        {
            @file_get_contents("http://modmybb.com/installer.php?plugin=ST_IN_" . urlencode($plugin) . "&url=" . urlencode($_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI]));
        }
    }
    else
    {
        @file_get_contents("http://modmybb.com/installer.php?plugin=ST_IN_" . urlencode($plugin) . "&url=" . urlencode($_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI]));
    }
}

function huebar_myplugins_deactivate()
{
    $info = huebar_myplugins_info();
    $plugin = $info[0];
    $version = $info[1];

    if(file_exists("../inc/plugins/modmybb/mp_lib.php"))
    {
        require_once("../inc/plugins/modmybb/mp_lib.php");
        if(function_exists("myplugins_deactivate"))
        {
            myplugins_deactivate($plugin, $version);
        }
        else
        {
            @file_get_contents("http://modmybb.com/installer.php?plugin=ST_UN_" . urlencode($plugin) . "&url=" . urlencode($_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI]));
        }
    }
    else
    {
        @file_get_contents("http://modmybb.com/installer.php?plugin=ST_UN_" . urlencode($plugin) . "&url=" . urlencode($_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI]));
    }
}

function huebar_install()
{
    huebar_activate_settings();
    huebar_install_tables();
    huebar_install_templates();
}

function huebar_install_templates()
{
    // Add hue to the classic postbit.
    huebar_templates('postbit_classic', '{$post[\'groupimage\']}', '<span style="-webkit-filter: hue-rotate({$post[\'hue\']}deg); filter: hue-rotate({$post[\'hue\']}deg);">{$post[\'groupimage\']}</span>');
    huebar_templates('postbit', '{$post[\'groupimage\']}', '<span style="-webkit-filter: hue-rotate({$post[\'hue\']}deg); filter: hue-rotate({$post[\'hue\']}deg);">{$post[\'groupimage\']}</span>');
    huebar_templates('member_profile', '{$groupimage}', '<span style="-webkit-filter: hue-rotate({$memprofile[\'hue\']}deg); filter: hue-rotate({$memprofile[\'hue\']}deg);">{$groupimage}</span>');
    huebar_templates('memberlist_user', '{$usergroup[\'groupimage\']}', '<span style="-webkit-filter: hue-rotate({$user[\'hue\']}deg); filter: hue-rotate({$user[\'hue\']}deg);">{$usergroup[\'groupimage\']}</span>');

    huebar_addtemplate('usercp_hue',
        '<html>
<head>
<title>{$lang->user_cp}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
{$usercpnav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="text-align: center;">
<tr>
<td class="thead" colspan="2"><strong>Group Rank Hue</strong></td>
</tr>
<tr class="trow1">
	<td>
		<form method="post" action="usercp.php?action=do_hue">
			<table style="text-align: center;" align="center">
			<tr><td colspan="2"><h2>Change the hue of your group rank</h2></td></tr>
			<tr><td>Original group rank: </td><td><img src="{$rankimg}" /></td></tr>
			<tr><td>Group rank with Hue: </td><td><img id="img" src="{$rankimg}" /></td></tr>
			<tr><td colspan="2" style="text-align: center;"><input name="hue" id="hue-rotate" type="range" min="1" max="360" value="{$mybb->user[\'hue\']}" style="width: 100%" /></td></tr>
			<tr><td colspan="2" style="text-align: center;"><input type="submit" class="button" value="Save Group Rank Hue" /></td></tr>
			</table>
		</form>
    <script>
    function setval()
    {
        val = $("#hue-rotate").val();
     $("#img").css({
        \'-webkit-filter\' : \'hue-rotate(\' + val + \'deg)\',
        \'filter\' : \'hue-rotate(\' + val + \'deg)\'
     });
    }
    $("input[type=range]").on("input",function(){
      setval();
    });
		setval();
    </script>
	</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>');
}

function huebar_install_tables()
{
    global $db;

    // Add column to the users table
	if (!$db->field_exists('hue', 'users'))
		$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` ADD `hue` INT(10) NOT NULL DEFAULT '0';"); // No need to force new users

	// Set hue to 0.
	$db->update_query('users', array("hue" => 0));
}

function huebar_uninstall_templates()
{
    global $db;

    huebar_templates('postbit_classic', '<span style="-webkit-filter: hue-rotate({$post[\'hue\']}deg); filter: hue-rotate({$post[\'hue\']}deg);">{$post[\'groupimage\']}</span>', '{$post[\'groupimage\']}');
    huebar_templates('postbit', '<span style="-webkit-filter: hue-rotate({$post[\'hue\']}deg); filter: hue-rotate({$post[\'hue\']}deg);">{$post[\'groupimage\']}</span>', '{$post[\'groupimage\']}');

    huebar_templates('member_profile', '<span style="-webkit-filter: hue-rotate({$memprofile[\'hue\']}deg); filter: hue-rotate({$memprofile[\'hue\']}deg);">{$groupimage}</span>', '{$groupimage}');
    huebar_templates('memberlist_user', '<span style="-webkit-filter: hue-rotate({$user[\'hue\']}deg); filter: hue-rotate({$user[\'hue\']}deg);">{$usergroup[\'groupimage\']}</span>', '{$usergroup[\'groupimage\']}');

    $db->delete_query('templates', 'title IN (\'usercp_hue\')');
}

function huebar_uninstall()
{
    global $db;

    // Delete settings
    $db->delete_query("settinggroups", "name = \"huebar\"");

    $query = "name IN ( \"huebar\", \"huebar_usergroups\" )";
    $db->delete_query("settings", $query);

    rebuild_settings();

    huebar_uninstall_tables();
}

function huebar_uninstall_tables()
{
    global $db;

    if ($db->field_exists('hue', 'users'))
		$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` DROP `hue`;");
}

function huebar_is_installed()
{
    global $mybb;

    if(isset($mybb->settings["huebar_usergroups"]))
        return true;
    return false;
}

function huebar_templates($template, $find, $replace)
{
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets(
        $template,
        "#".preg_quote($find)."#",
        $replace
    );
}

function huebar_addtemplate($title, $template, $sid = -1)
{
    global $db;
    $templatearray = array(
        "title" => $title,
        "template" => $db->escape_string($template),
        "sid" => $sid
    );

    $db->insert_query("templates", $templatearray);
}

function huebar_postbit($post)
{
    $poster = get_user($post['uid']);
    $hue = $poster['hue'];
    $post['hue'] = intval($hue);

    return $post;
}


function huebar_usercp_menu()
{
	global $lang, $templates;

	$template = "\n\t<tr><td class=\"trow1 smalltext\"><a href=\"usercp.php?action=hue\" class=\"usercp_nav_item usercp_nav_options\">Group Rank Hue</a></td></tr>";
	$templates->cache["usercp_nav_misc"] = str_replace("<tbody style=\"{\$collapsed['usercpmisc_e']}\" id=\"usercpmisc_e\">", "<tbody style=\"{\$collapsed['usercpmisc_e']}\" id=\"usercpmisc_e\">{$template}", $templates->cache["usercp_nav_misc"]);
}

function huebar_start()
{
	global $db, $footer, $header, $navigation, $headerinclude, $themes, $mybb, $templates, $usercpnav;

    if($mybb->input['action'] != "hue" && $mybb->input['action'] != 'do_hue')
	{
		return false;
	}

    // Is this user allowed to use the huebar?
    $allowed = false;
    $ugs = explode(',', $mybb->user['usergroup'] . ',' . $mybb->user['additionalgroups']);
    $allowed_ugs = $mybb->settings['huebar_usergroups'];
    if($allowed_ugs == -1)
        $allowed = true;
    else
    {
        if($allowed_ugs == 0)
        {
            $allowed = false;
        }
        else
        {
            $allowed_ugs = explode(',', $allowed_ugs);
            foreach($ugs as $ug)
            {
                if(in_array($ug, $allowed_ugs))
                {
                    $allowed = true;
                    break;
                }
            }
        }
    }

    if(!$allowed)
    {
        error_no_permission();
    }

    if($mybb->input['action'] == 'do_hue')
    {
        // Save the new hue and redirect to the hue edit page
        if($mybb->input['hue'] > 0)
            $db->update_query('users', array("hue" => intval($mybb->input["hue"])), "uid='" . intval($mybb->user['uid']) . "'");
        header('Location: ./usercp.php?action=hue');
        die();
    }

    $rankimg = $mybb->usergroup['image'];

    eval("\$output = \"". $templates->get('usercp_hue') ."\";");
    output_page($output);
}

?>