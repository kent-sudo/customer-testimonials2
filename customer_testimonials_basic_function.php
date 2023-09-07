<?php
/*
Plugin Name: KENT customer testimonials Plugin
Description: A plugin to add cards from the admin panel.
Version: 1.0
Author: KENT
*/
// Enqueue Bootstrap styles and scripts
function customer_testimonials_enqueue_bootstrap_assets() {
    global $post;
    if (has_shortcode($post->post_content, 'show_customer_testimonials_1'))
    {
        wp_enqueue_style('prefix_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
        wp_enqueue_script('prefix_bootstrap_js', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js', array('jquery'), null, false);
        wp_enqueue_script('prefix_bootstrap_bundle', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, false);
        wp_enqueue_script('prefix_jq', '//code.jquery.com/jquery-3.5.1.min.js', array('jquery'), null, false);
    }
}

add_action('wp_enqueue_scripts', 'customer_testimonials_enqueue_bootstrap_assets');

function create_customer_testimonials_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'customer_testimonials';

    // Check if the table already exists
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Table not in database. Create new table
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title text NOT NULL,
            subtitle text NOT NULL,
            description text NOT NULL,
            image_url text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Run the function when the plugin is activated
register_activation_hook(__FILE__, 'create_customer_testimonials_table');

// Hook for adding admin menus
add_action('admin_menu', 'add_customer_testimonials_menu');

// action function for above hook
function add_customer_testimonials_menu() {
    // Add a new top-level menu
    add_menu_page('添加樣本', '添加樣本', 'manage_options', 'add_customer_testimonials', 'add_add_customer_testimonials_page' );
}

// Display the admin options page for managing sub cards
function add_add_customer_testimonials_page() {
    ?>
    <div>
        <h2>客戶完成樣本</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <p><label>添加樣本: <input type="text" name="sub_card_title" /></label></p>
            <p><label>點擊區的文字: <input type="text" name="sub_card_click" /></label></p>
            <p><label>圖片: <input type="file" name="sub_card_image" /></label></p>
            <p><label>詳細的的講解:</label></p>
            <textarea name="sub_card_description" type="text" id="summernote_kent" >
            </textarea>
            <p><input type="submit" value="Submit" /></p>
        </form>
    </div>
    <div>
        <?php if (isset($_GET['success'])): ?>
            <div class="notice notice-success">
                <p>Sub Card added successfully!</p>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="notice notice-error">
                <p>Error: <?php echo urldecode($_GET['error']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Rest of your form goes here -->
    </div>
    <?php
}

// Handle the submission of the sub card form
add_action('admin_init', 'handle_customer_testimonials_submission');
function handle_customer_testimonials_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub_card_title'],$_POST['sub_card_click'], $_POST['sub_card_description'], $_FILES['sub_card_image'])) {
        global $wpdb;

        $sub_table_name = $wpdb->prefix . 'customer_testimonials'; // Use your sub_cards table name

        $sub_card_title = sanitize_text_field($_POST['sub_card_title']);
        //summernote content
        $content = $_POST['sub_card_description'];
        $content = wp_kses_post($content);
        $sub_card_description = $content;
        $sub_card_click = $_POST['sub_card_click'];
        $sub_card_image = $_FILES['sub_card_image'];

        // Validate and sanitize uploaded image
        $image_url = '';
        if (!empty($sub_card_image['tmp_name'])) {
            if (!function_exists('wp_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            $uploadedfile = $_FILES['sub_card_image'];
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
            if ($movefile && !isset($movefile['error'])) {
                $image_url = esc_url($movefile['url']);
            } else {
                wp_redirect(admin_url('admin.php?page=manage_sub_cards&error=' . urlencode($movefile['error'])));
                exit;
            }
        }

        $data = array(
            'title' => $sub_card_title,
            'subtitle' => $sub_card_click,
            'description' => $sub_card_description,
            'image_url' => $image_url, // Use the image URL from file upload
        );

        $format = array('%s', '%s', '%d', '%s'); // Add %d for integer data

        if ($wpdb->insert($sub_table_name, $data, $format)) {
            wp_redirect(admin_url('admin.php?page=add_customer_testimonials&success=true'));
        } else {
            wp_redirect(admin_url('admin.php?page=add_customer_testimonials&error=' . urlencode('Failed to insert sub card into database.')));
        }
        exit;
    }
}

function get_all_customer_testimonials() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'customer_testimonials';

    $customer_testimonials = $wpdb->get_results(
        "
        SELECT 
            id ,
            title ,
            subtitle ,
            image_url
        FROM $table_name
        "
    );

    return $customer_testimonials;
}
// Register the shortcode
add_shortcode('show_customer_testimonials_1', 'show_customer_testimonials_shortcode');
//show customer card

// Updated show_customer_testimonials_shortcode function
function show_customer_testimonials_shortcode($atts) {
    $customer_testimonials = get_all_customer_testimonials();

    $output = '<div class="container"><br><div class="row self-examination-main"><em style="text-align: center;">Step 1</em><br><h3 style="text-align: center;">選擇設備類型</h3><br>';

    foreach ($customer_testimonials as $customer_testimonial) {
        // Generate a link for each card with a URL parameter to identify the testimonial
        // Add 'testimonial_id' parameter to URL
        $testimonial_url = esc_url(add_query_arg('testimonial_id', $customer_testimonial->id, 'http://localhost/wordpress/testimonial/'));
        $output .= '
            <div class="col col-6 col-sm-6 col-md-3 col-lg-3" style="margin-bottom:20px;">
                <a href="' . $testimonial_url . '" class="card-link">
                    <div class="card shadow-sm h-100 kent-main-card" data-card-id="' . esc_attr($customer_testimonial->id) . '">
                        <img src="' . esc_url($customer_testimonial->image_url) . '" alt="' . esc_attr($customer_testimonial->title) . '" width="100%" height="auto">
                        <h5>' . esc_html($customer_testimonial->title) . '</h5>
                        <h7>' . esc_html($customer_testimonial->subtitle) . '</h7>
                    </div>
                </a>
            </div>';
    }
    $output .= '</div></div>'; // Closing the outer div container

    return $output;
}

function get_customer_testimonial_by_id($id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'customer_testimonials';

    $customer_testimonial = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        )
    );

    return $customer_testimonial;
}
// Register the shortcode
add_shortcode('show_customer_testimonial_2', 'show_customer_testimonial_shortcode');

