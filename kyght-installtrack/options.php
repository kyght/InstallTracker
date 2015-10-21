<?php

class KYG_INSTK_SettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Install Tracker Admin',
            'Install Tracker',
            'manage_options',
            'kytracker-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'kytracker_option_name' );
        ?>
        <div class="wrap">
            <h2>Install Tracker Settings</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'kytracker_option_group' );
                do_settings_sections( 'kytracker-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'kytracker_option_group', // Option group
            'kytracker_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'General', // Title
            array( $this, 'print_section_info' ), // Callback
            'kytracker-setting-admin' // Page
        );

        add_settings_field(
            'secret_key', // ID
            'Secret Key', // Title
            array( $this, 'secretkey_callback' ), // Callback
            'kytracker-setting-admin', // Page
            'setting_section_id' // Section
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['secret_key'] ) )
            $new_input['secret_key'] = sanitize_text_field( $input['secret_key'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function secretkey_callback()
    {
        printf(
            '<input type="text" id="secret_key" name="kytracker_option_name[secret_key]" value="%s" />',
            isset( $this->options['secret_key'] ) ? esc_attr( $this->options['secret_key']) : ''
        );
    }
}

if( is_admin() )
    $kyginstk_settings_page = new KYG_INSTK_SettingsPage();
    
