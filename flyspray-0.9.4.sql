## Inserting this file into a mysql database will create the table
## structure necessary to run the Flyspray bug tracking system.

CREATE TABLE flyspray_attachments (
  attachment_id mediumint(5) NOT NULL auto_increment,
  task_id mediumint(10) NOT NULL default '0',
  orig_name varchar(100) NOT NULL default '',
  file_name varchar(30) NOT NULL default '',
  file_desc varchar(100) NOT NULL default '',
  file_type varchar(50) NOT NULL default '',
  file_size mediumint(20) NOT NULL default '0',
  added_by mediumint(3) NOT NULL default '0',
  date_added varchar(12) NOT NULL default '',
  PRIMARY KEY  (attachment_id)
) TYPE=MyISAM COMMENT='List the names and locations of files attached to tasks';

CREATE TABLE flyspray_comments (
  comment_id mediumint(10) NOT NULL auto_increment,
  task_id mediumint(10) NOT NULL default '0',
  date_added varchar(12) NOT NULL default '',
  user_id mediumint(3) NOT NULL default '0',
  comment_text longtext NOT NULL,
  PRIMARY KEY  (comment_id)
) TYPE=MyISAM;

CREATE TABLE flyspray_groups (
  group_id mediumint(3) NOT NULL auto_increment,
  group_name varchar(20) NOT NULL default '',
  group_desc varchar(150) NOT NULL default '',
  is_admin mediumint(1) NOT NULL default '0',
  can_open_jobs mediumint(1) NOT NULL default '0',
  can_modify_jobs mediumint(1) NOT NULL default '0',
  can_add_comments mediumint(1) NOT NULL default '0',
  can_attach_files mediumint(1) NOT NULL default '0',
  can_vote mediumint(1) NOT NULL default '0',
  group_open mediumint(1) NOT NULL default '0',
  PRIMARY KEY  (group_id)
) TYPE=MyISAM COMMENT='User Groups for the Flyspray bug killer';

INSERT INTO flyspray_groups VALUES (1, 'Admin', 'Members have unlimited access to all functionality.', 1, 1, 1, 1, 1, 1, 1);
INSERT INTO flyspray_groups VALUES (2, 'Developers', 'The core development team', 0, 1, 1, 1, 1, 1, 1);
INSERT INTO flyspray_groups VALUES (3, 'Contributors', 'Additional helpers who submit patches', 0, 1, 0, 1, 1, 1, 1);
INSERT INTO flyspray_groups VALUES (4, 'Reporters', 'These people can open new jobs only', 0, 1, 0, 0, 0, 0, 1);
INSERT INTO flyspray_groups VALUES (5, 'Pending', 'Users who are awaiting approval of their accounts.', 0, 0, 0, 0, 0, 0, 0);

CREATE TABLE flyspray_list_category (
  category_id mediumint(3) NOT NULL auto_increment,
  category_name varchar(30) NOT NULL default '',
  list_position mediumint(3) NOT NULL default '0',
  show_in_list mediumint(1) NOT NULL default '0',
  category_owner mediumint(3) NOT NULL default '0',
  PRIMARY KEY  (category_id)
) TYPE=MyISAM;

INSERT INTO flyspray_list_category VALUES (1, 'Backend / Core', 1, 1, 0);
INSERT INTO flyspray_list_category VALUES (2, 'User Interface', 2, 1, 0);

CREATE TABLE flyspray_list_os (
  os_id mediumint(3) NOT NULL auto_increment,
  os_name varchar(20) NOT NULL default '',
  list_position mediumint(3) NOT NULL default '0',
  show_in_list mediumint(1) NOT NULL default '0',
  PRIMARY KEY  (os_id)
) TYPE=MyISAM COMMENT='Operating system list for the Flyspray bug killer';

INSERT INTO flyspray_list_os VALUES (1, 'All', 1, 1);
INSERT INTO flyspray_list_os VALUES (2, 'Windows', 2, 1);
INSERT INTO flyspray_list_os VALUES (3, 'Linux', 3, 1);
INSERT INTO flyspray_list_os VALUES (4, 'Mac OS', 4, 1);
INSERT INTO flyspray_list_os VALUES (5, 'UNIX', 4, 1);