function show_customer_testimonial_shortcode($atts) {
    // Extract the ID attribute from the shortcode
    $atts = shortcode_atts(array(
        'id' => 0, // Default to 0 if no ID is provided
    ), $atts);

    $testimonial_id = intval($atts['id']);
    $customer_testimonial = get_customer_testimonial_by_id($testimonial_id);

    if ($customer_testimonial) {
        $output = '<div class="container"><br><div class="row self-examination-main"><em style="text-align: center;">Step 1</em><br><h3 style="text-align: center;">選擇設備類型</h3><br>';

        $output .= '
            <div class="col col-6 col-sm-6 col-md-3 col-lg-3" style="margin-bottom:20px;">
                    <div class="card shadow-sm h-100 kent-main-card" data-card-id="' . esc_attr($customer_testimonial->id) . '">
                        <img src="' . esc_url($customer_testimonial->image_url) . '" alt="' . esc_attr($customer_testimonial->title) . '" width="100%" height="auto">
                        <h5>' . esc_html($customer_testimonial->title) . '</h5>
                        <h7>' . esc_html($customer_testimonial->subtitle) . '</h7>
                        <div><p>' . esc_html($customer_testimonial->description) . '</p></div>
                    </div>
            </div>';
        $output .= '</div></div>'; // Closing the outer div container

        return $output;
    } else {
        return 'No testimonial found with the provided ID.';
    }
}

