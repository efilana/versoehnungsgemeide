<?php
/**
 * ChurchTools 2.0
 * http://www.churchtools.de
 *
 * Copyright (c) 2013 Jens Martin Rauen
 * Licensed under the MIT license, located in LICENSE.txt
 */

/* Important global vars */
$q = ""; // which module to use
$config = array (); // all config from config file and amended with table cc_config
$mapping = array (); // all mappings
$files_dir = null; // dir for page specific files

$add_header = ""; // http headers?
$content = ""; // page content
$user = null; // user
$embedded = false; // is calling is with embedding=true (without menu etc.)
$i18n = null; // translation object
$ajax = false; // to find out if its an ajax call

// TODO: most functions should be moved to churchcore/functions.php
// then the remaining code may be put into index.php
// or we could put the content of churchtools_main direct in this file

/**
 * Shutdown function, if an error happened, an error message is displayed.
 * FIXME: need to be changed - this errors are corrupting json answers
 *
 * global $ajax currently will be set to true in CTAjaxHandler! (there are errors caused by debugging which was hindering)
 * if error on ajax is needed, add something like $json['error'] = $info;
 *
 * error_get_last() dont respects error_reporting() - f.e. deprecated options in php.ini which are not shown
 * and maybe can not be changed by admins and dont influence scripting causes an error here!
 *
 * I think heavy core errors shouldnt be shown on the page and all others should be catched by an exception handler.
 */
function handleShutdown() {
  global $ajax;
  $error = error_get_last();
  if (!$ajax && $error !== NULL) { // no Error notizes on ajax requests!
    $info = "[ERROR] file:" . $error['file'] . ":" . $error['line'] . " <br/><i>" . $error['message'] . '</i>' . PHP_EOL;
    echo '<div class="alert alert-error">' . $info . '</div>';
  }
}

/**
 * Load config from file while checking for multisite installation.
 *
 *
 * For multisite use you have to add a folder for each subdomain in sites,
 * eg sites/mghh for mghh.churchtools.de.
 * Don't works for more then one subdomain like intern.mghh.churchtools.de
 */
function loadConfig() {
  global $files_dir;
  // Unix default. Should have ".conf" extension as per standards.
  $config = null;

  // read config, based on subdomain.
  // WARNING: This code dont works for per IP address access and supports only last subdomain.
  if (strpos($_SERVER["SERVER_NAME"], ".") > 0) {
    $subdomain = substr($_SERVER["SERVER_NAME"], 0, strpos($_SERVER["SERVER_NAME"], "."));
    $cnf_location = SITES . "/$subdomain/churchtools.config";
    if (file_exists($cnf_location)) {
      $config = parse_ini_file($cnf_location);
      $files_dir = SITES . "/$subdomain";
    }
  }

  // if no config, read default config
  $cnf_location = DEFAULT_SITE . "/churchtools.config";
  if ($config == null && file_exists($cnf_location)) {
    $config = parse_ini_file($cnf_location);
  }

  // if still no config, look in default linux etc location
  $cnf_location = "/etc/churchtools/default.conf";
  if ($config == null && @file_exists($cnf_location)) {
    $config = parse_ini_file($cnf_location);
  }

  // still no config? Look fo r host specific config in etc
  // Package installed, per domain.
  // All possible virt-hosts in HTTP server has to be symlinked to it.
  $cnf_location = "/etc/churchtools/hosts/" . $_SERVER["SERVER_NAME"] . ".conf";
  if ($config == null && @file_exists($cnf_location)) {
    $config = parse_ini_file($cnf_location);
  }

  //
  if ($config == null) { // TODO: maybe use template
    $error_message = "<h3>" . t('error.config.file.not.found') . "</h3>
        <p>Expected locations are:
        <ul>
          <li>Default appliance: <code>/etc/churchtools/default.conf</code></li>
          <li>Per-domain appliance: <code>/etc/churchtools/hosts/" . $_SERVER["SERVER_NAME"] . ".conf</code></li>
          <li>Shared hosting per domain: <code><i>YOUR_INSTALLATION</i>/sites/" . $_SERVER["SERVER_NAME"] . "/churchtools.config</code></li>
          <li>Hosting per sub-domain: <code><i>YOUR_INSTALLATION</i>/sites/<b>&lt;subdomain&gt;.&lt;domain&gt;</b>/churchtools.config</code></li>
          <li>Shared hosting default (single installation):
              <code><i>YOUR_INSTALLATION</i>/" . DEFAULT_SITE . "/churchtools.config</code>
          </li>
        </ul>
        <div class=\"alert alert-info\">You can also use <strong>example</strong> file in
          <code><i>INSTALLATION</i>/" . DEFAULT_SITE . "/churchtools.example.config</code>
          by renaming it to either location that suits your setup and edit it to meet your needs.</div>";

    addErrorMessage($error_message);
  }
  else $config["_current_config_file"] = $cnf_location;

  return $config;
}