CREATE TABLE flyspray_list_resolution (
  resolution_id mediumint(3) NOT NULL auto_increment,
  resolution_name varchar(20) NOT NULL default '',
  list_position mediumint(3) NOT NULL default '0',
  show_in_list mediumint(1) NOT NULL default '0',
  PRIMARY KEY  (resolution_id)
) TYPE=MyISAM;

INSERT INTO flyspray_list_resolution VALUES (1, 'None', 1, 1);
INSERT INTO flyspray_list_resolution VALUES (2, 'Not a bug', 2, 1);
INSERT INTO flyspray_list_resolution VALUES (3, 'Won\'t fix', 3, 1);
INSERT INTO flyspray_list_resolution VALUES (4, 'Won\'t implement', 4, 1);
INSERT INTO flyspray_list_resolution VALUES (5, 'Works for me', 5, 1);
INSERT INTO flyspray_list_resolution VALUES (6, 'Duplicate', 6, 1);
INSERT INTO flyspray_list_resolution VALUES (7, 'Deferred', 7, 1);
INSERT INTO flyspray_list_resolution VALUES (8, 'Fixed', 8, 1);
INSERT INTO flyspray_list_resolution VALUES (9, 'Implemented', 9, 1);

CREATE TABLE flyspray_list_tasktype (
  tasktype_id mediumint(3) NOT NULL auto_increment,
  tasktype_name varchar(20) NOT NULL default '',
  list_position mediumint(3) NOT NULL default '0',
  show_in_list mediumint(1) NOT NULL default '0',
  PRIMARY KEY  (tasktype_id)
) TYPE=MyISAM COMMENT='List of task types for Flyspray the bug killer.';

INSERT INTO flyspray_list_tasktype VALUES (1, 'Bug Report', 1, 1);
INSERT INTO flyspray_list_tasktype VALUES (2, 'Feature Request', 2, 1);
INSERT INTO flyspray_list_tasktype VALUES (3, 'Support Request', 3, 1);

CREATE TABLE flyspray_list_version (
  version_id mediumint(3) NOT NULL auto_increment,
  version_name varchar(10) NOT NULL default '',
  list_position mediumint(3) NOT NULL default '0',
  show_in_list mediumint(1) NOT NULL default '0',
  PRIMARY KEY  (version_id)
) TYPE=MyISAM;

INSERT INTO flyspray_list_version VALUES (1, 'CVS', 1, 1);
INSERT INTO flyspray_list_version VALUES (2, '1.0', 2, 1);

CREATE TABLE flyspray_notifications (
  notify_id mediumint(10) NOT NULL auto_increment,
  task_id mediumint(10) NOT NULL default '0',
  user_id mediumint(5) NOT NULL default '0',
  PRIMARY KEY  (notify_id)
) TYPE=MyISAM COMMENT='Extra task notifications are stored here';

CREATE TABLE flyspray_prefs (
  pref_id mediumint(1) NOT NULL auto_increment,
  pref_name varchar(20) NOT NULL default '',
  pref_value varchar(50) NOT NULL default '',
  pref_desc varchar(100) NOT NULL default '',
  PRIMARY KEY  (pref_id)
) TYPE=MyISAM COMMENT='Application preferences are set here';

