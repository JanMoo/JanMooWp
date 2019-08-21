<?php
/*
  Plugin Name: WP Like button
  Description: WP Like button allows you to add Facebook like button to your wordpress blog.
  Author: <a href="http://crudlab.com/">CRUDLab</a>
  Version: 1.6.6
 */
$CLFBLBPath = plugin_dir_path(__FILE__);

require_once $CLFBLBPath . 'CLLBtnSettings.php';

class CLFBLBtn {

    private $CLLBtnSettings = null;
    private $table_name = null;
    public static $table_name_s = 'fblb';
    private $db_version = '7';
    private $menuSlug = "facebook-like-button";
    private $settingsData = null;

    public function __construct() {

        register_activation_hook(__FILE__, array($this, 'fblb_install'));
        register_uninstall_hook(__FILE__, array('CLFBLBtn', 'fblb_uninstall_hook'));

        add_action('admin_menu', array($this, 'fblb_plugin_setup_menu'));
        $this->CLLBtnSettings = new CLLBtnSettings($this);
        $this->menuSlug = 'facebook-like-button';
        global $wpdb;
        $this->table_name = $wpdb->prefix . self::$table_name_s;
        $this->settingsData = $wpdb->get_row("SELECT * FROM $this->table_name WHERE id = 1");

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array($this, 'settingsLink'));

