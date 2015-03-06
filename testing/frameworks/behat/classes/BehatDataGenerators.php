<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle Behat, 2013 David Monllaó
 *
 */

require_once(__DIR__ . '/BehatBase.php');

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Behat\Exception\PendingException as PendingException;

/**
 * Class to set up quickly a Given environment.
 *
 */
class BehatDataGenerators extends BehatBase {

    /**
     * @var testing_data_generator
     */
    protected $datagenerator;

    /**
     * Each element specifies:
     * - The data generator suffix used.
     * - The available fields array(fieldname=>fieldtype)
     * - The required fields.
     * - The mapping between other elements references and database field names.
     * @var array
     */
    protected static $elements = array(
        'users' => array(
            'datagenerator' => 'user',
            'available' => array(
                'username'     => 'text',
                'password'     => 'text',
                'email'        => 'text',
                'firstname'    => 'text',
                'lastname'     => 'text',
                'institution'  => 'text',
                'role'         => 'text',
                'authname'     => 'text',
                'remoteusername' => 'text',
            ),
            'required' => array('username', 'password', 'email', 'firstname', 'lastname')
        ),
        'groups' => array(
            'datagenerator' => 'group',
            'available' => array(
                'name'             => 'text',
                'owner'            => 'text',
                'description'      => 'text',
                'grouptype'        => 'text',
                'open'             => 'bool',
                'controlled'       => 'bool',
                'request'          => 'bool',
                'invitefriends'    => 'bool',
                'suggestfriends'   => 'bool',
                'editroles'        => 'text',
                'submittableto'    => 'bool',
                'allowarchives'    => 'bool',
                'editwindowstart'  => 'text',
                'editwindowend'    => 'text',
                'members'          => 'text',
                'staff'            => 'text',
                'admins'           => 'text',
            ),
            'required' => array('name', 'owner'),
        ),
        'institutions' => array(
            'datagenerator' => 'institution',
            'available' => array(
                'name'             => 'text',
                'displayname'      => 'text',
                'showonlineusers'  => 'number',
                'registerallowed'  => 'bool',
                'registerconfirm'  => 'bool',
                'lang'             => 'text',
                'theme'            => 'text',
                'dropdownmenu'     => 'bool',
                'skins'            => 'bool',
                'licensemandatory' => 'bool',
                'licensedefault'   => 'text',
                'defaultquota'     => 'number',
                'defaultmembershipperiod' => 'number',
                'maxuseraccounts'  => 'number',
                'expiry'           => 'text',
                'allowinstitutionpublicviews' => 'bool',
                'members'          => 'text',
                'staff'            => 'text',
                'admins'           => 'text',
            ),
            'required' => array('name', 'displayname')
        ),
        'pages' => array(
            'datagenerator' => 'view',
            'available' => array(
                'title'            => 'text',
                'description'      => 'text',
                'ownertype'        => 'text',
                'ownername'        => 'text',
                'layout'           => 'text',
            ),
            'required' => array('title', 'ownertype', 'ownername'),
        ),
        'group memberships' => array(
            'datagenerator' => 'group_membership',
            'required' => array('username', 'groupname', 'role')
        ),
        'institution memberships' => array(
            'datagenerator' => 'institution_membership',
            'required' => array('username', 'institutionname', 'role')
        ),
    );

    /**
     * Normalise values in a given record
     * For example, 'ON' -> 1, 'OFF' -> 0
     * @param array ('field' => 'values', ...) $record
     * @return $record
     */
    public function normalise(&$record) {
        foreach ($record as &$value) {
            $value = trim($value);
            // Normalise boolean values
            if (strtolower($value) == 'on' || $value == '1') {
                $value = true;
            }
            else if (strtolower($value) == 'off' || $value == '0') {
                $value = false;
            }
        }
    }

    /**
     * Validate field values in a given record
     *
     * @param array ('fieldname' => 'fieldtype', ...) $availablefields
     * @param array ('fieldname' => 'values', ...) $record
     * @return void
     * @throws MaharaBehatTestException
     */
    public function validate_fields($availablefields, $record) {
        foreach ($record as $fieldname => $fieldvalue) {
            if (!in_array($fieldname, array_keys($availablefields))) {
                throw new MaharaBehatTestException("The field '" . $fieldname . "' is not available.\n".
                                "All available fields are " . implode(',', array_keys($availablefields)));
            }
            if ($availablefields[$fieldname] == 'bool' && !is_bool($fieldvalue)) {
                throw new MaharaBehatTestException("The value '" . $fieldvalue . "' of the field '" . $fieldname . "' must be a boolean ('ON'|'OFF', '1'|'0' are accepted boolean values).");
            }
            if ($availablefields[$fieldname] == 'number' && !is_numeric($fieldvalue)) {
                throw new MaharaBehatTestException("The value '" . $fieldvalue . "' of the field '" . $fieldname . "' must be a number.");
            }
        }
    }

    /**
     * Creates the specified element.
     *
     * @Given /^the following "(?P<element_string>(?:[^"]|\\")*)" exist:$/
     *
     * @throws MaharaBehatTestException
     * @throws PendingException
     * @param string    $elementname The name of the entity to add
     * @param TableNode $data
     */
    public function the_following_exist($elementname, TableNode $data) {

        // Now that we need them require the data generators.
        require_once(get_config('docroot') . '/testing/classes/generator/lib.php');

        if (empty(self::$elements[$elementname])) {
            throw new PendingException($elementname . ' data generator is not implemented');
        }

        $this->datagenerator = TestingUtil::get_data_generator();

        $elementdatagenerator = self::$elements[$elementname]['datagenerator'];
        $availablefields = self::$elements[$elementname]['available'];
        $requiredfields = self::$elements[$elementname]['required'];
        if (!empty(self::$elements[$elementname]['switchids'])) {
            $switchids = self::$elements[$elementname]['switchids'];
        }

        foreach ($data->getHash() as $elementdata) {

            // Normalise field values
            $this->normalise($elementdata);
            // Validate available fields for given element
            $this->validate_fields($availablefields, $elementdata);
            // Check if all the required fields are there.
            foreach ($requiredfields as $requiredfield) {
                if (!isset($elementdata[$requiredfield])) {
                    throw new MaharaBehatTestException($elementname . ' requires the field ' . $requiredfield . ' to be specified');
                }
            }

            // Preprocess the entities that requires a special treatment.
            if (method_exists($this, 'preprocess_' . $elementdatagenerator)) {
                $elementdata = $this->{'preprocess_' . $elementdatagenerator}($elementdata);
            }

            // Creates element.
            $methodname = 'create_' . $elementdatagenerator;
            if (method_exists($this->datagenerator, $methodname)) {
                // Using data generators directly.
                $this->datagenerator->{$methodname}($elementdata);
            }
            else if (method_exists($this, 'process_' . $elementdatagenerator)) {
                // Using an alternative to the direct data generator call.
                $this->{'process_' . $elementdatagenerator}($elementdata);
            }
            else {
                throw new PendingException($elementname . ' the create_ or process_ method is not implemented');
            }
        }

    }

}
