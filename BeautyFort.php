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
    private $option_name = 'feedier_data';
    
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
     * Make an API call to the BeautyFort API and returns the response
     *
     * @param string $private_key
     * @return array
     */
    private function getSurveys($private_key)
    {
 
        $data = array();
        $response = wp_remote_get('https://api.feedier.com/v1/carriers/?api_key='. $private_key);
    
        if (is_array($response) && !is_wp_error($response)) {
            $data = json_decode($response['body'], true);
        }
    
        return $data;
    }

    /**
     * Outputs the Admin Dashboard layout containing the form with all its options
     *
     * @return void
     */
    public function adminLayout()
    {
    
        $data = $this->getData();
    
        $surveys = $this->getSurveys($data['private_key']);
    
        ?>
    
        <div class="wrap">
            <h3><?php _e('Feedier API Settings', 'feedier'); ?></h3>
    
                <p>
                <?php _e('You can get your Feedier API settings from your <b>Integrations</b> page.', 'feedier'); ?>
                </p>
    
                <hr>
    
                <form id="feedier-admin-form">
    
            <table class="form-table">
                        <tbody>
                            <tr>
                                <td scope="row">
                                    <label><?php _e( 'Public key', 'feedier' ); ?></label>
                                </td>
                                <td>
                                    <input name="feedier_public_key"
                                        id="feedier_public_key"
                                        class="regular-text"
                                        value="<?php echo (isset($data['public_key'])) ? $data['public_key'] : ''; ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <td scope="row">
                                    <label><?php _e( 'Private key', 'feedier' ); ?></label>
                                </td>
                                <td>
                                    <input name="feedier_private_key"
                                        id="feedier_private_key"
                                        class="regular-text"
                                        value="<?php echo (isset($data['private_key'])) ? $data['private_key'] : ''; ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <hr>
                                    <h4><?php _e( 'Widget options', 'feedier' ); ?></h4>
                                </td>
                            </tr>
    
                            <?php if (!empty($data['private_key']) && !empty($data['public_key'])): ?>
    
                                <?php
                                // if we don't even have a response from the API
                                if (empty($surveys)) : ?>
    
                                    <tr>
                                        <td>
                                            <p class="notice notice-error">
                                                <?php _e( 'An error happened on the WordPress side. Make sure your server allows remote calls.', 'feedier' ); ?>
                                            </p>
                                        </td>
                                    </tr>
    
                                <?php
                                // If we have an error returned by the API
                                elseif (isset($surveys['error'])): ?>
    
                                    <tr>
                                        <td>
                                            <p class="notice notice-error">
                                                <?php echo $surveys['error']; ?>
                                            </p>
                                        </td>
                                    </tr>
    
                                <?php
                                // If the surveys were returned
                                else: ?>
    
                                    <tr>
                                        <td>
                                            <p class="notice notice-success">
                                                <?php _e( 'The API connection is established!', 'feedier' ); ?>
                                            </p>
    
                                            <div>
                                                <label><?php _e( 'Choose a survey', 'feedier' ); ?></label>
                                            </div>
                                            <select name="feedier_widget_carrier_id"
                                                    id="feedier_widget_carrier_id">
                                                <?php
                                                // We loop through the surveys
                                                foreach ($surveys['data'] as $survey) : ?>
                                                    <?php
                                                    // We also only keep the id -> x from the carrier_x returned by the API
                                                    $survey['id'] = substr($survey['id'], 8); ?>
                                                    <option value="<?php echo $survey['id']; ?>" <?php echo ($survey['id'] === $data['widget_carrier_id']) ? 'selected' : '' ?>>
                                                        <?php echo $survey['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <hr>
                                    </tr>
    
                                    <tr>
                                        <td>
                                            <div class="label-holder">
                                                <label><?php _e( 'Display probability (from 0 to 100)', 'feedier' ); ?></label>
                                            </div>
                                            <input name="feedier_widget_display_probability"
                                                id="feedier_widget_display_probability"
                                                class="regular-text"
                                                value="<?php echo (isset($data['widget_display_probability'])) ? $data['widget_display_probability'] : '100'; ?>"/>
                                        </td>
                                        <td>
                                            <div class="label-holder">
                                                <label><?php _e( 'Shaking effect (shake after 10s without click)', 'feedier' ); ?></label>
                                            </div>
                                            <input name="feedier_widget_shake"
                                                id="feedier_widget_shake"
                                                type="checkbox"
                                                <?php echo (isset($data['widget_shake']) && $data['widget_shake']) ? 'checked' : ''; ?>/>
                                        </td>
                                        <td>
                                            <div class="label-holder">
                                                <label><?php _e( 'Position', 'feedier' ); ?></label>
                                            </div>
                                            <select name="feedier_widget_position"
                                                    id="feedier_widget_position">
                                                <option value="left" <?php echo (!isset($data['widget_position']) || (isset($data['widget_position']) && $data['widget_position'] === 'left')) ? 'checked' : ''; ?>>
                                                    <?php _e( 'Left side', 'feedier' ); ?>
                                                </option>
                                                <option value="right" <?php echo (isset($data['widget_position']) && $data['widget_position'] === 'right') ? 'checked' : ''; ?>>
                                                    <?php _e( 'Right side', 'feedier' ); ?>
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
    
                                <?php endif; ?>
    
                            <?php else: ?>
    
                                <tr>
                                    <td>
                                        <p>Please fill up your API keys to see the widget options.</p>
                                    </td>
                                </tr>
    
                            <?php endif; ?>
    
                            <tr>
                                <td colspan="2">
                                    <button class="button button-primary" id="feedier-admin-save" type="submit"><?php _e( 'Save', 'feedier' ); ?></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
    
                </form>
    
        </div>
    
        <?php
    
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
    
            if (substr($field, 0, 8) !== "feedier_" || empty($value))
        continue;
    
        // We remove the feedier_ prefix to clean things up
        $field = substr($field, 8);
    
            $data[$field] = $value;
    
        }
    
        update_option($this->option_name, $data);
    
        echo __('Saved!', 'feedier');
        die();
    
    }
}
/*
 * Starts our plugin class, easy!
 */
new BeautyFort();