        add_filter('wp_head', array($this, 'fblb_header'));
        add_filter('the_content', array($this, 'fb_like_button'));
        //add_filter('the_excerpt', 'fb_like_button');
        add_shortcode('fblike', array($this, 'fb_like_button'));
    }

    function settingsLink($links) {
        $settings_link = '<a href="admin.php?page=' . $this->menuSlug . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public static function getTableName() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name_s;
    }

    public function getTable_name() {
        return $this->table_name;
    }

    public function getDb_version() {
        return $this->db_version;
    }

    public function setTable_name($table_name) {
        $this->table_name = $table_name;
    }

    public function setDb_version($db_version) {
        $this->db_version = $db_version;
    }

    public function getMenuSlug() {
        return $this->menuSlug;
    }

    public function setMenuSlug($menuSlug) {
        $this->menuSlug = $menuSlug;
    }

    public function getSettingsData() {
        return $this->settingsData;
    }

    public function reloadDBData() {
        global $wpdb;
        return $this->settingsData = $wpdb->get_row("SELECT * FROM $this->table_name WHERE id = 1");
    }
    
    function fblb_header() {
        if ($this->getSettingsData()->status != 0) {
            $fb_app_id = $this->getSettingsData()->fb_app_id;
            $fb_app_default_image = $this->getSettingsData()->default_image;
            $fb_app_admin = explode(",", $this->getSettingsData()->fb_app_admin);
            echo '<meta property="fb:app_id" content="' . $fb_app_id . '">';
            if ($fb_app_default_image != "" && $fb_app_default_image != null) {
                echo '<meta property="og:image" content="' . $fb_app_default_image . '" />';
            }
            foreach ($fb_app_admin as $admin_id) {
                echo '<meta property="fb:admins" content="' . $admin_id . '">';
            }
            ?>
            <div id="fb-root"></div>
            <script>(function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id))
                        return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = "//connect.facebook.net/<?php echo $this->getSettingsData()->language ?>/sdk.js#xfbml=1&version=v2.0";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>
            <?php
        }
    }

    function fblb_plugin_setup_menu() {
        if ($this->getSettingsData()->status == 0) {
            add_menu_page('WP like button', 'WP like button <span id="fblb_circ" class="update-plugins count-1" style="background:#F00"><span class="plugin-count">&nbsp&nbsp</span></span>', 'manage_options', $this->menuSlug, array($this, 'admin_settings'), plugins_url("/images/ico.png", __FILE__));
        } else {
            add_menu_page('WP like button', 'WP like button <span id="fblb_circ" class="update-plugins count-1" style="background:#0F0"><span class="plugin-count">&nbsp&nbsp</span></span>', 'manage_options', $this->menuSlug, array($this, 'admin_settings'), plugins_url("/images/ico.png", __FILE__));
        }
    }

    function admin_settings() {
        $this->CLLBtnSettings->registerJSCSS();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->CLLBtnSettings->validateData()) {
                $this->CLLBtnSettings->saveData();
            }
        }
        $this->CLLBtnSettings->renderPage();
    }

    function fblb_install() {
        global $wpdb;
        global $wpfblike_db_version;
        $table_name = $wpdb->prefix . 'fblb';
        $charset_collate = $wpdb->get_charset_collate();
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        // status: 1=active, 0 unactive
        // display: 1=all other page, 2= home page, 3=all pages
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    display int, 
                    width int,
                    beforeafter varchar (25),
                    except_ids varchar(255),
                    where_like varchar (50),
                    layout varchar (50),
                    action varchar (50),
                    color varchar (50),
                    btn_size varchar (50),
                    position varchar (50),
                    language varchar (50),
                    fb_app_id varchar (100),
                    fb_app_admin varchar (100),
                    url varchar (255),
                    default_image varchar (500),
                    status int, 
                    mobile int,
                    kid int,
                    user_id int,
                    active int,
                    share int,
                    faces int,
                    created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    last_modified datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    UNIQUE KEY id (id)
            ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
        add_option('wpfblike_db_version', $this->db_version);
        update_option('wpfblike_db_version', $this->db_version);
        if(get_option('crudlab_fblb_install') == false){
            add_option('crudlab_fblb_install', strtotime( "now" ));
        }else{
            update_option('crudlab_fblb_install', strtotime( "now" ));
        }
        $myrows = $wpdb->get_results("SELECT * FROM $this->table_name WHERE id = 1");
        if ($myrows == NULL) {
            $wpdb->insert($this->table_name, array(
                'created' => current_time('mysql'),
                'last_modified' => current_time('mysql'),
                'status' => 1,
                'display' => 3,
                'width' => 65,
                'except_ids' => '',
                'user_id' => $user_id,
                'active' => 1,
                'share' => 1,
                'faces' => 1,
                'mobile' => 1,
                'kid' => 0,
                'position' => 'center',
                'beforeafter' => 'before',
                'where_like' => 'eachpage',
                'layout' => 'box_count',
                'action' => 'like',
                'color' => 'light',
                'btn_size' => 'small',
                'language' => 'en_US',
                'url' => ''
            ));
        }
    }    

    public static function fblb_uninstall_hook() {
        global $wpdb;
        $tbl = self::getTableName();
        $wpdb->query("DROP TABLE IF EXISTS $tbl");
    }
    
    public function fb_like_button($content = NULL) {
        $post_id = get_the_ID();
        $settings = $this->getSettingsData();

        $beforeafter = $settings->beforeafter;
        $where_like = $settings->where_like;
        $status = $settings->status;
        $layout = $settings->layout;
        $action = $settings->action;
        $color = $settings->color;
        $btn_size = $settings->btn_size;
        $display = $settings->display;
        $except_ids = $settings->except_ids;
        $language = $settings->language;
        $url = $settings->url;
        $mobile = $settings->mobile;
        $kid = $settings->kid;
        $width = $settings->width;
        $str = $content;
        $share = $settings->share;
        $faces = $settings->faces;
        $position = $settings->position;
        if ($share == 1) {
            $share = 'true';
        } else {
            $share = 'false';
        }
        if ($faces == 1) {
            $faces = 'true';
        } else {
            $faces = 'false';
        }
        if ($kid == 1) {
            $kid = 'true';
        } else {
            $kid = 'false';
        }
        if ($where_like == 'eachpage') {
            $actual_link = get_permalink();
        } else if ($where_like == 'entiresite') {
            $actual_link = get_site_url();
        } else {
            $actual_link = $url;
        }
        if (!wp_is_mobile()) {
            $fb = '<style>.fb_iframe_widget span{width:460px !important;} .fb_iframe_widget iframe {margin: 0 !important;}        .fb_edge_comment_widget { display: none !important; }</style><div style="width:100%; text-align:' . $position . '"><div class="fb-like" style="width:' . $width . 'px; overflow: hidden !important; " data-href="' . $actual_link . '" data-size="' . $btn_size . '" data-colorscheme="' . $color . '" data-width="' . $width . '" data-layout="' . $layout . '" data-action="' . $action . '" data-show-faces="' . $faces . '" data-share="' . $share . '" kid_directed_site="' . $kid . '"></div></div>';
        } else if ($mobile && wp_is_mobile()) {
            $fb = '<style>.fb-like {overflow: hidden !important;}</style><div style="width:100%; text-align:' . $position . '"><div class="fb-like" style="width:' . $width . 'px" data-href="' . $actual_link . '" data-colorscheme="' . $color . '" data-size="' . $btn_size . '" data-width="' . $width . '" data-layout="' . $layout . '" data-action="' . $action . '" data-show-faces="' . $faces . '" data-share="' . $share . '" kid_directed_site="' . $kid . '"></div></div>
            <br>';
        }
        $width = $settings->width . 'px';
        if ($status == 0) {
            $str = $content;
        } else {
            if ($content == NULL) {
                $str = $fb;
            }
            if ($display & 2) {
                if (is_page() && !is_front_page()) {
                    if ($beforeafter == 'before') {
                        $str = $fb . $content;
                    } else {
                        $str = $content . $fb;
                    }
                }
            }
            if ($display & 1) {
                if (is_front_page()) {
                    if ($beforeafter == 'before') {
                        $str = $fb . $content;
                    } else {
                        $str = $content . $fb;
                    }
                }
            }
            if ($display & 4) {
                if (is_single()) {
                    if ($beforeafter == 'before') {
                        $str = $fb . $content;
                    } else {
                        $str = $content . $fb;
                    }
                }
            }
            if ($display & 16) {
                if (is_archive()) {
                    if ($beforeafter == 'before') {
                        $str = $fb . $content;
                    } else {
                        $str = $content . $fb;
                    }
                }
            }
        }
        $except_check = true;
        if ($display & 8) {
            @$expect_ids_arrays = explode(',', $except_ids);
            foreach ($expect_ids_arrays as $id) {
                if (trim($id) == $post_id) {
                    $except_check = false;
                }
            }
        }
        if ($except_check) {
            return $str;
        } else {
            return $content;
        }
    }
}

global $wpfblbtn;
$wpfblbtn = new CLFBLBtn();

function fb_like_button() {
    global $wpfblbtn;
    echo $wpfblbtn->fb_like_button();
}
