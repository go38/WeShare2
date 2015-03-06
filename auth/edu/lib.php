<?php

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');

class AuthEdu extends Auth {

    private $token = null;

    public function __construct($id = null) {
        $this->type = 'edu';
        $this->has_instance_config = true;

        $this->config['host_url'] = '';

        if (!empty($id)) {
            return $this->init($id);
        }
        return true;
    }

    public function init($id) {
        $this->ready = parent::init($id);

        // Check that required fields are set
        if (empty($this->config['host_url'])) {
            $this->ready = false;
        }

        libxml_disable_entity_loader(false);

        return true;
    }

    /**
     * Attempt to authenticate user
     *
     * @param object $user     As returned from the usr table
     * @param string $password The password being used for authentication
     * @return bool            True/False based on whether the user
     *                         authenticated successfully
     * @throws AuthUnknownUserException If the user does not exist
     */
    public function authenticate_user_account($user, $password) {
        $this->must_be_ready();
        $username = $user->username;

        if (isset($user->tokenId)) {
            $this->token = $user->tokenId;
            $client = new SoapClient('http://sso.cloud.edu.tw/ORG/service/SSOServiceX?wsdl');
            $response = $client->__soapCall('validToken1',array(
                array(
                    'TokenId' => $this->token,
                    'UserIP' => self::get_ip()
                )
            ));
            if ($response->return->ActXML->StatusCode == 200 && $response->return->ActXML->RsInfo == 1) {
                return true;
            }
        }

        // empty user or password is not allowed.
        if (empty($username) || empty($password)) {
            return false;
        }

        $client = new SoapClient($this->config['host_url']);

        $response = $client->__soapCall('getToken1', array(
            array(
                'UserId'   => $username,
                'Password' => $password,
                'UserIP'   => self::get_ip(),
            )
        ));

        if ($response->return->ActXML->StatusCode == 200 && !empty($response->return->ActXML->RsInfo->TokenId)) {
            $this->token = $response->return->ActXML->RsInfo->TokenId;
            return true;
        }

        return false;
    }

    private static function get_ip() {/*{{{*/
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }/*}}}*/

    public function can_auto_create_users() {
        return true;
    }

    public function get_user_info($username) {
        $client = new SoapClient($this->config['host_url']);

        $response = $client->__soapCall("getToken2", array(
            array(
                'TokenId' => $this->token,
                'UserIP' => self::get_ip(),
            )
        ));

        $user = new stdClass;
        $user->firstname = mb_substr($response->return->ActXML->RsInfo->User->CName, 1, NULL, 'UTF-8');
        $user->lastname  = mb_substr($response->return->ActXML->RsInfo->User->CName, 0, 1,    'UTF-8');
        $user->email     = $response->return->ActXML->RsInfo->User->UserEmail;
        return $user;
    }

    /**
     * Any old password is valid
     *
     * @param string $password The password to check
     * @return bool            True, always
     */
    public function is_password_valid($password) {
        return true;
    }
}

/**
 * Plugin configuration class
 */
class PluginAuthEdu extends PluginAuth {

    private static $default_config = array(
        'host_url' => '',
    );

    public static function has_config() {
        return false;
    }

    public static function get_config_options() {
        return array();
    }

    public static function has_instance_config() {
        return true;
    }

