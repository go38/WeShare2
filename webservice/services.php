<?php

/**
 * Core external functions and service definitions.
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  2009 Petr Skoda (http://skodak.org)
 *
 */

/*
 * The function descriptions for the mahara_user_* functions
 */
$functions = array(

    // === user related functions ===

    'mahara_user_create_users' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'create_users',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Create users',
        'type'        => 'write',
    ),

    'mahara_user_update_users' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'update_users',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Update users',
        'type'        => 'write',
    ),

    'mahara_user_get_users' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'get_users',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get all users',
        'type'        => 'read',
    ),

    'mahara_user_get_users_by_id' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'get_users_by_id',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get users by id.',
        'type'        => 'read',
    ),

    'mahara_user_get_my_user' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'get_my_user',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get the current user details',
        'type'        => 'read',
    ),

    'mahara_user_get_online_users' => array(
            'classname'   => 'mahara_user_external',
            'methodname'  => 'get_online_users',
            'classpath'   => WEBSERVICE_DIRECTORY,
            'description' => 'Get the current list of online users',
            'type'        => 'read',
    ),

    'mahara_user_get_context' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'get_context',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get the institution context of the authenticated user',
        'type'        => 'read',
    ),

    'mahara_user_get_extended_context' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'get_extended_context',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get the extended context of the authenticated user',
        'type'        => 'read',
    ),

    'mahara_user_delete_users' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'delete_users',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Delete users by id.',
        'type'        => 'write',
    ),

    'mahara_user_get_favourites' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'get_favourites',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get favourites for a user',
        'type'        => 'read',
    ),

    'mahara_user_get_all_favourites' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'get_all_favourites',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get all user favourites',
        'type'        => 'read',
    ),

    'mahara_user_update_favourites' => array(
        'classname'   => 'mahara_user_external',
        'methodname'  => 'update_favourites',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Update user favourites',
        'type'        => 'write',
    ),

    // === group related functions ===

    'mahara_group_create_groups' => array(
		'classname'   => 'mahara_group_external',
        'methodname'  => 'create_groups',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Create groups',
        'type'        => 'write',
    ),

    'mahara_group_update_groups' => array(
        'classname'   => 'mahara_group_external',
        'methodname'  => 'update_groups',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Update groups',
        'type'        => 'write',
    ),

    'mahara_group_update_group_members' => array(
        'classname'   => 'mahara_group_external',
        'methodname'  => 'update_group_members',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Update group members',
        'type'        => 'write',
    ),

    'mahara_group_get_groups' => array(
        'classname'   => 'mahara_group_external',
        'methodname'  => 'get_groups',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get all groups',
        'type'        => 'read',
    ),

    'mahara_group_get_groups_by_id' => array(
        'classname'   => 'mahara_group_external',
        'methodname'  => 'get_groups_by_id',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Get groups by id.',
        'type'        => 'read',
    ),

    'mahara_group_delete_groups' => array(
        'classname'   => 'mahara_group_external',
        'methodname'  => 'delete_groups',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'Delete groups by id.',
        'type'        => 'write',
    ),

    // === institution related functions ===

    'mahara_institution_add_members' => array(
		'classname'   => 'mahara_institution_external',
        'methodname'  => 'add_members',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'add members to institutions',
        'type'        => 'write',
    ),

    'mahara_institution_remove_members' => array(
        'classname'   => 'mahara_institution_external',
        'methodname'  => 'remove_members',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'remove members from institutions',
        'type'        => 'write',
    ),

    'mahara_institution_invite_members' => array(
        'classname'   => 'mahara_institution_external',
        'methodname'  => 'invite_members',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'invite members to institutions',
        'type'        => 'write',
    ),

    'mahara_institution_decline_members' => array(
        'classname'   => 'mahara_institution_external',
        'methodname'  => 'decline_members',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'decline request for institiution membership',
        'type'        => 'write',
    ),

    'mahara_institution_get_members' => array(
        'classname'   => 'mahara_institution_external',
        'methodname'  => 'get_members',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'decline request for institiution membership',
        'type'        => 'read',
    ),

    'mahara_institution_get_requests' => array(
        'classname'   => 'mahara_institution_external',
        'methodname'  => 'get_requests',
        'classpath'   => WEBSERVICE_DIRECTORY,
        'description' => 'decline request for institiution membership',
        'type'        => 'read',
    ),
);

