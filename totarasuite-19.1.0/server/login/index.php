<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Main login page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\login\complete_login_callback;

if (!empty($_POST)) {
    // Totara: no permanent auto login here via cookie if the login and password come from a post request.
    define('PERSISTENT_LOGIN_SKIP', true);
}

require('../config.php');
require_once('lib.php');

// Try to prevent searching for sites that allow sign-up.
if (!isset($CFG->additionalhtmlhead)) {
    $CFG->additionalhtmlhead = '';
}
$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';

// Totara: Hook to modify setup of login page.
$hook = new core\hook\login_page_start();
$hook->execute();

redirect_if_major_upgrade_required();

$testsession = optional_param('testsession', 0, PARAM_INT); // test session works properly
$anchor      = optional_param('anchor', '', PARAM_RAW);      // Used to restore hash anchor to wantsurl.

if (defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING) {
    $behat_username = optional_param('behat_login_as', null, PARAM_RAW);
    if ($behat_username) {
        $user = authenticate_user_login($behat_username, $behat_username, false, $errorcode);
        if ($user) {
            complete_user_login(
                $user,
                complete_login_callback::create(
                    'login_page_complete_login_callback',
                    [false, $SITE->fullname, get_string('loginsite')]
                )
            );
        } else {
            echo 'Invalid login';
        }
        exit;
    }
}

