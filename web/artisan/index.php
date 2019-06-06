<?php
session_start();

define('VESTA_CMD', '/usr/bin/sudo /usr/local/vesta/bin/');

if (isset($_GET['rkey']) && ($rkey = $_GET['rkey'])) {
    exec (VESTA_CMD . "v-list-user ".$rkey." json", $output, $return_var);

    if ($data = current(json_decode(implode('', $output), true))) {
        if (isset($data['RKEY'])) {
            echo $data['RKEY'];
        }
    }
}
if (isset($_GET['user']) && isset($_GET['token']) && ($v_user = $_GET['user']) && ($hash = $_GET['token'])) {
    exec (VESTA_CMD . "v-list-user ". $v_user ." json", $output, $return_var);
    if (($data = json_decode(implode('', $output), true)) && isset($data[$v_user]['RKEY'])) {
        if ($hash === hash('sha256', $data[$v_user]['RKEY'])) {
            // Update RKEY
            exec (VESTA_CMD . "v-change-user-rkey ". $v_user ." json", $output, $return_var);

            // Define session user
            $_SESSION['user'] = key($data);

            // Define language
            $output = '';
            exec (VESTA_CMD."v-list-sys-languages json", $output, $return_var);
            $languages = json_decode(implode('', $output), true);
            if (in_array($data[$v_user]['LANGUAGE'], $languages)){
                $_SESSION['language'] = $data[$v_user]['LANGUAGE'];
            } else {
                $_SESSION['language'] = 'en';
            }
            
            // Check system configuration
            $output = '';
            exec (VESTA_CMD . "v-list-sys-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            $sys_arr = $data['config'];
            foreach ($sys_arr as $key => $value) {
                $_SESSION[$key] = $value;
            }

            // Generate CSRF token
            $_SESSION['token'] = md5(uniqid(mt_rand(), true));

            // Regenerate session id to prevent session fixation
            session_regenerate_id();

            // Location main page
            header('Location: /list/user/');
        }
    }
}