/**
* Prepopulated service groups that propose units of access
*/
$services = array(
    'User Provisioning' => array(
            'functions' => array ('mahara_user_get_online_users', 'mahara_user_get_all_favourites', 'mahara_user_get_favourites', 'mahara_user_update_favourites', 'mahara_user_get_users', 'mahara_user_get_users_by_id', 'mahara_user_create_users', 'mahara_user_delete_users', 'mahara_user_update_users', 'mahara_user_get_context', 'mahara_user_get_extended_context'),
            'enabled'=>1,
    ),
    'User Query' => array(
            'functions' => array ('mahara_user_get_online_users', 'mahara_user_get_all_favourites', 'mahara_user_get_favourites', 'mahara_user_get_users', 'mahara_user_get_users_by_id', 'mahara_user_get_context', 'mahara_user_get_extended_context'),
            'enabled'=>1,
    ),
    'Simple User Provisioning' => array(
            'functions' => array ('mahara_user_get_online_users', 'mahara_user_get_all_favourites', 'mahara_user_get_favourites', 'mahara_user_update_favourites', 'mahara_user_get_users', 'mahara_user_get_users_by_id', 'mahara_user_create_users', 'mahara_user_delete_users', 'mahara_user_update_users', 'mahara_user_get_context', 'mahara_user_get_extended_context'),
            'enabled'=>1,
            'restrictedusers'=>1,
    ),
    'Simple User Query' => array(
            'functions' => array ('mahara_user_get_online_users', 'mahara_user_get_all_favourites', 'mahara_user_get_favourites', 'mahara_user_get_users', 'mahara_user_get_users_by_id', 'mahara_user_get_context', 'mahara_user_get_extended_context'),
            'enabled'=>1,
            'restrictedusers'=>1,
    ),
    'UserToken User Query' => array(
            'functions' => array ('mahara_user_get_my_user', 'mahara_user_get_context', 'mahara_user_get_extended_context'),
            'enabled'=>1,
            'tokenusers'=>1,
    ),
    'Group Provisioning' => array(
            'functions' => array ('mahara_group_get_groups', 'mahara_group_get_groups_by_id', 'mahara_group_create_groups', 'mahara_group_delete_groups', 'mahara_group_update_groups', 'mahara_group_update_group_members'),
            'enabled'=>1,
    ),
    'Group Query' => array(
            'functions' => array ('mahara_group_get_groups', 'mahara_group_get_groups_by_id'),
            'enabled'=>1,
    ),
    'Simple Group Provisioning' => array(
            'functions' => array ('mahara_group_get_groups', 'mahara_group_get_groups_by_id', 'mahara_group_create_groups', 'mahara_group_delete_groups', 'mahara_group_update_groups', 'mahara_group_update_group_members'),
            'enabled'=>1,
            'restrictedusers'=>1,
    ),
    'Simple Group Query' => array(
            'functions' => array ('mahara_group_get_groups', 'mahara_group_get_groups_by_id'),
            'enabled'=>1,
            'restrictedusers'=>1,
    ),
    'Institution Provisioning' => array(
            'functions' => array ('mahara_institution_add_members', 'mahara_institution_remove_members', 'mahara_institution_invite_members', 'mahara_institution_decline_members',),
            'enabled'=>1,
    ),
    'Institution Query' => array(
            'functions' => array ('mahara_institution_get_members', 'mahara_institution_get_requests'),
            'enabled'=>1,
    ),
    'Simple Institution Provisioning' => array(
            'functions' => array ('mahara_institution_add_members', 'mahara_institution_remove_members', 'mahara_institution_invite_members', 'mahara_institution_decline_members',),
            'enabled'=>1,
            'restrictedusers'=>1,
    ),
    'Simple Institution Query' => array(
            'functions' => array ('mahara_institution_get_members', 'mahara_institution_get_requests'),
            'enabled'=>1,
            'restrictedusers'=>1,
    ),
);


