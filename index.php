<?php
include('header.php');

// Get the translation for the wrapper page (this page)
$lang = $flyspray_prefs['lang_code'];
get_language_pack($lang, 'main');

// Fetch the page to include below
$do = $_REQUEST['do'];

// Default page is the task list
if (!isset($_REQUEST['do'])) {
  $do = "index";
}

// Check that the requested project actually exists
$check_proj_exists = $fs->dbQuery("SELECT * FROM flyspray_projects
                                   WHERE project_id = ?",
                                   array($project_id)
                                 );
if (!$fs->dbCountRows($check_proj_exists)) {
  die("<meta http-equiv=\"refresh\" content=\"0; URL=index.php?project={$flyspray_prefs['default_project']}\">");
};

// If a file was requested, deliver it
if ($_GET['getfile']) {

  list($orig_name, $file_name, $file_type) = $fs->dbFetchArray(
                                $fs->dbQuery("SELECT orig_name,
                                              file_name,
                                              file_type
                                              FROM flyspray_attachments
                                              WHERE attachment_id = '{$_GET['getfile']}'
                                      ")
                                 );

  if (file_exists("attachments/$file_name")) {

    $path = "attachments/$file_name";

    header("Pragma: public");
    header("Content-type: $file_type");
    header("Content-Disposition: filename=$orig_name");
    header("Content-transfer-encoding: binary\n");
    header("Content-length: " . filesize($path) . "\n");

    readfile("$path");
  } else {
    echo $language['filenotexist'];
  };

// If no file was requested, show the page as per normal
} else {
  // Send this header for i18n support
  // Note that server admins can override this, breaking Flyspray.
  header("Content-type: text/html; charset=utf-8");

  // Start Output Buffering and gzip encoding if setting is present.
  // This functionality provided Mariano D'Arcangelo
  if ($conf_array['general']['output_buffering']=='gzip') include 'scripts/gzip_compress.php';
  elseif ($conf_array['general']['output_buffering']=='on') ob_start();
  // ob_end_flush() isn't needed in MOST cases because it is called automatically
  // at the end of script execution by PHP itself when output buffering is turned on
  // either in the php.ini or by calling ob_start().
  
  // If the user has used the search box, store their search for later on
  if (isset($_GET['perpage']) || isset($_GET['tasks']) || isset($_GET['order'])) {
    $_SESSION['lastindexfilter'] = "index.php?tasks={$_GET['tasks']}&amp;project={$_GET['project']}&amp;string={$_GET['string']}&amp;type={$_GET['type']}&amp;sev={$_GET['sev']}&amp;due={$_GET['due']}&amp;dev={$_GET['dev']}&amp;cat={$_GET['cat']}&amp;status={$_GET['status']}&amp;perpage={$_GET['perpage']}&amp;pagenum={$_GET['pagenum']}&amp;order={$_GET['order']}&amp;order2=" . $_GET['order2'] . "&amp;sort={$_GET['sort']}&amp;sort2=" . $_GET['sort2'];
  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo stripslashes($project_prefs['project_title']);?></title>
  <link rel="icon" href="./favicon.ico" type="image/png" />
  <meta name="description" content="Flyspray, a Bug Tracking System written in PHP." />
  <link href="themes/<?php echo $project_prefs['theme_style'];?>/theme.css" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src="functions.js"></script>
  <script type="text/javascript" src="styleswitcher.js"></script>
  <style type="text/css">@import url(jscalendar/calendar-win2k-1.css);</style>
  <script type="text/javascript" src="jscalendar/calendar_stripped.js"></script>
  <script type="text/javascript" src="jscalendar/lang/calendar-en.js"></script>
  <script type="text/javascript" src="jscalendar/calendar-setup.js"></script>
  
  <?php
      // open the themes directory
      if ($handle = opendir('themes/')) {
      $theme_array = array();
       while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && file_exists("themes/$file/theme.css")) {
          array_push($theme_array, $file);
        }
      }
      closedir($handle);
    }

    // Sort the array alphabetically
    sort($theme_array);
    // Then display them as alternate themes for browsers that support such features, like Mozilla!
    while (list($key, $val) = each($theme_array)) {
      echo "<link href=\"themes/$val/theme.css\" title=\"$val\" rel=\"alternate stylesheet\" type=\"text/css\" />\n";
    };
    ?>
</head>
<body>

<?php
// People might like to define their own header files for their theme
$headerfile = "$basedir/themes/".$project_prefs['theme_style']."/header.inc.php"; 
if(file_exists("$headerfile")) { 
 include("$headerfile"); 
};


// If the admin wanted the Flyspray logo shown at the top of the page...
if ($project_prefs['show_logo'] == '1') {
  echo "<h1 id=\"title\"><span>{$project_prefs['project_title']}</span></h1>";
};

////////////////////////////////////////////////
// OK, now we start the new permissions stuff //
////////////////////////////////////////////////

// If the user has the right name cookies
if ($_COOKIE['flyspray_userid'] && $_COOKIE['flyspray_passhash']) {

    // Check to see if the user has been trying to hack their cookies to perform sql-injection
    if (!preg_match ("/^\d*$/", $_COOKIE['flyspray_userid']) OR (!preg_match ("/^\d*$/", $_COOKIE['flyspray_project']))) {
      die("Stop hacking your cookies, you naughty fellow!");
    };

  // Get current user details.  We need this to see if their account is enabled or disabled
  $result = $fs->dbQuery("SELECT * FROM flyspray_users WHERE user_id = ?", array($_COOKIE['flyspray_userid']));
  $current_user = $fs->dbFetchArray($result);

  // Get the global group for this user, and put the permissions into an array
  /*$search_global_group = $fs->dbQuery("SELECT * FROM flyspray_groups WHERE belongs_to_project = '0'");
  while ($row = $fs->dbFetchRow($search_global_group)) {
    $check_in = $fs->dbQuery("SELECT * FROM flyspray_users_in_groups WHERE user_id = ? AND group_id = ?", array($_COOKIE['flyspray_userid'], $row['group_id']));
    if ($fs->dbCountRows($check_in) > '0') {
      $global_permissions = $row;
    };
  };*/

  // Get the global group permissions for the current user
  $global_permissions = $fs->dbFetchArray($fs->dbQuery("SELECT *
                                                        FROM flyspray_groups g
                                                        LEFT JOIN flyspray_users_in_groups uig ON g.group_id = uig.group_id
                                                        WHERE uig.user_id = ? and g.belongs_to_project = '0'",
                                                        array($_COOKIE['flyspray_userid'])
                                                       ));
  
  // Check that their global group and user profile lets them login
  /*if ($global_permissions['group_open'] != '1'
      OR $current_user['account_enabled'] != '1') {
      	Header("Location: scripts/authenticate.php?action=logout");
  };*/

  // Get the project-level group for this user, and put the permissions into an array
  $search_project_group = $fs->dbQuery("SELECT * FROM flyspray_groups WHERE belongs_to_project = ?", array($project_id));
  while ($row = $fs->dbFetchRow($search_project_group)) {
    $check_in = $fs->dbQuery("SELECT * FROM flyspray_users_in_groups WHERE user_id = ? AND group_id = ?", array($_COOKIE['flyspray_userid'], $row['group_id']));
    if ($fs->dbCountRows($check_in) > '0') {
      $project_permissions = $row;
    };
  };
  
  // Define which fields we care about from the groups information
  $field = array(
                  '1'  => 'is_admin',
		  '2'  => 'manage_project',
		  '3'  => 'view_tasks',
		  '4'  => 'open_new_tasks',
		  '5'  => 'modify_own_tasks',
		  '6'  => 'modify_all_tasks',
		  '7'  => 'view_comments',
		  '8'  => 'add_comments',
		  '9'  => 'edit_comments',
		  '10' => 'delete_comments',
		  '11' => 'view_attachments',
		  '12' => 'create_attachments',
		  '13' => 'delete_attachments',
		  '14' => 'view_history',
		  '15' => 'close_own_tasks',
		  '16' => 'close_other_tasks',
		  '17' => 'assign_to_self',
		  '18' => 'assign_others_to_self',
		  '19' => 'view_reports',
		 );

  // Now, merge the two arrays, making the highest permission active (basically, use a boolean OR)
  $permissions = array();

  while (list($key, $val) = each($field)) {
    if ($global_permissions[$val] == '1' OR $project_permissions[$val] == '1') {
      $permissions[$val] = '1';
    } else {
      $permissions[$val] = '0';
      
    };
          // This to print out the effective permissions for testing
          //echo $val . ' = ' . $permissions[$val] . '<br />';  
  };

  // Check that the user hasn't spoofed the cookie contents somehow
  if ($_COOKIE['flyspray_passhash'] == crypt($current_user['user_pass'], "$cookiesalt")
      // And that their account is enabled
      && $current_user['account_enabled'] == '1'
      // and that their group is enabled
      && $global_permissions['group_open'] == '1')
    {

    ////////////////////////
    // Show the user menu //
    ////////////////////////

    echo "<p id=\"menu\">\n";
    echo "<em>{$language['loggedinas']} - {$current_user['user_name']}</em>";
    echo "<span id=\"mainmenu\">\n";
    
    if ($permissions['open_new_tasks'] == '1') {
      echo '<small> | </small>';
      echo '<a href="?do=newtask&amp;project=' . $project_id . '">' .
      $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/newtask.png") . '&nbsp;' . $language['addnewtask'] . "</a>\n";
    };
    
    if ($permissions['view_reports'] == '1') {
      echo '<small> | </small>';
      echo '<a href="index.php?do=reports">' .
      $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/reports.png"). '&nbsp;' . $language['reports'] . "</a>\n";
    };
    
    echo '<small> | </small>';
    echo '<a href="?do=admin&amp;area=users&amp;id=' . $current_user['user_id'] . '">' .
    $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/editmydetails.png") . '&nbsp;' . $language['editmydetails'] . "</a>\n";

    // If the user has conducted a search, then show a link to the most recent task list filter
    echo '<small> | </small>';
    if(isset($_SESSION['lastindexfilter'])) {
      echo '<a href="' . $_SESSION['lastindexfilter'] . '">' .
      $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/search.png"). '&nbsp;' . $language['lastsearch'] . "</a>\n";
    } else {
      echo $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/search.png"). '&nbsp;' . $language['lastsearch'] . "</a>\n";
    };
      
    echo '<small> | </small>';
    echo '<a href="scripts/authenticate.php?action=logout">' .
    $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/logout.png"). '&nbsp;' . $language['logout'] . "</a>\n";
    echo "</span>\n";

      /////////////////////////
     // Show the Admin menu //
    /////////////////////////

    if ($permissions['is_admin'] == "1" OR $permissions['manage_project'] == '1') {

    // Find out if there are any PM requests wanting attention
    $get_req = $fs->dbQuery("SELECT * FROM flyspray_admin_requests
                             WHERE project_id = ? AND resolved_by = '0'",
                             array($project_id));
    $num_req = $fs->dbCountRows($get_req);

    // Check for admin requests too
    if ($permissions['is_admin'] == '1') {
      $get_admin_req = $fs->dbQuery("SELECT * FROM flyspray_admin_requests
                                     WHERE project_id = '0' AND resolved_by = '0'");
      $num_req = $num_req + $fs->dbCountRows($get_admin_req);
    };


      echo '<span id="adminmenu">';

      echo '<ul id="admenu">';
      echo '<li><em>' . $language['adminmenu']. '</em></li>';

      
      if ($permissions['is_admin'] == '1') {
        echo '<li>';
        echo '<a href="?do=admin&amp;area=options">' . $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/options.png") . '&nbsp;' . $language['options'] . "</a>\n";
        echo '</li>';
      };
      
      if ($permissions['manage_project'] == '1') {
        echo '<li>';
        echo '<a href="?do=admin&amp;area=projects&amp;show=prefs&amp;project=' . $project_id . '">' . $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/projectprefs.png") . '&nbsp;' . $language['projects'] . "</a>\n";

        // Get a list of projects so that we can cycle through them for the submenu
        $get_projects = $fs->dbQuery("SELECT * FROM flyspray_projects");
        echo '<ul>';
        while ($this_project = $fs->dbFetchArray($get_projects)) {
           echo '<li>';
           echo '<a href="?do=admin&amp;area=projects&amp;show=prefs&amp;project=' . $this_project['project_id'] . '">' . $this_project['project_title'] . "</a>\n";
           echo '</li>';
        };
        echo '</ul>';
        echo '</li>';
      };
      
      if ($permissions['is_admin'] == '1') {
        echo '<li>';
        echo '<a href="?do=admin&amp;area=users">' . $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/usersandgroups.png") . '&nbsp;' . $language['usersandgroups'] . "</a>\n";
        echo '</li>';
      };
     
      if ($permissions['is_admin'] == '1') {
        echo '<li>';
        echo '<a href="?do=admin&amp;area=tasktype">' . $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/lists.png") . '&nbsp;' . $language['tasktypes'] . "</a>\n";
        echo '</li>';
      
        echo '<li>';
        echo '<a href="?do=admin&amp;area=resolution">' . $fs->ShowImg("themes/{$project_prefs['theme_style']}/menu/lists.png") . '&nbsp;' . $language['resolutions'] . "</a>\n";
        echo '</li>';
      };

      // Show the amount of admin requests waiting
      if (($permissions['manage_project'] == '1'
          OR $permissions['is_admin'] == '1')
          && $num_req > '0') {
         echo '<li class="attention">';
         echo '<span class="attention"><a href="?do=admin&amp;area=pendingreq">' . $num_req . ' ' . $language['adminreqwaiting'] . '</a></span>';
         echo '</li>';
      };

      echo '</ul>';
      echo "</span>\n";

    // End of checking if the admin menu should be displayed
    };

    echo "</p>";

    // If the user's account is closed
    } else {
      echo "<br />{$language['disabledaccount']}";
      //echo "<meta http-equiv=\"refresh\" content=\"0; URL=scripts/authenticate.php?action=logout\">\n";
      Header("Location: scripts/authenticate.php?action=logout");
    // End of checking if the user's account is open
    };

// End of checking if the user has the right cookies
};
?>


<div id="content">
<map id="formselecttasks" name="formselecttasks">
<form action="index.php" method="get">
      <p>
      <select name="tasks">
        <option value="all"><?php echo $language['tasksall'];?></option>
      <?php if ($_COOKIE['flyspray_userid']) { ?>
        <option value="assigned" <?php if($_GET['tasks'] == 'assigned') echo 'selected="selected"'; ?>><?php echo $language['tasksassigned']; ?></option>
        <option value="reported" <?php if($_GET['tasks'] == 'reported') echo 'selected="selected"'; ?>><?php echo $language['tasksreported']; ?></option>
        <option value="watched" <?php if($_GET['tasks'] == 'watched') echo 'selected="selected"'; ?>><?php echo $language['taskswatched']; ?></option>
      <?php }; ?> 
      </select>
      <?php echo $language['selectproject'];?>
      <select name="project">
      <option value="0"<?php if ($_GET['project'] == '0') echo ' selected="selected"';?>><?php echo $language['allprojects'];?></option>
      <?php
      $get_projects = $fs->dbQuery("SELECT * FROM flyspray_projects WHERE project_is_active = ? ORDER BY project_title", array('1'));
      while ($row = $fs->dbFetchArray($get_projects)) {
        if ($project_id == $row['project_id'] && $_GET['project'] != '0') {
          echo '<option value="' . $row['project_id'] . '" selected="selected">' . stripslashes($row['project_title']) . '</option>';
        } else {
          echo '<option value="' . $row['project_id'] . '">' . stripslashes($row['project_title']) . '</option>';
        };
      };
      ?>
      </select>
      <input class="mainbutton" type="submit" value="<?php echo $language['show'];?>" />
      </p>
</form>
</map>

<form action="index.php" method="get">
    <p id="showtask">
      <label><?php echo $language['showtask'];?> #
      <input name="id" type="text" size="10" maxlength="10" accesskey="t" /></label>
      <input type="hidden" name="do" value="details" />
      <input class="mainbutton" type="submit" value="<?php echo $language['go'];?>" />
    </p>
</form>

<?php

// Show the project blurb if the project manager defined one
if ($project_prefs['intro_message'] != ''  && $do != 'admin' && $do != 'modify') {
  $intro_message = nl2br(stripslashes($project_prefs['intro_message'])); 
  echo "<p class=\"intromessage\">$intro_message</p>";
};

// If we have allowed anonymous logging of new tasks
// Show the link to the Add Task form
if ($project_prefs['anon_open'] == '1' && !$_COOKIE['flyspray_userid']) {
  echo "<p class=\"unregistered\"><a href=\"?do=newtask&amp;project=$project_id\">{$language['opentaskanon']}</a></p><br />";
};

// Check that this page isn't being submitted twice
if (requestDuplicated()) {
  printf('<meta http-equiv="refresh" content="2; URL=?id=%s">', $project_id);
  printf('<div class="redirectmessage"><p><em>%s</em></p></div>', $language['duplicated']);
  echo '</body></html>';
  exit;
};

// Show the page the user wanted
require("scripts/$do.php");


// if no-one's logged in, show the login box
if(!$_COOKIE['flyspray_userid']) {
  require('scripts/loginbox.php');
};
?>

</div>      
<p id="footer">
<!-- Please don't remove this line - it helps promote Flyspray -->
<a href="http://flyspray.rocks.cc/" class="offsite"><?php printf("%s %s", $language['poweredby'], $fs->version);?></a>
</p>


<?php 
$footerfile = "$basedir/themes/".$project_prefs['theme_style']."/footer.inc.php"; 
if(file_exists("$footerfile")) { 
 include("$footerfile"); 
} 
?> 

</body>
</html>

<?php
// End of file delivery / showing the page
};
?>
