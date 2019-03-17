<?php
/**
 * Teampass - a collaborative passwords manager.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package   Teampass
 * @author    Nils Laumaillé <nils@teamapss.net>
 * @copyright 2009-2019 Teampass.net
 * @license   https://spdx.org/licenses/GPL-3.0-only.html#licenseText GPL-3.0
 * @version   GIT: <git_id>
 * @link      https://www.teampass.net
 */

// Automatic redirection
$nextUrl = '';
if (strpos($server_request_uri, '?') > 0) {
    $nextUrl = filter_var(
        substr($server_request_uri, strpos($server_request_uri, '?')),
        FILTER_SANITIZE_URL
    );
}

$post_sig_response = filter_input(INPUT_POST, 'sig_response', FILTER_SANITIZE_STRING);
$post_duo_login = filter_input(INPUT_POST, 'duo_login', FILTER_SANITIZE_STRING);
$post_duo_pwd = filter_input(INPUT_POST, 'duo_pwd', FILTER_SANITIZE_STRING);
$post_duo_data = filter_input(INPUT_POST, 'duo_data', FILTER_SANITIZE_STRING);
$post_login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
$post_pw = filter_input(INPUT_POST, 'pw', FILTER_SANITIZE_STRING);

echo '
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">',
    (isset($SETTINGS['custom_logo']) === true
  && empty($SETTINGS['custom_logo']) === false) ?
  '
    <img src="'.(string) $SETTINGS['custom_logo'].'" alt="" style="margin-bottom:40px;" />' :
  '',
    '
    <a href="../../index2.html"><b>'.TP_TOOL_NAME.'</b></a>
  </div>
  <!-- /.login-logo -->
<div class="card">
    <div class="card-body login-card-body">
        <p class="login-box-msg">'.langHdl('index_get_identified').'</p>

        <div>
            <div class="input-group has-feedback mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-user fa-fw"></i></span>
                </div>';

if (isset($SETTINGS['enable_http_request_login']) === true
    && $SETTINGS['enable_http_request_login'] === '1'
    && isset($_SERVER['PHP_AUTH_USER']) === true
    && !(isset($SETTINGS['maintenance_mode']) === true
    && $SETTINGS['maintenance_mode'] === '1')
) {
    if (strpos($_SERVER['PHP_AUTH_USER'], '@') !== false) {
        $username = explode('@', $_SERVER['PHP_AUTH_USER'])[0];
    } elseif (strpos($_SERVER['PHP_AUTH_USER'], '\\') !== false) {
        $username = explode('\\', $_SERVER['PHP_AUTH_USER'])[1];
    } else {
        $username = $_SERVER['PHP_AUTH_USER'];
    }
    echo '
          <input type="text" id="login" class="form-control" placeholder="', filter_var($username, FILTER_SANITIZE_STRING), '" readonly>';
} else {
    echo '
          <input type="text" id="login" class="form-control" placeholder="'.langHdl('index_login').'">';
}

echo '
        </div>';
if (!(isset($SETTINGS['enable_http_request_login']) === true
    && $SETTINGS['enable_http_request_login'] === '1'
    && isset($_SERVER['PHP_AUTH_USER']) === true
    && !(isset($SETTINGS['maintenance_mode']) === true
    && $SETTINGS['maintenance_mode'] === '1'))
) {
    echo '
        <div class="input-group has-feedback mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
            </div>
          <input type="password" id="pw" class="form-control submit-button" placeholder="'.langHdl('index_password').'">
        </div>';
}

