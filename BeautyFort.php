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
     * The security nonce
     *
     * @var string
     */
    private $_nonce = 'beautyfort_admin';

    /**
     * The option name
     *
     * @var string
     */
    private $option_name = 'beautyfort_data';

    /**
     * Beautyfort api username
     *
     * @var string
     */
    private $soapUsername = 'joetest';

    /**
     * Beautyfort api secret
     *
     * @var string
     */
    private $soapSecret = 'jcRZVsWP2XdDt5iJIM0mS64hCr3f';

    /**
     * Beautyfort api datetime
     *
     * @var string
     */
    private $soapDateTime = '';

    /**
     * Beautyfort api nonce
     *
     * @var string
     */
    private $soapNonce = '';

    /**
     * Beautyfort api password
     *
     * @var string
     */
    private $soapPassword = '';

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
                                <input name="beautyfort_beautyfortUser" id="beautyfort_beautyfortUser" class="regular-text" value="<?php echo (isset($data['beautyfort_beautyfortUser'])) ? $data['beautyfort_beautyfortUser'] : ''; ?>"/>
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

    }

    /**
     * Adds Admin Scripts for the Ajax call
     */
    public function addAdminScripts()
    {
        wp_enqueue_script('beautyfort-admin', BeautyFort_URL. 'assets/js/admin.js', array(), 1.0);
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
            die('Invalid Request! Reload your page please.');

        $data = $this->getData();

        foreach ($_POST as $field=>$value) {

            if (substr($field, 0, 11) !== "beautyfort_" || empty($value))
                continue;

            if (empty($value))
                unset($data[$field]);

            // We remove the beautyfort_ prefix to clean things up
            $field = substr($field, 11);

            $data[$field] = esc_attr__($value);
        }

        $soapUsername = $_POST['beautyfort_beautyfortUser'];
        $soapSecret = $_POST['beautyfort_secret'];
        $soapDateTime = $_POST['beautyfort_created'];
        $soapNonce = $_POST['beautyfort_nonce'];
        $soapPassword = $_POST['beautyfort_password'];

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
// $client = new SoapClient('http://www.beautyfort.com/api/wsdl/v2/wsdl.wsdl', $options);

// var_dump($response);

// useful logging function
function log_me($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}