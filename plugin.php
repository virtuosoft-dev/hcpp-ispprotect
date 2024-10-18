<?php
/**
 * Plugin Name: ISPProtect
 * Plugin URI: https://github.com/virtuosoft-dev/hcpp-ispprotect
 * Description: ISPProtect is a plugin for HestiaCP that installs ISPProtect and prerequisites on your server.
 * Version: 1.0.0
 * Author: Stephen J. Carnam
 */

// Register the install and uninstall scripts
global $hcpp;
require_once( dirname(__FILE__) . '/ispprotect.php' );

$hcpp->register_install_script( dirname(__FILE__) . '/install' );
$hcpp->register_uninstall_script( dirname(__FILE__) . '/uninstall' );