echo '
        <div class="input-group has-feedback mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-clock fa-fw"></i></span>
            </div>
          <input type="text" id="session_duration" class="form-control submit-button" placeholder="'.langHdl('index_session_duration').'&nbsp;('.langHdl('minutes').')" value="', isset($SETTINGS['default_session_expiration_time']) === true ? $SETTINGS['default_session_expiration_time'] : '', '">
        </div>';

    // 2FA auth selector
    echo '
        <input type="hidden" id="2fa_agses" value="', isset($SETTINGS['agses_authentication_enabled']) === true && $SETTINGS['agses_authentication_enabled'] === '1' ? '1' : '0', '" />
        <input type="hidden" id="2fa_duo" value="', isset($SETTINGS['duo']) === true && $SETTINGS['duo'] === '1' ? '1' : '0', '" />
        <input type="hidden" id="2fa_google" value="', isset($SETTINGS['google_authentication']) === true && $SETTINGS['google_authentication'] === '1' ? '1' : '0', '" />
        <input type="hidden" id="2fa_yubico" value="', isset($SETTINGS['yubico_authentication']) === true && $SETTINGS['yubico_authentication'] === '1' ? '1' : '0', '" />
        <input type="hidden" id="2fa_user_selection" value="',
            (isset($_GET['post_type']) === true && $_GET['post_type'] === 'duo' ? 'duo' : '')
        , '" />
        <input type="hidden" id="duo_sig_response" value="', null !== $post_sig_response ? $post_sig_response : '', '" />
        <div class="row mb-3" id="2fa_methods_selector">
          <div class="col-12">
            <h8 class="login-box-msg">'.langHdl('2fa_authentication_selector').'</h8>
            <div class="2fa-methods" style="padding:3px; text-align:center;">
            ', isset($SETTINGS['google_authentication']) === true && $SETTINGS['google_authentication'] === '1' ?
                '<label for="select2fa-google">Google</label>
                <input type="radio" class="2fa_selector_select" name="2fa_selector_select" id="select2fa-google" data-mfa="google">' : '', '
                ', isset($SETTINGS['agses_authentication_enabled']) === true && $SETTINGS['agses_authentication_enabled'] === '1' ?
                '<label for="select2fa-agses">Agses</label>
                <input type="radio" class="2fa_selector_select" name="2fa_selector_select" id="select2fa-agses" data-mfa="agses">' : '', '
                ', isset($SETTINGS['duo']) === true && $SETTINGS['duo'] === '1' ?
                '<label for="select2fa-duo">Duo Security</label>
                <input type="radio" class="2fa_selector_select" name="2fa_selector_select" id="select2fa-duo" data-mfa="duo">' : '', '
                ', isset($SETTINGS['yubico_authentication']) === true && $SETTINGS['yubico_authentication'] === '1' ?
                '<label for="select2fa-yubico">Yubico</label>
                <input type="radio" class="2fa_selector_select" name="2fa_selector_select" id="select2fa-yubico" data-mfa="yubico">' : '', '
            </div>
          </div>
        </div>';

// AGSES
if (isset($SETTINGS['agses_authentication_enabled']) === true && $SETTINGS['agses_authentication_enabled'] === '1') {
    echo '
        <div id="div-2fa-agses" class="row mb-3 div-2fa-method ', isset($_SESSION['2famethod-agses']) === true && $_SESSION['2famethod-agses'] === '1' ? '' : 'hidden', '">
            <div id="agses_cardid_div" style="text-align:center; padding:5px; width:454px; margin:5px 0 5px;" class="ui-state-active ui-corner-all">
                '.langHdl('user_profile_agses_card_id').': &nbsp;
                <input type="text" size="12" id="agses_cardid">
            </div>
            <div id="agses_flickercode_div" style="text-align:center; display:none;">
                <canvas id="axs_canvas"></canvas>
            </div>
            <input type="text" id="agses_code" name="agses_code" style="margin-top:15px;" class="input_text text ui-widget-content ui-corner-all hidden submit-button" placeholder="'.langHdl('index_agses_key').'" />
        </div>';
}

// DUO box
if (isset($SETTINGS['duo']) === true && $SETTINGS['duo'] === '1') {
    echo '
        <div id="div-2fa-duo" class="row mb-3 div-2fa-method hidden">
            <div id="div-2fa-duo-progress" class="text-center hidden"></div>
            <form method="post" id="duo_form" action="">
                <input type="hidden" id="duo_login" name="duo_login" value="', null !== $post_duo_login ? $post_duo_login : '', '" />
                <input type="hidden" id="duo_pwd" name="duo_pwd" value="', null !== $post_duo_pwd ? $post_duo_pwd : '', '" />
                <input type="hidden" id="duo_data" name="duo_data" value="', null !== $post_duo_data ? $post_duo_data : '', '" />
            </form>
        </div>';
}

// Google Authenticator code
if (isset($SETTINGS['google_authentication']) === true && $SETTINGS['google_authentication'] === '1') {
    echo '
        <div id="div-2fa-google" class="mb-3 div-2fa-method', isset($_SESSION['2famethod-google']) === true && $_SESSION['2famethod-google'] === '1' ? '' : ' hidden', '">
            <div class="row">
                <div class="col-3">
                    <img src="includes/images/2fa_google_auth.png">
                </div>
                <div class="col-8">
                    <input type="text" id="ga_code" class="form-control submit-button" placeholder="'.langHdl('ga_identification_code').'" />
                </div>
                <div class="col-1">
                    <span class="fa fa-envelope-o form-control-feedback pointer infotip" title="'.langHdl('i_need_to_generate_new_ga_code').'" onclick="send_user_new_temporary_ga_code()"></span>
                </div>
            </div>
            <div id="div-2fa-google-qr" class="row mt-2 "></div>
        </div>';
}