/**
 * Loads all config data from the db
 */
function loadDBConfig() {
  global $config;
  try {
    $res = db_query("SELECT * FROM {cc_config}", null, false);
    foreach ($res as $val) $config[$val->name] = $val->value;
  }
  catch (SQLException $e) {
    // do nothing
  }
}

/**
 * Load url mappings for each module and merge them together
 * module map path like like system/churchdb/churchdb.mapping
 *
 * @return array
 */
function loadMapping() {
  $map = parse_ini_file(SYSTEM . "/churchtools.mapping");

  foreach (churchcore_getModulesSorted(true) as $module) {
    if (file_exists(SYSTEM . "/$module/$module.mapping")) {
      $modMap = parse_ini_file(SYSTEM . "/$module/$module.mapping");
      if (isset($modMap["page_with_noauth"]) && isset($map["page_with_noauth"])) {
        $modMap["page_with_noauth"] = array_merge($modMap["page_with_noauth"], $map["page_with_noauth"]);
      }
      $map = array_merge($map, $modMap);
    }
  }
  return $map;
}

/**
 * Loads the user object in the session.
 *
 * If there is no user, it will create an anymous user
 */
function loadUserObjectInSession() {
  global $q;
  if (!isset($_SESSION['user'])) {
    // Wenn nicht ausgeloggt wird und RememberMe bei der letzten Anmeldung aktiviert wurde
    if ($q != "logout" && isset($_COOKIE['RememberMe']) && $_COOKIE['RememberMe'] == 1) {
      if (isset($_COOKIE['CC_SessionId'])) {

        $res = db_query("SELECT * FROM {cc_session}
                         WHERE session=:session AND hostname=:hostname",
                         array(":session" => $_COOKIE['CC_SessionId'],
                               ":hostname" => $_SERVER["HTTP_HOST"]
               ));
        // if session exists, read user data
        if ($res) {
          $res = $res->fetch();
          if (isset($res->person_id)) {

            $res = db_query("SELECT * FROM {cdb_person}
                             WHERE id=:id",
                             array (":id" => $res->person_id))
                             ->fetch();
            $res->auth = getUserAuthorization($res->id);
            $_SESSION['user'] = $res;
            addInfoMessage(t('welcome.back.x', $res->vorname), true);
          }
        }
      }
    }
    if (!isset($_SESSION['user']))  createAnonymousUser();
  }
  else {
    $_SESSION["user"]->auth = getUserAuthorization($_SESSION["user"]->id);
    if (isset($_COOKIE['CC_SessionId'])) {
      $dt = new DateTime();

      db_query("UPDATE {cc_session} SET datum=:datum
                WHERE person_id=:p_id AND session=:session AND hostname=:hostname",
                array (":datum" => $dt->format('Y-m-d H:i:s'),
                       ":session" => $_COOKIE['CC_SessionId'],
                       ":p_id" => $_SESSION["user"]->id,
                       ":hostname" => $_SERVER["HTTP_HOST"],
                ));
    }
  }
}

/**
 * For accept data security
 */
