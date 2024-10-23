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
            $hcpp->add_action( 'priv_update_sys_rrd', [ $this, 'priv_update_sys_rrd' ] );
            $hcpp->ispprotect = $this;
        }

        /**
         * Process one ispp_scan.json request every five minutes; if found.
         */
        public function priv_update_sys_rrd( $args ) {

            // Exit if ispp_scan is already in memory
            $result = trim( shell_exec( 'ps -ax | grep [i]spp_scan' ) );
            if ( $result != '' ) return $args;

            // Get list of users
            global $hcpp;
            $users = $hcpp->run( 'list-users json' );
            $private_folders = [];
            foreach( $users as $username => $userInfo ) {
                if ( isset( $userInfo['SUSPENDED']) && $userInfo['SUSPENDED'] == "no" ) {
                    $domains = $hcpp->run("list-web-domains $username json");

                    // Get list of domains
                    foreach( $domains as $domain => $domainInfo ) {
                        if ( isset( $domainInfo['SUSPENDED'] ) && $domainInfo['SUSPENDED'] == "no" ) {
                            $private_folders[] = "/home/$username/web/$domain/private";
                        }
                    }
                }
            }

            // Find first ispp_scan*.json file from a private folder.
            $ispp_json_file = '';
            foreach( $private_folders as $folder ) {
                $files = glob( "$folder/ispp_scan*.json" );
                if ( $files !== false && !empty( $files ) ) {
                    $ispp_json_file = $files[0];
                    break;
                }
            }
            if ( $ispp_json_file == '' ) return $args;

            // Read the scan request file for parameters
            $ispp_params = json_decode( file_get_contents( $ispp_json_file ), true );
            $allowed_params = ['path','allow-single','no-malware-scan','no-version-scan',
                'no-plugin-version-scan','no-dereference','no-symlinks','results-dir',
                'email-results','email-results-sender','email-empty-results',
                'email-hostname','use-smtp','non-interactive','ignore','exclude',
                'exclude-from','include','include-from','quarantine','all','restore',
                'whitelist','whitelist-path','key-status','scan-key','show-hits','max-age',
                'ignore-chmod0','incremental','time-check','update','channel','report',
                'false-positive','force-yes','use-tmp','use-tmpfs','cleanup',
                'check-permissions','db-scan','db-scan-maxrows','db-scan-full',
                'db-no-context','db-exclude','db-exclude-table','db-only','db-only-table',
                'blacklist-check'];

            // Filter only allowed keys
            $ispp_params = array_filter(
                $ispp_params,
                function ($key) use ($allowed_params) {
                    return in_array($key, $allowed_params);
                },
                ARRAY_FILTER_USE_KEY
            );
            
            // Apple santization to each value in the array
            $ispp_params = array_map( [$this, 'sanitize_value'], $ispp_params );
            $results_dir = $ispp_params['results-dir'] ?? substr($ispp_json_file, 0, -5) . '_results';
            if ( ! is_dir( $results_dir ) ) {
                mkdir( $results_dir, 0755, true );

                // Set the owner and group to the same as the parent folder
                $parent_folder = dirname( $results_dir );
                $owner = fileowner( $parent_folder );
                $group = filegroup( $parent_folder );
                chown( $results_dir, $owner );
                chgrp( $results_dir, $group );
            }
            $ispp_params['results-dir'] = $results_dir;

            // Assemble the command line that removes the ispp_scan.json file and 
            // kicks off the ispp_scan command with the given parameters.
            $cmd = "rm -f $ispp_json_file ; /usr/local/bin/ispp_scan --non-interactive";
            foreach( $ispp_params as $key => $value ) {
                if ( trim( $value ) != '' ) {
                    $cmd .= " --$key=$value";
                }else{
                    $cmd .= " --$key";
                };
            }

            // Get the owner and group of the parent folder
            $parent_dir = dirname($results_dir);
            $owner = posix_getpwuid(fileowner($parent_dir))['name'];
            $group = posix_getgrgid(filegroup($parent_dir))['name'];

            // Execute the ispp_scan and put output results to $results_dir/results.txt
            // and run it asynchronously
            $cmd .= " > $results_dir/results.txt ; chown -R $owner:$group $results_dir 2>&1 &";
            $hcpp->log( "Running ISPProtect scan with command: $cmd" );
            $hcpp->log( shell_exec( $cmd ) );
            return $args;
        }

        /**
         * Sanitize ispp_scan parameters against dangerous code injections.
         */
        function sanitize_value($value) {
            // Remove any descending folder traversals, pipes, redirects, ampersands, etc.
            $dangerous_chars = ['..', '|', '&&', '&', ';', ':', '<', '>'];
            foreach ($dangerous_chars as $char) {
                $value = str_replace($char, '', $value);
            }
            return $value;
        }

        // Setup with the given user options
        public function setup( $args ) {
            return $args;
        }
    }
    new ISPProtect();
}