    public static function is_usable() {
        if (isset($_GET['token1'])) {
            global $USER;

            $authinstance = get_record('auth_instance', 'institution', 'mahara', 'authname', 'edu');
            $instances[]= $authinstance->id;
            foreach ($instances as $authinstanceid) {
                libxml_disable_entity_loader(false);
                $client = new SoapClient('http://sso.cloud.edu.tw/ORG/service/SSOServiceX?wsdl');
                $tokenId = $_GET['token1'];
                $response = $client->__soapCall('getToken2',array(
                    array(
                        'TokenId' => $tokenId,
                        'UserIP' => self::get_ip()
                    )
                ));
                if ($response->return->ActXML->StatusCode == 200 && !empty($response->return->ActXML->RsInfo->User->UserAccount)) {
                    // do the normal user lookup
                    $sql = 'SELECT
                                *,
                                ' . db_format_tsfield('expiry') . ',
                                ' . db_format_tsfield('lastlogin') . ',
                                ' . db_format_tsfield('lastlastlogin') . ',
                                ' . db_format_tsfield('lastaccess') . ',
                                ' . db_format_tsfield('suspendedctime') . ',
                                ' . db_format_tsfield('ctime') . '
                            FROM
                                {usr}
                            WHERE
                                LOWER(username) = ?';
                    $user = get_record_sql($sql, array(strtolower($response->return->ActXML->RsInfo->User->UserAccount)));

                    $auth = AuthFactory::create($authinstanceid);
                    if ($user) {
                        $user->tokenId = $tokenId;
                        $auth->authenticate_user_account($user, '');
                        $USER->reanimate($user->id, $auth->instanceid);
                    } else {
                        $user = new stdClass();
                        $user->username = $response->return->ActXML->RsInfo->User->UserAccount;
                        $user->tokenId = $tokenId;
                        if ($auth->authenticate_user_account($user, '')) {
                            $USER->username = $user->username;

                            $USER->authinstance = $authinstance->id;
                            $userdata = $auth->get_user_info($user->username);

                            if (empty($userdata)) {
                                throw new AuthUnknownUserException("\"$username\" is not known");
                            }

                            // We have the data - create the user
                            $USER->lastlogin = db_format_timestamp(time());
                            if (isset($userdata->firstname)) {
                                $USER->firstname = sanitize_firstname($userdata->firstname);
                            }
                            if (isset($userdata->lastname)) {
                                $USER->lastname = sanitize_firstname($userdata->lastname);
                            }
                            if (isset($userdata->email)) {
                                $USER->email = sanitize_email($userdata->email);
                            }
                            else {
                                // The user will be asked to populate this when they log in.
                                $USER->email = null;
                            }

                            $profilefields = array();
                            foreach (array('studentid', 'preferredname') as $pf) {
                                if (isset($userdata->$pf)) {
                                    $sanitize = 'sanitize_' . $pf;
                                    if (($USER->$pf = $sanitize($userdata->$pf)) !== '') {
                                        $profilefields[$pf] = $USER->$pf;
                                    }
                                }
                            }
                        }

                        $remoteauth = $auth->is_parent_authority();
                        create_user($USER, $profilefields, null, $remoteauth);
                        $USER->reanimate($USER->id, $authinstance->id);
                    }
                }
            }
        }

        // would be good to be able to detect SimpleSAMLPHP libraries
        return true;
    }

    private static function get_ip() {/*{{{*/
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    	return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    	return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }/*}}}*/

    public static function get_instance_config_options($institution, $instance = 0) {

        if ($instance > 0) {
            $current_config = get_records_menu('auth_instance_config', 'instance', $instance, '', 'field, value');

            if ($current_config == false) {
                $current_config = array();
            }

            foreach (self::$default_config as $key => $value) {
                if (array_key_exists($key, $current_config)) {
                    self::$default_config[$key] = $current_config[$key];
                }
            }
        }

        $elements = array(
            'instance' => array(
                'type'  => 'hidden',
                'value' => $instance,
            ),
            'institution' => array(
                'type'  => 'hidden',
                'value' => $institution,
            ),
            'authname' => array(
                'type'  => 'hidden',
                'value' => 'edu',
            ),
            'instancename' => array(
                'type'  => 'hidden',
                'value' => 'Edu',
            ),
            'host_url' => array(
                'type' => 'text',
                'title' => get_string('hosturl', 'auth.edu'),
                'rules' => array(
                    'required' => true
                ),
                'defaultvalue' => self::$default_config['host_url']
            )
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function save_instance_config_options($values, $form) {

        $authinstance = new stdClass();

        if ($values['instance'] > 0) {
            $values['create'] = false;
            $current = get_records_assoc('auth_instance_config', 'instance', $values['instance'], '', 'field, value');
            $authinstance->id = $values['instance'];
        }
        else {
            $values['create'] = true;
            $lastinstance = get_records_array('auth_instance', 'institution', $values['institution'], 'priority DESC', '*', '0', '1');

            if ($lastinstance == false) {
                $authinstance->priority = 0;
            }
            else {
                $authinstance->priority = $lastinstance[0]->priority + 1;
            }
        }

        $authinstance->institution  = $values['institution'];
        $authinstance->authname     = $values['authname'];
        $authinstance->instancename = $values['instancename'];

        if ($values['create']) {
            $values['instance'] = insert_record('auth_instance', $authinstance, 'id', true);
        }
        else {
            update_record('auth_instance', $authinstance, array('id' => $values['instance']));
        }

        if (empty($current)) {
            $current = array();
        }

        self::$default_config = array('host_url' => $values['host_url']);

        foreach(self::$default_config as $field => $value) {
            $record = new stdClass();
            $record->instance = $values['instance'];
            $record->field = $field;
            $record->value = $value;

            if ($values['create'] || !array_key_exists($field, $current)) {
                insert_record('auth_instance_config', $record);
            }
            else {
                update_record('auth_instance_config', $record, array('instance' => $values['instance'], 'field' => $field));
            }
        }

        return $values;
    }
}