if (isset($SETTINGS['enable_http_request_login']) === true
    && $SETTINGS['enable_http_request_login'] === '1'
    && isset($_SERVER['PHP_AUTH_USER']) === true
    && (isset($SETTINGS['maintenance_mode']) === false
    && $SETTINGS['maintenance_mode'] === '1')
) {
    echo '
<script>
var seconds = 1;
function updateLogonButton(timeToGo){
document.getElementById("but_identify_user").value = "'.langHdl('duration_login_attempt').' " + timeToGo;
}
$( window ).on( "load", function() {
updateLogonButton(seconds);
setInterval(function() {
  seconds--;
  if (seconds >= 0) {
      updateLogonButton(seconds);
  } else if(seconds === 0) {
      launchIdentify("", "'.$nextUrl.'");
  }
  updateLogonButton(seconds);
},
1000
);
});
</script>';
}

// Yubico authentication
if (isset($SETTINGS['yubico_authentication']) === true && $SETTINGS['yubico_authentication'] === '1') {
    echo '
        <div id="div-2fa-yubico" class="row mb-3 div-2fa-method ', isset($_SESSION['2famethod-yubico']) === true && $_SESSION['2famethod-yubico'] === '1' ? '' : 'hidden', '">
            <div class="col-3">
                <img src="includes/images/yubico.png">
            </div>

            <div class="col-8">
                <div id="yubiko-new-key" class="alert alert-info hidden">
                    <p>
                        <input type="text" size="10" id="yubico_user_id" class="form-control" placeholder="'.langHdl('yubico_user_id').'">
                    </p>
                    <p>
                    <input type="text" size="10" id="yubico_user_key" class="form-control" placeholder="'.langHdl('yubico_user_key').'">
                    </p>
                </div>
                <input autocomplete="off" type="text" id="yubico_key" class="form-control submit-button" placeholder="'.langHdl('press_your_yubico_key').'">
                <div class="row">
                    <span class="ml-2 mt-1 font-weight-light small pointer" id="register-yubiko-key">'.langHdl('register_new_yubiko_key').'</span>
                </div>
            </div>
        </div>';
}

echo '
        <div class="row mb-3 mt-5">
            <div class="col-12">
                <button id="but_identify_user" class="btn btn-primary btn-block">'.langHdl('log_in').'</button>
            </div>
        </div>
    </div>';

/*
// No forgot link any more - not secure - must go through Admin or Managers
// Forgot link
if (isset($SETTINGS['disable_show_forgot_pwd_link']) === true) {
    echo '
        <div class="row mb-1">
            <a href="#" id="link_forgot_user_pwd">'.langHdl('forgot_my_pw').'</a>
        </div>';
}
*/

echo '
    </div>
    <!-- /.login-card-body -->

    <div class="card-body confirm-password-card-body hidden">
        <h5 class="login-box-msg">'.langHdl('new_password_required').'</h5>

        <div class="alert alert-info">
            <div class="text-center"><i class="icon fa fa-info"></i>'.langHdl('password_strength').'
            <span id="confirm-password-level" class="ml-2 font-weight-bold"></span></div>
        </div>

        <div>
            <div id="current-user-password-div" class="hidden">
                <div class="input-group has-feedback mb-2">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-key"></i></span>
                    </div>
                    <input type="password" id="current-user-password" class="form-control" placeholder="'.langHdl('current_password').'">
                </div>
            </div>
            <div class="input-group has-feedback mb-2 mt-4">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                </div>
                <input type="password" id="new-user-password" class="form-control" placeholder="'.langHdl('index_new_pw').'">
            </div>
            <div class="input-group has-feedback mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                </div>
                <input type="password" id="new-user-password-confirm" class="form-control" placeholder="'.langHdl('index_change_pw_confirmation').'">
            </div>
            <div class="row mb-3">
                <div class="col-md-12 offset-sm-4 text-center">
                    <input type="hidden" id="new-user-password-complexity-level" />
                    <div id="new-user-password-strength"></div>
                </div>
                <!-- /.col -->
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <button id="but_confirm_new_password" class="btn btn-primary btn-block">'.langHdl('confirm').'</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body hidden" id="card-user-treat-psk">
        <div class="alert alert-info">
            <div class="text-center"><i class="icon fa fa-info"></i>'.langHdl('user_has_psk_info').'</div>
        </div>
        <div class="input-group has-feedback mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-key"></i></span>
            </div>
            <input type="password" id="user-old-defuse-psk" class="form-control" placeholder="'.langHdl('home_personal_saltkey').'">
        </div>
        <div class="row mb-3 mt-4">
            <div class="col-12">
                <button id="but_confirm_defuse_psk" class="btn btn-primary btn-block">'.langHdl('launch').'</button>
            </div>
            <div class="col-12 mt-3">
                <button id="but_confirm_forgot_defuse_psk" class="btn btn-danger btn-block text-bold">'.langHdl('i_cannot_remember').'</button>
            </div>
        </div>
    </div>

</div>
<!-- /.login-box -->';
