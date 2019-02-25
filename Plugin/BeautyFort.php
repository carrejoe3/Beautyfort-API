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

if (!defined('BeautyFort_URL')) {
    define('BeautyFort_URL', plugin_dir_url(__FILE__));
}

if (!defined('BeautyFort_PATH')) {
    define('BeautyFort_PATH', plugin_dir_path(__FILE__));
}

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
     * BeautyFort constructor.
     *
     * The main plugin actions registered for WordPress
     */
    public function __construct()
    {
        // Admin page calls:
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('wp_ajax_store_admin_data', array($this, 'storeAdminData'));
        add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'));
    }

    /**
     * Returns the saved options data as an array
     *
     * @return array
     */
    private function getData()
    {
        return get_option($this->option_name, array());
    }

    /**
     * Adds the  label to the WordPress Admin Sidebar Menu
     */
    public function addAdminMenu()
    {
        add_menu_page(
            __('BeautyFort', 'beautyfort'),
            __('BeautyFort', 'beautyfort'),
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
            <h3><?php _e('BeautyFort stock request', 'beautyfort');?></h3>

            <hr>

            <form id="beautyfort-admin-form">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <td scope="row">
                                <label><?php _e('Username', 'beautyfort');?></label>
                            </td>
                            <td>
                                <input name="beautyfort_beautyfortUser" id="beautyfort_beautyfortUser" class="regular-text" value="<?php echo (null !== (get_option('beautyfort_beautyfortUser'))) ? get_option('beautyfort_beautyfortUser') : ''; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label><?php _e('Secret', 'beautyfort');?></label>
                            </td>
                            <td>
                                <input name="beautyfort_secret" id="beautyfort_secret" class="regular-text" value="<?php echo (null !== (get_option('beautyfort_secret'))) ? get_option('beautyfort_secret') : ' '; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label><?php _e('Stockcode', 'beautyfort');?></label>
                            </td>
                            <td>
                                <input name="beautyfort_stockcode" id="beautyfort_stockcode" class="regular-text" value="<?php echo (null !== (get_option('beautyfort_stockcode'))) ? get_option('beautyfort_stockcode') : ' '; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label><?php _e('Stock Level', 'beautyfort');?></label>
                            </td>
                            <td>
                                <input name="beautyfort_stocklevel" id="beautyfort_stocklevel" class="regular-text" value=""/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button class="button button-primary" id="beautyfort-admin-save" type="submit"><?php _e('Query', 'beautyfort');?></button>
                            </td>
                        </tr>
                        <!-- hidden fields -->
                        <tr>
                            <td>
                                <input type="hidden" name="beautyfort_nonce" id="beautyfort_nonce" class="regular-text" value=""/>
                            </td>
                            <td>
                                <input type="hidden" name="beautyfort_password" id="beautyfort_password" class="regular-text" value=""/>
                            </td>
                            <td>
                                <input type="hidden" name="beautyfort_created" id="beautyfort_created" class="regular-text" value=""/>
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
        wp_enqueue_script('beautyfort-admin', BeautyFort_URL . 'assets/js/admin.js', array(), 1.0);

        $admin_options = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            '_nonce' => wp_create_nonce($this->_nonce),
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

        if (wp_verify_nonce($_POST['security'], $this->_nonce) === false) {
            die('Invalid Request! Reload your page please.');
        }

        $data = $this->getData();

        foreach ($_POST as $field => $value) {

            if (substr($field, 0, 11) !== "beautyfort_" || empty($value)) {
                continue;
            }

            if (empty($value)) {
                unset($data[$field]);
            }

            // We remove the beautyfort_ prefix to clean things up
            $field = substr($field, 11);

            $data[$field] = esc_attr__($value);
        }

        update_option($this->option_name, $data);
        update_option('beautyfort_beautyfortUser', $_POST['beautyfort_beautyfortUser']);
        update_option('beautyfort_secret', $_POST['beautyfort_secret']);
        update_option('beautyfort_stockcode', $_POST['beautyfort_stockcode']);

        soapRequest($_POST['beautyfort_beautyfortUser'], $_POST['beautyfort_nonce'], $_POST['beautyfort_created'], $_POST['beautyfort_password'], $_POST['beautyfort_stockcode']);

        echo __('Saved!', 'beautyfort');
        die();
    }
}
/*
 * Starts our plugin class, easy!
 */

