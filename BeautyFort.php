<?php
/**
 * Plugin Name:       BeautyFort
 * Description:       Seemlessly integrate the BeautyFort api into your site!
 * Version:           0.0.1
 * Author:            Joe Carré
 * Author URI:        https://www.joecarre.co.uk
 * License:           GPL-2.0+
 * GitHub Plugin URI: https://github.com/carrejoe3/Beautyfort-API
 */
/*
 * Plugin constants
 */
if(!defined('BeautyFort_URL'))
	define('BeautyFort_URL', plugin_dir_url( __FILE__ ));
if(!defined('BeautyFort_PATH'))
	define('BeautyFort_PATH', plugin_dir_path( __FILE__ ));

/*
 * Main class
 */
/**
 * Class BeautyFort
 *
 * This class creates the option page and add the web app script
 */
class BeautyFort
{
    /**
     * BeautyFort constructor.
     *
     * The main plugin actions registered for WordPress
     */
    public function __construct()
    {
        // Admin page calls:
	    add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
	    add_action( 'wp_ajax_store_admin_data', array( $this, 'storeAdminData' ));
	    add_action( 'admin_enqueue_scripts', array( $this, 'addAdminScripts' ) );
    }

    /**
     * The option name
     *
     * @var string
     */
    private $option_name = 'beautyfort_data';

    /**
     * Returns the saved options data as an array
     *
     * @return array
     */
    private function getData() {
        return get_option($this->option_name, array());
    }

    /**
     * Adds the  label to the WordPress Admin Sidebar Menu
     */
    public function addAdminMenu()
    {
        add_menu_page(
        __( 'BeautyFort', 'beautyfort' ),
        __( 'BeautyFort', 'beautyfort' ),
        'manage_options',
        'beautyfort',
        array($this, 'adminLayout'),
        ''
        );
    }

    /**
     * Outputs the Admin Dashboard layout containing the form with all its options
     *
     * @return void
     */
    public function adminLayout()
    {

        $data = $this->getData();

        ?>

        <div class="wrap">
            <h3><?php _e('BeautyFort stock request', 'beautyfort'); ?></h3>

            <hr>

            <form id="beautyfort-admin-form">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <td scope="row">
                                <label><?php _e( 'Username', 'beautyfort' ); ?></label>
                            </td>
                            <td>
                                <input name="beautyfort_username" id="beautyfort_username" class="regular-text" value="<?php echo (isset($data['beautyfort_username'])) ? $data['beautyfort_usernamez'] : ''; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label><?php _e( 'Secret', 'beautyfort' ); ?></label>
                            </td>
                            <td>
                                <input name="beautyfort_secret" id="beautyfort_secret" class="regular-text" value="<?php echo (isset($data['beautyfort_secret'])) ? $data['beautyfort_secret'] : ''; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label><?php _e( 'Created', 'beautyfort' ); ?></label>
                            </td>
                            <td>
                                <input name="beautyfort_created" id="beautyfort_created" class="regular-text" value="<?php echo (isset($data['beautyfort_created'])) ? $data['beautyfort_created'] : ''; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label><?php _e( 'Nonce', 'beautyfort' ); ?></label>
                            </td>
                            <td>
                                <input name="beautyfort_nonce" id="beautyfort_nonce" class="regular-text" value="<?php echo (isset($data['beautyfort_nonce'])) ? $data['beautyfort_nonce'] : ''; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <button class="button button-primary" id="beautyfort-admin-save" type="submit"><?php _e( 'Save', 'beautyfort' ); ?></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>

        <?php

        var_dump($data);

    }

    /**
     * The security nonce
     *
     * @var string
     */
    private $_nonce = 'beautyfort_admin';

    /**
     * Adds Admin Scripts for the Ajax call
     */
    public function addAdminScripts()
    {
        wp_enqueue_script('beautyfort-admin', BeautyFort_URL. '/assets/js/admin.js', array(), 1.0);
        $admin_options = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            '_nonce'   => wp_create_nonce( $this->_nonce ),
        );
        wp_localize_script('beautyfort-admin', 'beautyfort_exchanger', $admin_options);
    }

    /**
     * Callback for the Ajax request
     *
     * Updates the options data
     *
     * @return void
     */
    public function storeAdminData()
    {

        if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false)
        die('Invalid Request!');

        $data = $this->getData();

        foreach ($_POST as $field=>$value) {

        if (substr($field, 0, 8) !== "beautyfort_" || empty($value))
        continue;

        // We remove the beautyfort_ prefix to clean things up
        $field = substr($field, 8);

            $data[$field] = $value;

        }

        update_option($this->option_name, $data);

        echo __('Saved!', 'beautyfort');
        die();

    }
}
/*
 * Starts our plugin class, easy!
 */
new BeautyFort();

// Initialize webservice

// $options = array(
//     'Username' => $username,
//     'Nonce' => $nonce,
//     'Created' => $dateTime,
//     'Password' => $password,
//     'TestMode' => true,
//     'StockFileFormat' => 'JSON',
// );

// $client = new SoapClient('http://www.beautyfort.com/api/wsdl/v2/wsdl.wsdl', $options);

// var_dump($response);