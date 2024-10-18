<?php
/**
 * Extend the HestiaCP Pluginable object with our ISPProtect object for
 * performing ISPProtect and BanDaemon operations.
 * 
 * @version 1.0.0
 * @license GPL-3.0
 * @link https://github.com/virtuosoft-dev/hcpp-ispprotect
 * 
 */

if ( ! class_exists( 'ISPProtect') ) {
    class ISPProtect {
        /**
         * Constructor, listen for the invoke, POST, and render events
         */
        public function __construct() {
            global $hcpp;
            $hcpp->ispprotect = $this;
        }

        // Setup Ghost with the given user options
        public function setup( $args ) {
            return $args;
        }
    }
    new ISPProtect();
}