$context = context_system::instance();
$PAGE->set_url("$CFG->wwwroot/login/index.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');
$PAGE->set_cacheable(false);

/// Initialize variables
$errormsg = '';
$errorcode = 0;

// login page requested session test
if ($testsession) {
    if ($testsession == $USER->id) {
        if (isset($SESSION->wantsurl)) {
            $urltogo = $SESSION->wantsurl;
        } else {
            $urltogo = $CFG->wwwroot.'/';
        }
        unset($SESSION->wantsurl);
        redirect($urltogo);
    } else {
        // TODO: try to find out what is the exact reason why sessions do not work
        $errormsg = get_string("cookiesnotenabled");
        $errorcode = 1;
    }
}

/// Check for timed out sessions
if (!empty($SESSION->has_timed_out)) {
    $session_has_timed_out = true;
    unset($SESSION->has_timed_out);
} else {
    $session_has_timed_out = false;
}

// Automatically redict to SSO provider if one is set.
$skip_soo_redirect = optional_param('nosso', 0, PARAM_INT);
if (!$skip_soo_redirect &&
    !$testsession &&
    !(isloggedin() && !isguestuser()) &&
    !data_submitted() &&
    !empty($CFG->loginpageredirect) &&
    empty($SESSION->loginerrormsg)
) {
    // Remember where we came from before we redirect off.
    core_login_set_wants_url();

    [$authname, $providername] = explode(':::', $CFG->loginpageredirect, 2);
    $authplugin = get_auth_plugin($authname);
    $providers = $authplugin->loginpage_idp_list(true);
    foreach ($providers as $provider) {
        if ($provider['name'] == $providername) {
            redirect($provider['url']);
        }
    }
}

/// auth plugins may override these - SSO anyone?
$frm  = false;
$user = false;

// if the login form is submitted and (there's an active session or pending mfa verification), show confirm logout.
if (data_submitted()) {
    // User has active session or pending session that requires mfa
    // The existing user may be a guest user, don't prompt for them.
    $loggedin = isloggedin() && !isguestuser();
    $pending_mfa = empty($USER->mfa_completed) && !empty($USER->pseudo_id);

    if ($loggedin || $pending_mfa) {
        $PAGE->set_pagelayout('pending_login');

        // Set the message and buttons to display
        switch (true) {
            case $loggedin:
                $message = get_string('already_loggedin');
                $logout_button = $OUTPUT->single_button(new moodle_url('/login/logout.php', ['sesskey' => sesskey()]), get_string('signout'), 'post', ['primary' => true]);
                $return_button = $OUTPUT->single_button($CFG->wwwroot . '/', get_string('cancel'));
                $buttons = $logout_button . $return_button;
                break;
            case $pending_mfa:
                $message = get_string('has_pending_mfa_session');
                $continue_mfa_button = $OUTPUT->single_button($CFG->wwwroot . '/mfa/verify.php', get_string('continue_mfa'), 'post', ['primary' => true]);
                $sign_in_as_different_user_button = $OUTPUT->single_button(new moodle_url('/login/logout.php', ['sesskey' => sesskey()]), get_string('sign_in_as_another_user'));
                $buttons = $continue_mfa_button . $sign_in_as_different_user_button;
                break;
        }

        echo $OUTPUT->header();
        $output = $OUTPUT->box_start('generalbox modal modal-dialog modal-in-page show', 'notice');
        $output .= $OUTPUT->box_start('modal-content', 'modal-content');
        $output .= $OUTPUT->box_start('modal-header', 'modal-header');
        $output .= html_writer::tag('h2', get_string('confirm'));
        $output .= $OUTPUT->box_end();
        $output .= $OUTPUT->box_start('modal-body', 'modal-body');
        $output .= html_writer::tag('p', $message);
        $output .= $OUTPUT->box_end();
        $output .= $OUTPUT->box_start('modal-footer', 'modal-footer');
        $output .= html_writer::tag('div', $buttons, array('class' => 'buttons'));
        $output .= $OUTPUT->box_end();
        $output .= $OUTPUT->box_end();
        $output .= $OUTPUT->box_end();
        echo $output;
        echo $OUTPUT->footer();
        die;
    }
}

// Redirect to mfa verification.
if(empty($USER->mfa_completed) && !empty($USER->pseudo_id)) {
    redirect($CFG->wwwroot .'/mfa/verify.php');
}

$authsequence = get_enabled_auth_plugins(true); // auths, in sequence
foreach($authsequence as $authname) {
    $authplugin = get_auth_plugin($authname);
    $authplugin->loginpage_hook();
}


/// Define variables used in page
$site = get_site();

// Ignore any active pages in the navigation/settings.
// We do this because there won't be an active page there, and by ignoring the active pages the
// navigation and settings won't be initialised unless something else needs them.
$PAGE->navbar->ignore_active();
$loginsite = get_string("loginsite");
$PAGE->navbar->add($loginsite);

if ($user !== false or $frm !== false or $errormsg !== '') {
    // some auth plugin already supplied full user, fake form data or prevented user login with error message

} else if (!empty($CFG->allowlogincsrf) && !empty($SESSION->wantsurl) && file_exists($CFG->dirroot.'/login/weblinkauth.php')) {
    // Handles the case of another Moodle site linking into a page on this site
    //TODO: move weblink into own auth plugin
    include($CFG->dirroot.'/login/weblinkauth.php');
    if (function_exists('weblink_auth')) {
        $user = weblink_auth($SESSION->wantsurl);
    }
    if ($user) {
        $frm->username = $user->username;
    } else {
        $frm = data_submitted();
    }

} else {
    $frm = data_submitted();

    if ($frm) {
        // Totara: prevent CSRF in regular login page, note that this breaks alternate login url.
        if (empty($CFG->allowlogincsrf)) {
            if (!isset($frm->logintoken) or $frm->logintoken !== sesskey()) {
                $errormsg = get_string('sessionerroruser2', 'error');
                $errorcode = AUTH_LOGIN_CSRF;
                $frm = false;
            }
        }
    }

    // TOTARA: keeping form state on incorrect submission (TL-7236)
    if (isset($frm->username)) {
        $SESSION->login_username = $frm->username;
        $SESSION->login_remember = !empty($frm->rememberusernamechecked);
        if (!$SESSION->login_remember && ((int)$CFG->rememberusername !== 1) && empty($CFG->persistentloginenable)) {
            set_moodle_cookie('');
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        unset($SESSION->login_username);
        unset($SESSION->login_remember);
    }
}

// Restore the #anchor to the original wantsurl. Note that this
// will only work for internal auth plugins, SSO plugins such as
// SAML / CAS / OIDC will have to handle this correctly directly.
if (!$errorcode && $anchor && isset($SESSION->wantsurl) && strpos($SESSION->wantsurl, '#') === false) {
    $wantsurl = new moodle_url($SESSION->wantsurl);
    $wantsurl->set_anchor(substr($anchor, 1));
    $SESSION->wantsurl = $wantsurl->out();
}

/// Check if the user has actually submitted login data to us

if (!$errorcode && $frm and isset($frm->username)) {                             // Login WITH cookies

    $frm->username = trim(core_text::strtolower($frm->username));

    if (is_enabled_auth('none') ) {
        if ($frm->username !== core_user::clean_field($frm->username, 'username')) {
            $errormsg = get_string('username').': '.get_string("invalidusername");
            $errorcode = 2;
            $user = null;
        }
    }

    if ($user) {
        //user already supplied by aut plugin prelogin hook
    } else if (($frm->username == 'guest') and empty($CFG->guestloginbutton)) {
        $user = false;    /// Can't log in as guest if guest button is disabled
        $frm = false;
    } else {
        if (empty($errormsg)) {
            $user = authenticate_user_login($frm->username, $frm->password, false, $errorcode);
        }
    }

    // Intercept 'restored' users to provide them with info & reset password
    if (!$user and $frm and is_restored_user($frm->username)) {
        $PAGE->set_title(get_string('restoredaccount'));
        $PAGE->set_heading($site->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->page_main_heading(get_string('restoredaccount'));
        echo $OUTPUT->box(get_string('restoredaccountinfo'), 'generalbox boxaligncenter');
        require_once('restored_password_form.php'); // Use our "supplanter" login_forgot_password_form. MDL-20846
        $form = new login_forgot_password_form('forgot_password.php', array('username' => $frm->username));
        $form->display();
        echo $OUTPUT->footer();
        die;
    }

    if ($user) {

        // language setup
        if (isguestuser($user)) {
            // no predefined language for guests - use existing session or default site lang
            unset($user->lang);

        } else if (!empty($user->lang)) {
            // unset previous session language - use user preference instead
            unset($SESSION->lang);
        }

        if (empty($user->confirmed)) {       // This account was never confirmed
            $PAGE->set_title(get_string("mustconfirm"));
            $PAGE->set_heading($site->fullname);
            echo $OUTPUT->header();
            echo $OUTPUT->page_main_heading(get_string("mustconfirm"));
            echo $OUTPUT->box(get_string("emailconfirmsent", "", $user->email), "generalbox boxaligncenter");
            echo $OUTPUT->footer();
            die;
        }

        // if multiple logins not permitted, clear out any existing sessions for this user
        if (!empty($CFG->preventmultiplelogins)) {
            \core\session\manager::kill_user_sessions($user->id);
        }

        /// Let's get them all set up.
        $remember_username_checked = !empty($frm->rememberusernamechecked);
        $site_fullname = $site->fullname;

        complete_user_login(
            $user,
            complete_login_callback::create(
                'login_page_complete_login_callback',
                [$remember_username_checked, $site_fullname, $loginsite]
            )
        );

        // MFA requires redirection:
        if ($USER->id !== $user->id && !$USER->mfa_completed) {
            redirect($CFG->wwwroot .'/mfa/verify.php');
        }

        // Discard any errors before the last redirect.
        unset($SESSION->loginerrormsg);

        // test the session actually works by redirecting to self
        $SESSION->wantsurl = $urltogo;
        redirect(new moodle_url(get_login_url(), array('testsession'=>$USER->id)));

    } else {
        if (empty($errormsg)) {
            if ($errorcode == AUTH_LOGIN_UNAUTHORISED) {
                $errormsg = get_string("unauthorisedlogin", "", $frm->username);
            } else {
                $errormsg = get_string("invalidlogin");
                $errorcode = 3;
            }
        }
    }
}

/// Detect problems with timedout sessions
if ($session_has_timed_out and !data_submitted()) {
    $errormsg = get_string('sessionerroruser', 'error');
    $errorcode = 4;
}

/// First, let's remember where the user was trying to get to before they got here
core_login_set_wants_url();

/// Redirect to alternative login URL if needed
if ((!empty($CFG->allowlogincsrf) || $authsequence[0] == 'shibboleth') && !empty($CFG->alternateloginurl)) { // Totara: alternate login url is deprecated!
    $loginurl = new moodle_url($CFG->alternateloginurl);

    $loginurlstr = $loginurl->out(false);

    if (strpos($SESSION->wantsurl, $loginurlstr) === 0) {
        // We do not want to return to alternate url.
        $SESSION->wantsurl = null;
    }

    // If error code then add that to url.
    if ($errorcode) {
        $loginurl->param('errorcode', $errorcode);
    }

    redirect($loginurl->out(false));
}

/// Generate the login page with forms
if (!isset($frm) or !is_object($frm)) {
    $frm = new stdClass();
}

if (empty($frm->username) && $authsequence[0] != 'shibboleth') {  // See bug 5184

    // TOTARA: keeping form state on incorrect submission (TL-7236)
    if (isset($SESSION->login_remember)){
        $frm->rememberusernamechecked = $SESSION->login_remember;
    }

    if (isset($SESSION->login_username)){
        $frm->username = $SESSION->login_username;
    } elseif (!empty($_GET["username"])) {
        // we do not want data from _POST here
        $frm->username = clean_param($_GET["username"], PARAM_RAW); // we do not want data from _POST here
    } else if (empty($CFG->persistentloginenable)) {
        // returning a username in the line below has always meant remember username checkbox should be checked.
        $frm->username = get_moodle_cookie();
        if (!empty($frm->username)){
            // We got it from a cookie, so we want it checked.
            $frm->rememberusername = '1';
            $frm->rememberusernamechecked = 1;
        }
    }
    unset($SESSION->login_remember);
    unset($SESSION->login_username);

    $frm->password = "";
}

if (!empty($SESSION->loginerrormsg)) {
    // We had some errors before redirect, show them now.
    $errormsg = $SESSION->loginerrormsg;
    unset($SESSION->loginerrormsg);

} else if ($testsession) {
    // No need to redirect here.
    unset($SESSION->loginerrormsg);

} else if ($errormsg or !empty($frm->password)) {
    // We must redirect after every password submission.
    if ($errormsg) {
        $SESSION->loginerrormsg = $errormsg;
    }
    redirect(new moodle_url('/login/index.php'));
}

$PAGE->set_title("$site->fullname: $loginsite");
$PAGE->set_heading("$site->fullname");

unset($SESSION->login_username);
unset($SESSION->login_remember);

$use_legacy_login_ui = !empty($CFG->enable_legacy_login);

if ($use_legacy_login_ui) {
    echo $OUTPUT->header();

    if (isloggedin() and !isguestuser()) {
        // prevent logging when already logged in, we do not want them to relogin by accident because sesskey would be changed
        echo $OUTPUT->box_start();
        $logout = new single_button(
            new moodle_url('/login/logout.php', ['sesskey' => sesskey(), 'loginpage' => 1]),
            get_string('logout'),
            'post'
        );
        $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
        echo $OUTPUT->confirm(get_string('alreadyloggedin', 'error', fullname($USER)), $logout, $continue);
        echo $OUTPUT->box_end();
    } else {
        $loginform = new \core_auth\output\login($authsequence, $frm);
        $loginform->set_error($errormsg);
        echo $OUTPUT->render($loginform);
    }

    echo $OUTPUT->footer();
} else {
    if (isloggedin() and !isguestuser()) {
        // if already logged in, redirect to homepage
        redirect(new moodle_url('/'));
    }
    $controller = new \core_auth\controllers\login();
    $controller->set_form_inputs($frm);
    $controller->set_error($errormsg);
    $controller->process();
}
