<?php

////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2016  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of either the current Phorum License (viewable at     //
//   phorum.org) or the Phorum License that was distributed with this file    //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
////////////////////////////////////////////////////////////////////////////////
define('phorum_page','version_iframe');

// Check for new versions of the Phorum software, for the <iframe> in the
// admin interface header. A cookie remembers the answer, so that the latest
// version isn't fetched from GitHub again on every admin page.

require_once('./common.php');

// Only an administrator may run the check.
phorum_api_user_session_restore(PHORUM_ADMIN_SESSION);
if (empty($PHORUM["user"]["admin"])) {
    exit();
}

if (isset($_COOKIE["phorum_upgrade_available"])) {
    $upgrade_available = $_COOKIE["phorum_upgrade_available"];
} else {
    require_once('./include/version_functions.php');
    $releases = phorum_find_upgrades();
    if (isset($releases["stable"]) && !empty($releases["stable"]["upgrade"])) {
        $upgrade_available = $releases["stable"]["version"];
    } elseif (isset($releases["development"]) && !empty($releases["development"]["upgrade"])) {
        $upgrade_available = $releases["development"]["version"];
    } else {
        $upgrade_available = 0;
    }
}
setcookie("phorum_upgrade_available", $upgrade_available, 0,
          $PHORUM["session_path"], $PHORUM["session_domain"]);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Phorum upgrade notification</title>
    <style type="text/css">
    body {
        background-color: white;
        margin: 0px;
        padding: 0px;
    }
    .notify_upgrade {
        text-align: center;
        border: 2px solid black;
        background-color: #e00000;
        padding: 3px;
        margin: 0px;
    }
    .notify_upgrade a {
        font-family: Lucida Sans Unicode,Lucida Grand,Verdana,Arial,Helvetica;
        color: white;
        font-weight: bold;
        font-size: 13px;
    }
    .notify_noupgrade {
        text-align: center;
        border: 1px solid black;
        padding: 3px;
        margin: 0px;
        font-family: Lucida Sans Unicode,Lucida Grand,Verdana,Arial,Helvetica;
        font-size: 13px;
    }
    </style>
  </head>
  <body>
  <?php if ($upgrade_available) { ?>
    <div class="notify_upgrade">
      <a target="_top" href="admin.php?module=version">New Phorum version <?php print htmlspecialchars($upgrade_available) ?> available!</a>
    </div>
  <?php } else { ?>
    <div class="notify_noupgrade">
      Your Phorum installation is up to date
    </div>
  <?php } ?>
  </body>
</html>
