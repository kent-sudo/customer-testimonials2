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
    if (has_shortcode($post->post_content, 'show_customer_testimonials_1')||has_shortcode($post->post_content,'show_customer_testimonial_2'))
    {
        wp_enqueue_style('kent-customer-testimonials-css', plugin_dir_url(__FILE__) . 'customer-testimonials-css/cystiner-testimonials.css');
        wp_enqueue_style('prefix_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
        wp_enqueue_script('prefix_bootstrap_js', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js', array('jquery'), null, false);
        wp_enqueue_script('prefix_bootstrap_bundle', '//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', array('jquery'), null, false);
        wp_enqueue_script('prefix_jq', '//code.jquery.com/jquery-3.5.1.min.js', array('jquery'), null, false);
    }
}

add_action('wp_enqueue_scripts', 'customer_testimonials_enqueue_bootstrap_assets');

function customer_testimonials_enqueue_summernote_assets() {
    wp_enqueue_style('summernote-bootstrap-css','//stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css' );
    wp_enqueue_script('summernote-jquery-js', '//code.jquery.com/jquery-3.5.1.min.js', array('jquery'), null, false);
    wp_enqueue_script('summernote-bootstrap-js', '//stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js', array('jquery'), null, false);
    wp_enqueue_style('summernote-css', '//cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css');
    wp_enqueue_script('summernote-js', '//cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js', array('jquery'), null, false);
    wp_enqueue_script('summernote-init-js', plugin_dir_url(__FILE__) . 'js/summernote-init.js', array('jquery'), null, true);
    wp_enqueue_script('summernote-zh-TW.js', plugin_dir_url(__FILE__) . 'summernote/lang/summernote-zh-TW.js', array('jquery'), null, false);
}

add_action('admin_enqueue_scripts', 'customer_testimonials_enqueue_summernote_assets');

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
            device text NOT NULL,
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
            <p><label>添加主標題: <input type="text" name="sub_card_title" /></label></p>
            <p><label>添加副標題: <input type="text" name="sub_card_subtitle" /></label></p>
            <p><label>裝置: <input type="text" name="sub_card_device" /></label></p>
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
    </div>
    <?php
}

// Handle the submission of the sub card form
add_action('admin_init', 'handle_customer_testimonials_submission');
function handle_customer_testimonials_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub_card_title'],$_POST['sub_card_subtitle'],$_POST['sub_card_device'], $_POST['sub_card_description'], $_FILES['sub_card_image'])) {
        global $wpdb;

        $sub_table_name = $wpdb->prefix . 'customer_testimonials'; // Use your sub_cards table name

        $sub_card_title = sanitize_text_field($_POST['sub_card_title']);
        $sub_card_subtitle = sanitize_text_field($_POST['sub_card_subtitle']);
        $sub_card_device = sanitize_text_field($_POST['sub_card_device']);
        $sub_card_description = wp_kses_post( $_POST['sub_card_description']);
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
                wp_redirect(admin_url('admin.php?page=add_customer_testimonials&error=' . urlencode($movefile['error'])));
                exit;
            }
        }

        $data = array(
            'title' => $sub_card_title,
            'subtitle' => $sub_card_subtitle,
            'device' => $sub_card_device,
            'description' => $sub_card_description,
            'image_url' => $image_url, // Use the image URL from file upload
        );

        $format = array('%s', '%s','%s', '%s', '%s'); // Add %d for integer data

        if ($wpdb->insert($sub_table_name, $data, $format)) {
            wp_redirect(admin_url('admin.php?page=add_customer_testimonials&success=true'));
        } else {
            wp_redirect(admin_url('admin.php?page=add_customer_testimonials&error=' . urlencode('Failed to insert sub card into database.')));
        }
        exit;
    }
}


// 添加一个新的edit菜单页面
function add_edit_customer_testimonials_menu() {
    add_submenu_page('add_customer_testimonials', '修改樣本', '修改樣本', 'manage_options', 'edit_customer_testimonials', 'edit_customer_testimonials_page');
}