function pleaseAcceptDatasecurity() {
  global $user, $q;
  include_once (CHURCHWIKI . "/churchwiki.php");
  if (getVar("acceptsecurity")) {
    db_query("UPDATE {cdb_person}
              SET acceptedsecurity=current_date()
              WHERE id=$user->id");
    $user->acceptedsecurity = new DateTime();
    addInfoMessage(t("datasecurity.accept.thanks"));

    return churchtools_processRequest($q);
  }

  $data = churchwiki_load("Sicherheitsbestimmungen", 0);
  $text = str_replace("[Vorname]", $user->vorname, $data->text);
  $text = str_replace("[Nachname]", $user->name, $text);
  $text = str_replace("[Spitzname]", ($user->spitzname == "" ? $user->vorname : $spitzname), $text);

  $text = '<div class="container-fluid"><div class="well">' . $text;
  $text .= '<a href="?q=' . $q . '&acceptsecurity=true" class="btn btn-important">' . t("datasecurity.accept") . '</a>';
  $text .= '</div></div>';

  return $text;
}

/**
 * calls churchservice => churchservice_main or churchservice/ajax => churchservice_ajax
 *
 * @param $q - Complete request URL inkl. suburl e.g. churchservice/ajax
 *
 *          TODO: should completely rewritten, using some classes
 */
function churchtools_processRequest($_q) {
  global $mapping, $config, $q;

  $content = "";

  // include mapped file
  if (isset($mapping[$_q])) {
    include_once (SYSTEM . "/" . $mapping[$_q]);

    $param = "main";
    if (strpos($_q, "/") > 0) {
      $param = "_" . substr($_q, strpos($_q, "/") + 1, 99);
      $_q = substr($_q, 0, strpos($_q, "/"));
    }

    if ((!user_access("view", $_q)) && (!in_array($_q, $mapping["page_with_noauth"])) && ($_q != "login")
         && (!in_array($_q, (isset($config["page_with_noauth"]) ? $config["page_with_noauth"] : array ())))) {
      if (!userLoggedIn()) {
      // only show login
        if (strrpos($q, "ajax") === false) {
          $q = "login";

          return churchtools_processRequest("login");
        }
        else {
          drupal_json_output(jsend()->error("Session expired!"));

          die();
        }
      }
      else {
        $name = $_q;
        if (isset($config[$_q . "_name"])) $name = $config[$_q . "_name"];
        addInfoMessage(t("no.permission.for", $name));

        return "";
      }
    }
    // does the main work?
    $content .= call_user_func($_q . "_" . $param);
    if ($content == null) die();
  }
  else
    addErrorMessage(t("mapping.not.found", "<i>$_q</i>"));
  return $content;
}


/**
 * Main entry point for churchtools.
 * This will be called from /index.php
 * Function load constants and simple functions and have a try and catch for the whole application
 * It calls churchtools_app().
 */
function churchtools_main() {
  try {
    require ("system/includes/constants.php");
    include_once (INCLUDES."/functions.php");
    includePlugins();
    churchtools_app();
  }
  catch ( SQLException $e ) {
    //  TODO: get sql and show it to admin only
    //  if (DEBUG) {
    //  echo "<h3>PDO-Error:</h3>", $db->errorCode(), "<br>", $db->lastQuery(), '<br>';
    //  }
    //  else {
    //  echo "<h3>Database-Error:</h3>", "There is an error";
    //  }

    CTException::reportError ( $e );
  }
  catch ( CTException $e ) {
    $e->reportError ( $e );
  }
  catch ( Exception $e ) {
    echo '
<div class="alert alert-error">
    <h3>Sorry, but there is an Error:</h3>
    <p><br/>'. $e->getMessage (). '</p>
  </div>';
  }
}

/**
 * Log all input params to files
 */
function logParams() {
  global $files_dir;
  $date = new DateTime();
  $myVar = $date->format('Y-m-d H:i:s'). " : " . $_SERVER["SERVER_NAME"] . " ";
  if (isset($_SESSION) && isset($_SESSION["user"])) $myVar .= $_SESSION["user"]->cmsuserid . "[" . $_SESSION["user"]->id . "] ";
  $myVar .= "- " . $_SERVER['HTTP_USER_AGENT'] . NL;
  $myVar .= print_r($_REQUEST, true);
  $myVar .= NL;
  file_put_contents("$files_dir/tmp/churchtools.log", $myVar, FILE_APPEND);
}

/**
 * TODO: put this into churchtools_main, no need for two functions
 *
 * Main entry point for churchtools.
 * This will be called from /index.php
 * Function loads i18n, configuration, check data security.
 * If everything is ok, it calls churchtools_processRequest()
 */
function churchtools_app() {
  global $q, $q_orig, $currentModule, $add_header, $config, $mapping, $content, $base_url, $files_dir, $user, $embedded, $i18n;
  include_once (CHURCHCORE . "/churchcore_db.php");

  $files_dir = DEFAULT_SITE;

  // which module is requested?
  $q = $q_orig = getVar("q", userLoggedIn() ? "home" : getConf("site_startpage", "home"));
  // $currentModule is needed for class autoloading and maybe other include paths
  list ($currentModule) = explode('/', getVar("q")); // get first part of $q or churchcore
  $embedded = getVar("embedded", false);

  $base_url = getBaseUrl();
  $config = loadConfig();
  if ($config) {
    if (db_connect()) {
      // DBConfig overwrites the config files
      loadDBConfig();
      if (empty($config['site_name'])) $config['site_name'] = 'ChurchTools'; //dont allow site_name to be empty

      date_default_timezone_set(getConf("timezone", "Europe/Berlin"));

      if (isset($_COOKIE["language"])) $config["language"] = $_COOKIE["language"];

      // Load i18n churchcore-bundle
      if (!isset($config["language"])) {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $config["language"] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        else $config["language"] = DEFAULT_LANGUAGE;
      }
      $i18n = new TextBundle(CHURCHCORE . "/resources/messages");
      $i18n->load("churchcore", ($config["language"] != null ? $config["language"] : null));

      // Session Init
      if (!file_exists($files_dir . "/tmp")) @mkdir($files_dir . "/tmp", 0775, true);
      if (!file_exists($files_dir . "/tmp")) {
        // Admin should act accordingly, default suggestion is 0755.
        addErrorMessage(t("permission.denied.write.dir", $files_dir));
      }
      else session_save_path($files_dir . "/tmp");
      session_name("ChurchTools_" . $config["db_name"]);
      session_start();
      register_shutdown_function('handleShutdown');

      // Check for offline mode. If it's activated display message and return false;
      if (getConf("site_offline") == 1) {
        if (!isset($_SESSION["user"]) || !in_array($_SESSION["user"]->id, getConf("admin_ids"))) {
          echo t("site.is.down");
          return false;
        }
      }
      $embedded = getVar("embedded", false);
      $mapping = loadMapping();
      $success = true;
      // Check for DB-Updates and loginstr only if this is not an ajax call.
      if (strrpos($q, "ajax") === false) {
        $success = checkForDBUpdates();
      }

      // Log if debug ist activated
      if (isset($config["debug"])) logParams();

      if ($success) {
        // Is there a loginstr which does not fit to the current logged in user?
        if (getVar("loginstr") && getVar("id") && userLoggedIn() && $_SESSION["user"]->id != getVar("id")) {
          logout_current_user();
          session_start();
        }
        else
          loadUserObjectInSession();
      }

      if ($success) {
        if (isset($_SESSION['user'])) $user = $_SESSION['user'];

        // Accept data security?
        if ((userLoggedIn()) && (!isset($_SESSION["simulate"])) && ($q != "logout") &&
             (isset($config["accept_datasecurity"])) && ($config["accept_datasecurity"] == 1) &&
             (!isset($user->acceptedsecurity))) $content .= pleaseAcceptDatasecurity();
        else $content .= churchtools_processRequest($q);
      }
    }
  }

  // TODO: i changed header/footer to a sort of template
  // probably some more logic could be removed from them by setting some more variables here
  // put header/footer into new file layout.php and add a variable $content
  $lang     = getConf("language");
  $simulate = getVar("simulate", false, $_SESSION);
  $sitename = getConf("site_name");
  if (getConf("test")) $sitename .= " TEST ";
  $logo = ($logo = getConf("site_logo")) ? "$files_dir/files/logo/$logo" : '';

  include (INCLUDES . "/header.php");
  echo $content;
  include (INCLUDES . "/footer.php");
}