new BeautyFort();

// useful logging function
function log_me($message)
{
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

function soapRequest($soapUsername, $soapNonce, $soapDateTime, $soapPassword, $soapStockCode)
{

    $wsdl = 'http://www.beautyfort.com/api/wsdl/v2/wsdl.wsdl';
    $mode = array(
        'soap_version' => 'SOAP_1_1',
        'keep_alive' => true,
        'trace' => 1,
        'encoding' => 'UTF-8',
        'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'user_agent' => 'Apache-HttpClient/4.5.2 (Java/1.8.0_181)',
    );

    $client = new SoapClient($wsdl, $mode);

    $auth = array(
        'Username' => $soapUsername,
        'Nonce' => $soapNonce,
        'Created' => $soapDateTime,
        'Password' => $soapPassword,
    );

    $header = new SoapHeader('http://www.beautyfort.com/api/', 'AuthHeader', $auth);

    $client->__setSoapHeaders($header);

    $xml_array['TestMode'] = true;
    $xml_array['StockFileFormat'] = 'JSON';
    $xml_array['FieldDelimiter'] = ',';
    $xml_array['StockFileFields'] = array(
        "StockFileField" => "StockCode",
        "StockFileField" => "StockLevel",
    );
    $xml_array['SortBy'] = 'StockCode';

    try {
        $response = $client->GetStockFile($xml_array);
    } catch (Exception $e) {
        log_me("Error!");
        log_me($e->getMessage());
        wp_send_json('Item not found');
    }

    if (!is_soap_fault($response)) {
        updateStockLevels($response);
        $filteredResponse = filterResponse($response, $soapStockCode);
        wp_send_json($filteredResponse);
    }

    // log_me($response);
    // log_me($client->__getFunctions());
    // log_me('Last request: '. $client->__getLastRequest());
    // log_me('Last request headers: '. $client->__getLastRequestHeaders());
    // log_me('Last response headers: '. $client->__getLastResponseHeaders());
    // log_me('Last response: '. $client->__getLastResponse());
}

// returns row from webservice that matches stockcode
function filterResponse($soapResponse, $soapStockCode)
{
    // declare output variable
    $filteredRow;

    // clean up response data
    $fileData = extractFileDataFromResponse($soapResponse);

    // match row on stockcode
    foreach ($fileData as $key => $val) {

        if (isset($val['StockCode']) && trim($val['StockCode']) == trim($soapStockCode)) {
            $filteredRow = $val;
        }
    }

    // if stockcode isn't found, set returned string to error message
    if (!isset($filteredRow)) {
        $filteredRow = 'Item not found.';
    }

    return $filteredRow;
}

// returns all product posts
function getProductPosts()
{
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
    );

    $products_array = get_posts($args);

    return $products_array;
}

function updateStockLevels($data)
{
    $stockcodes = getStockCodes();

    // clean up response data
    $data = extractFileDataFromResponse($data);

    // declare response ids array to be used later
    $responseIds = [];

    // loop through both arrays
    for ($i = 0; $i < count($stockcodes); $i++) {
        foreach ($data as $value) {
            // match on stockcode
            if ($value['StockCode'] == $stockcodes[$i]) {
                global $woocommerce;
                // get product id
                $product_id = wc_get_product_id_by_sku($stockcodes[$i]);
                $woocmmerce_instance = new WC_Product($product_id);
                $new_quantity = wc_update_product_stock($woocmmerce_instance, $value['StockLevel']);
                log_me($woocmmerce_instance);
            }
        }
    }
}

function getStockCodes()
{
    // get array of product posts
    $productPosts = getProductPosts();

    // array of stockcode ids
    $stockcodes = [];

    // loop through product posts and get the id's products
    if (!empty($productPosts)) {
        foreach ($productPosts as $product) {
            // double check its a product we're looking at
            $post_type = get_post_type($product);

            if ($post_type == 'product') {
                $product = wc_get_product($product);
                // push stockcode to stockcode array
                array_push($stockcodes, $product->get_sku());
            }
        }
    }

    return $stockcodes;
}

function extractFileDataFromResponse($data)
{
    // convert stdclass into object
    $data = get_object_vars($data);

    // target file data in response
    $fileData = $data['File'];

    // decode json
    $fileData = json_decode($fileData, true);

    return $fileData;
}