add_action('admin_menu', 'add_edit_customer_testimonials_menu');
function edit_customer_testimonials_page()
{
    $customer_testimonials = get_all_customer_testimonials();
    ?>
    <div class="row self-examination-main">
        <?php
        foreach ($customer_testimonials as $customer_testimonial) {
            ?>
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="margin-bottom:20px;">
                <div class="card shadow-sm h-100 main-card" data-card-id="<?php echo esc_attr($customer_testimonial->id); ?>">
                    <img src="<?php echo esc_html($customer_testimonial->image_url); ?>" alt="<?php echo esc_html($customer_testimonial->title); ?>" width="100%" height="250px">
                    <h2 style="font-size: 15px;"><?php echo esc_html($customer_testimonial->title); ?></h2>
                    <p><?php echo esc_html($customer_testimonial->subtitle); ?></p>
                    <a class="btn btn-primary" href="<?php echo admin_url('admin.php?page=edit_customer_testimonials2&customer_testimonial_id=' . esc_attr($customer_testimonial->id)); ?>" role="button">修改</a>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="customer_testimonials_id_edit" value="<?php echo esc_attr($customer_testimonial->id); ?>">
                        <p><input class="btn btn-danger" type="submit" name="delete_customer_testimonials_card" value="刪除" onclick="return confirm('您確定要刪除這張卡嗎？');" /></p>
                    </form>
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
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

//delete
add_action('admin_init', 'handle_delete_customer_testimonials_submission');
function handle_delete_customer_testimonials_submission() {
    if (isset($_POST['delete_customer_testimonials_card'])) {

        // Get the card ID to delete
        $customer_testimonial_id_to_delete = isset($_POST['customer_testimonials_id_edit']) ? intval($_POST['customer_testimonials_id_edit']) : 0;
        // Delete the card
        if (delete_customer_testimonials_by_id($customer_testimonial_id_to_delete)) {
            // Card deletion was successful
            wp_safe_redirect(admin_url('admin.php?page=edit_customer_testimonials'), 302);
        } else {
            // Error occurred during deletion
            wp_safe_redirect(admin_url('admin.php?page=edit_customer_testimonials&customer_testimonial_id=' . esc_attr($customer_testimonial_id_to_delete) . '&error=delete_failed'), 302);
        }
        exit();
    }
}

// delete_card
function delete_customer_testimonials_by_id($customer_testimonial_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'customer_testimonials';

    // Delete the card from the database
    $result = $wpdb->delete(
        $table_name,
        array('id' => $customer_testimonial_id),
        array('%d')
    );

    return $result !== false;
}


// 处理编辑后的卡片数据
function add_edit_customer_testimonials_menu2() {
    add_submenu_page('add_customer_testimonials', '修改樣本-修改', '修改樣本-修改', 'manage_options', 'edit_customer_testimonials2', 'edit_customer_testimonials_page2');
}

add_action('admin_menu', 'add_edit_customer_testimonials_menu2');
function edit_customer_testimonials_page2()
{
    // 获取要编辑的卡片ID
    $customer_testimonial_id = isset($_GET['customer_testimonial_id']) ? intval($_GET['customer_testimonial_id']) : 0; // 通过URL参数获取

    // 根据卡片ID从数据库中检索卡片数据
    $customer_testimonial = get_customer_testimonial_by_id($customer_testimonial_id);

    if ($customer_testimonial) {
        ?>
        <!-- 表单开始 -->
        <div>
            <h2>Edit sub Card</h2>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="customer_testimonial_id_edit" value="<?php echo esc_attr($customer_testimonial->id); ?>" />
                <p><label>主標題: <input type="text" name="customer_testimonial_title_edit" value="<?php echo esc_attr($customer_testimonial->title); ?>" /></label></p>
                <p><label>副標題: <input type="text" name="customer_testimonial_subtitle_edit" value="<?php echo esc_attr($customer_testimonial->subtitle); ?>" /></label></p>
                <p><label>裝置: <input type="text" name="customer_testimonial_device_edit" value="<?php echo esc_attr($customer_testimonial->device); ?>" /></label></p>
                <p><label>圖片: <input type="file" name="customer_testimonial_image_edit" /></label></p>
                <p><label>詳細的講解:</label></p>
                <textarea name="customer_testimonial_description_edit" type="text" id="summernote_kent">
                    <?php echo $customer_testimonial->description ?>
                </textarea>
                <p><input type="submit" value="Submit" /></p>
            </form>
        </div>
        <div>
            <?php if (isset($_GET['success'])): ?>
                <div class="notice notice-success">
                    <p>成功!</p>
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="notice notice-error">
                    <p>失敗: <?php echo urldecode($_GET['error']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Rest of your form goes here -->
        </div>
        <!-- 表单结束 -->
        <?php
    } else {
        echo '卡片不存在或已被删除。';
    }
}

add_action('admin_init', 'handle_edit_customer_testimonials_submission');
// 处理编辑后的卡片数据
function handle_edit_customer_testimonials_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_testimonial_id_edit'], $_POST['customer_testimonial_title_edit'],$_POST['customer_testimonial_device_edit'], $_POST['customer_testimonial_subtitle_edit'], $_POST['customer_testimonial_description_edit'], $_FILES['customer_testimonial_image_edit'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'customer_testimonials';
        $sub_card_id = intval($_POST['customer_testimonial_id_edit']);
        $sub_card_title = sanitize_text_field($_POST['customer_testimonial_title_edit']);
        $sub_card_click = sanitize_text_field($_POST['customer_testimonial_subtitle_edit']);
        $sub_card_device = sanitize_text_field($_POST['customer_testimonial_device_edit']);
        //summernote content
        $content = $_POST['customer_testimonial_description_edit'];
        $content = wp_kses_post($content);
        $sub_card_description = $content;
        $sub_card_image_edit = $_FILES['customer_testimonial_image_edit'];

        // Validate and sanitize uploaded image
        $image_url = '';
        if (!empty($sub_card_image_edit['tmp_name'])) {
            if (!function_exists('wp_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            $uploadedfile = $_FILES['customer_testimonial_image_edit'];
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
            if ($movefile && !isset($movefile['error'])) {
                $image_url = esc_url($movefile['url']);
            } else {
                wp_redirect(admin_url('admin.php?page=edit_customer_testimonials2&error=' . urlencode($movefile['error'])));
                exit;
            }
        }

        // Update card data in database
        $data = array(
            'title' => $sub_card_title,
            'subtitle' => $sub_card_click,
            'description' => $sub_card_description,
            'device'=>$sub_card_device,
            'image_url' => $image_url, // Use the image URL from file upload
        );
        $where = array('id' => $sub_card_id);
        $format = array('%s', '%s', '%s','%s'); // Data format (%s as string; more info in the wpdb documentation)
        $where_format = array('%d'); // Where format

        if ($wpdb->update($table_name, $data, $where, $format, $where_format)) {
            echo "<script>alert('Hello, kent!')</script>";
            wp_redirect(admin_url('admin.php?page=edit_customer_testimonials2&success=true&customer_testimonial_id='. $sub_card_id));
        } else {
            wp_redirect(admin_url('admin.php?page=edit_customer_testimonials2&error=' . urlencode('Failed to update sub card in database.') . '&customer_testimonial_id=' . $sub_card_id));
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
            device,
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
        $testimonial_url = esc_url(add_query_arg('testimonial_id', $customer_testimonial->id, 'http://localhost/wordpress/testimonial?id='.esc_attr($customer_testimonial->id)));
        $output .= '
            <div class="col col-6 col-sm-6 col-md-3 col-lg-3" style="margin-bottom:20px;">
                <a href="' . $testimonial_url . '" class="card-link " style=" text-decoration: none;" >
                    <div class="kent-btn card shadow-sm h-100 kent-main-card" data-card-id="' . esc_attr($customer_testimonial->id) . '">
                        <img src="' . esc_url($customer_testimonial->image_url) . '" alt="' . esc_attr($customer_testimonial->title) . '" width="100%" height="auto">
                        <h5 style="text-align: center;">' . esc_html($customer_testimonial->title) . '</h5>
                        <h7 style="text-align: center;">' . esc_html($customer_testimonial->subtitle) . '</h7>
                    </div>
                </a>
            </div>
            ';
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

// last data
function get_last_customer_testimonials() {
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
        ORDER BY id DESC
        LIMIT 4
        "
    );

    return $customer_testimonials;
}

// Register the shortcode
add_shortcode('show_customer_testimonial_2', 'show_customer_testimonial_shortcode');

function show_customer_testimonial_shortcode($atts) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $customer_testimonial = get_customer_testimonial_by_id($id);

    if ($customer_testimonial) {
        $output = '<div class="container"><br><div class="row self-examination-main">';

        $output .= '
            <div class="col border-end border-start" style="margin-bottom:20px; padding: 100px">
                    <div class="h-100 kent-main-card" daa-card-id="' . esc_attr($customer_testimonial->id) . '">
                        <h2 style="text-align: center;">' . esc_html($customer_testimonial->title) . '</h2>
                        <h5 style="text-align: center;">' . esc_html($customer_testimonial->subtitle) . '</h5>
                        <div><p>' . $customer_testimonial->description . '</p></div>
                        <img src="' . esc_url($customer_testimonial->image_url) . '" alt="' . esc_attr($customer_testimonial->title) . '" width="100%" height="auto">    
                        <p style="text-align: center;">' . $customer_testimonial->device . '</p>
                    </div>
            </div>';
        $output .= '</div></div>'; // Closing the outer div container

        $output .= '<div class="container"><br><div class="row self-examination-main"><em style="text-align: center;"></em><br><h3 style="text-align: center;">其他相關推薦About Recommendation</h3><br>';
        $last_datas=get_last_customer_testimonials();
        foreach ($last_datas as $last_data) {
            // Generate a link for each card with a URL parameter to identify the testimonial
            // Add 'testimonial_id' parameter to URL
            $testimonial_url = esc_url(add_query_arg('testimonial_id', $last_data->id, 'http://localhost/wordpress/testimonial?id='.esc_attr($last_data->id)));
            $output .= '
            <div class="col col-6 col-sm-6 col-md-3 col-lg-3" style="margin-bottom:20px;">
                <a href="' . $testimonial_url . '" class="card-link" style=" text-decoration: none;">
                    <div class="card shadow-sm h-100 kent-main-card kent-btn" data-card-id="' . esc_attr($last_data->id) . '">
                        <img src="' . esc_url($last_data->image_url) . '" alt="' . esc_attr($last_data->title) . '" width="100%" height="auto">
                        <h5 style="text-align: center;">' . esc_html($last_data->title) . '</h5>
                        <h7 style="text-align: center;">' . esc_html($last_data->subtitle) . '</h7>
                    </div>
                </a>
            </div>';
        }
        $output .= '</div></div>';
        return $output;
    } else {
        return 'No testimonial found with the provided ID.';
    }
}