INSERT INTO flyspray_prefs VALUES (1, 'anon_open', '2', 'Allow anonymous users to open new tasks');
INSERT INTO flyspray_prefs VALUES (2, 'theme_style', 'Bluey', 'Theme / Style');
INSERT INTO flyspray_prefs VALUES (3, 'jabber_server', '', 'Jabber server');
INSERT INTO flyspray_prefs VALUES (4, 'jabber_port', '5222', 'Jabber server port');
INSERT INTO flyspray_prefs VALUES (5, 'jabber_username', '', 'Jabber username');
INSERT INTO flyspray_prefs VALUES (6, 'jabber_password', '', 'Jabber password');
INSERT INTO flyspray_prefs VALUES (8, 'anon_group', '4', 'Group for anonymous registrations');
INSERT INTO flyspray_prefs VALUES (7, 'project_title', 'Flyspray - The bug killer!', 'Project title');
INSERT INTO flyspray_prefs VALUES (9, 'base_url', 'http://yourdomain/flyspray/', 'Base URL for this installation');
INSERT INTO flyspray_prefs VALUES (10, 'user_notify', '1', 'Force task notifications as');
INSERT INTO flyspray_prefs VALUES (11, 'admin_email', 'flyspray@yourdomain', 'Reply email address for notifications');
INSERT INTO flyspray_prefs VALUES (12, 'assigned_groups', '1 2 3', 'Members of these groups can be assigned tasks');
INSERT INTO flyspray_prefs VALUES (13, 'default_cat_owner', '', 'Default category owner');
INSERT INTO flyspray_prefs VALUES (14, 'lang_code', 'en', 'Language');
INSERT INTO flyspray_prefs VALUES (15, 'spam_proof', '1', 'Use confirmation codes for user registrations');
INSERT INTO flyspray_prefs VALUES (16, 'anon_view', '1', 'Allow anonymous users to view this BTS');

CREATE TABLE flyspray_registrations (
  reg_id mediumint(10) NOT NULL auto_increment,
  reg_time varchar(12) NOT NULL default '',
  confirm_code varchar(20) NOT NULL default '',
  PRIMARY KEY  (reg_id)
) TYPE=MyISAM COMMENT='Storage for new user registration confirmation codes';

CREATE TABLE flyspray_related (
  related_id mediumint(10) NOT NULL auto_increment,
  this_task mediumint(10) NOT NULL default '0',
  related_task mediumint(10) NOT NULL default '0',
  PRIMARY KEY  (related_id)
) TYPE=MyISAM COMMENT='Related task entries';

CREATE TABLE flyspray_tasks (
  task_id mediumint(10) NOT NULL auto_increment,
  task_type mediumint(3) NOT NULL default '0',
  date_opened varchar(12) NOT NULL default '',
  opened_by mediumint(3) NOT NULL default '0',
  date_closed varchar(12) NOT NULL default '',
  closed_by mediumint(3) NOT NULL default '0',
  item_summary varchar(100) NOT NULL default '',
  detailed_desc longtext NOT NULL,
  item_status mediumint(3) NOT NULL default '0',
  assigned_to mediumint(3) NOT NULL default '0',
  resolution_reason mediumint(3) NOT NULL default '1',
  product_category mediumint(3) NOT NULL default '0',
  product_version mediumint(3) NOT NULL default '0',
  closedby_version mediumint(3) NOT NULL default '0',
  operating_system mediumint(3) NOT NULL default '0',
  task_severity mediumint(3) NOT NULL default '0',
  last_edited_by mediumint(3) NOT NULL default '0',
  last_edited_time varchar(12) NOT NULL default '0',
  percent_complete mediumint(3) NOT NULL default '0',
  PRIMARY KEY  (task_id)
) TYPE=MyISAM COMMENT='Bugs and feature requests for the Flyspray bug killer';

INSERT INTO flyspray_tasks VALUES (1, 1, '1061341664', 1, '', 0, 'Test Task', 'This isn\'t a real task.  You should close it and report some real ones.', 2, 0, 0, 2, 1, 2, 1, 1, 0, '', 0);

CREATE TABLE flyspray_users (
  user_id mediumint(3) NOT NULL auto_increment,
  user_name varchar(20) NOT NULL default '',
  user_pass varchar(30) NOT NULL default '',
  real_name varchar(100) NOT NULL default '',
  group_in mediumint(3) NOT NULL default '0',
  jabber_id varchar(100) NOT NULL default '',
  email_address varchar(100) NOT NULL default '',
  notify_type mediumint(1) NOT NULL default '0',
  account_enabled mediumint(1) NOT NULL default '0',
  PRIMARY KEY  (user_id)
) TYPE=MyISAM COMMENT='Users for the Flyspray bug killer';

INSERT INTO flyspray_users VALUES (1, 'super', '4tuKHcjxpFYag', 'Mr Super User', 1, 'super@yourdomain', 'super@yourdomain', 2, 1);
