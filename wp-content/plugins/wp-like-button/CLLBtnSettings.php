<?php
if (!defined('ABSPATH'))
    exit;

class CLLBtnSettings {

    /**
     *
     * @var CLFBLBtn
     */
    private $parrent = null;

    public function __construct(CLFBLBtn &$parrent) {
        $this->parrent = $parrent;
        add_action('wp_ajax_getpostpages', array($this, 'getExcludesPages'));
        add_action('wp_ajax_cllbactive', array($this, 'activePlugin'));
    }

    public function getExcludesPages() {
        $data = array();
        $args = array(
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        $posts = get_posts($args);
        foreach ($posts as $post) {
            $data[] = array('id' => $post->ID, 'name' => $post->post_title);
        }

        echo json_encode($data);
        wp_die();
    }

    public function activePlugin() {
        global $wpdb;
        $wpdb->update($this->parrent->getTable_name(), array('status' => (isset($_REQUEST['fblb_switchonoff']) && $_REQUEST['fblb_switchonoff'] == 1) ? 1 : 0), array('id' => 1));
        $this->parrent->reloadDBData();

        echo json_encode(array('status' => $this->parrent->getSettingsData()->status));
        wp_die();
    }

    public function addJSCSS() {
        add_action('wp_enqueue_scripts', array($this, 'registerJSCSS'));
    }

    private function getLayout() {
        return array(
            'standard' => 'standard',
            'box_count' => 'box_count',
            'button_count' => 'button_count',
            'button' => 'button'
        );
    }
    
    private function getAction() {
        return array(
            'like' => 'like',
            'recommend' => 'recommend'
        );
    }
    private function getColor() {
        return array(
            'light' => 'light',
            'dark' => 'dark'
        );
    }
    private function getButtonSize() {
        return array(
            'small' => 'small',
            'large' => 'large'
        );
    }
    
    private function getLang() {
        return array(
            'Afrikaans' => 'af',
            'Amharic' => 'am',
            'Arabic' => 'ar',
            'Basque' => 'eu',
            'Bengali' => 'bn',
            'Bulgarian' => 'bg',
            'Catalan' => 'ca',
            'Chinese (Hong Kong)' => 'zh-HK',
            'Chinese (Simplified)' => 'zh-CN',
            'Chinese (Traditional)' => 'zh-TW',
            'Croatian' => 'hr',
            'Czech' => 'cs',
            'Danish' => 'da',
            'Dutch' => 'nl',
            'English (UK)' => 'en-GB',
            'English (US)' => 'en-US',
            'Estonian' => 'et',
            'Filipino' => 'fil',
            'Finnish' => 'fi',
            'French' => 'fr',
            'French (Canadian)' => 'fr-CA',
            'Galician' => 'gl',
            'German' => 'de',
            'Greek' => 'el',
            'Gujarati' => 'gu',
            'Hebrew' => 'iw',
            'Hindi' => 'hi',
            'Hungarian' => 'hu',
            'Icelandic' => 'is',
            'Indonesian' => 'id',
            'Italian' => 'it',
            'Japanese' => 'ja',
            'Kannada' => 'kn',
            'Korean' => 'ko',
            'Latvian' => 'lv',
            'Lithuanian' => 'lt',
            'Malay' => 'ms',
            'Malayalam' => 'ml',
            'Marathi' => 'mr',
            'Norwegian' => 'no',
            'Persian' => 'fa',
            'Polish' => 'pl',
            'Portuguese (Brazil)' => 'pt-BR',
            'Portuguese (Portugal)' => 'pt-PT',
            'Romanian' => 'ro',
            'Russian' => 'ru',
            'Serbian' => 'sr',
            'Slovak' => 'sk',
            'Slovenian' => 'sl',
            'Spanish' => 'es',
            'Spanish (Latin America)' => 'es-419',
            'Swahili' => 'sw',
            'Swedish' => 'sv',
            'Tamil' => 'ta',
            'Telugu' => 'te',
            'Thai' => 'th',
            'Turkish' => 'tr',
            'Ukrainian' => 'uk',
            'Urdu' => 'ur',
            'Vietnamese' => 'vi',
            'Zulu' => 'zu'
        );
    }

    public function registerJSCSS() {
        wp_enqueue_media();
        wp_register_script('my-admin-js', plugins_url('/js/custom.js', __FILE__), array('jquery'));
        wp_enqueue_script('my-admin-js');
        wp_register_script('fblb_magicsuggest', plugins_url('/js/magicsuggest-min.js', __FILE__), array('jquery'));
        wp_enqueue_script('fblb_magicsuggest');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_register_style('fblb_css', plugins_url('/css/fblb_style.css', __FILE__));
        wp_enqueue_style('fblb_css');
        wp_register_style('fblb_magicsuggest-min', plugins_url('/css/magicsuggest-min.css', __FILE__));
        wp_enqueue_style('fblb_magicsuggest-min');
        wp_register_style('fblb_jquery-ui', plugins_url('/css/jquery-ui.css', __FILE__));
        wp_enqueue_style('fblb_jquery-ui');        
    }

    public function validateData() {
        if(isset($_POST['display']) && is_array($_POST['display'])){
            foreach ($_POST['display'] as $d) {
                if(!in_array(intval($d), array(1,2,4,16))){
                    $msg = "Invalid display value!";
                    return false;
                }
            }
        }
        if(isset($_POST['width']) && (intval($_POST['width']) < 1 || intval($_POST['width']) > 2000)){
            $msg = "Width is Invalid!";
            return false;
        }
        if(isset($_POST['beforeafter']) && !in_array($_POST['beforeafter'], array('before', 'after'))){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['except_ids']) && is_array($_POST['except_ids'])){
            foreach($except_ids as $ei){
                if(intval($msg) < 1){
                    $msg = "Value is Invalid!";
                    return false;
                }
            }
        }
        if(isset($_POST['eachpage']) && !in_array($_POST['eachpage'], array('eachpage', 'entiresite', 'url'))){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['layout']) && !in_array($_POST['layout'], $this->getLayout())){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['action']) && !in_array($_POST['action'], $this->getAction())){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['color']) && !in_array($_POST['color'], $this->getColor())){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['btn_size']) && !in_array($_POST['btn_size'], $this->getButtonSize())){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['language']) && !in_array($_POST['language'], $this->getLang())){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['position']) && !in_array($_POST['position'], array('left', 'center', 'right'))){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['mobile']) && !in_array(intval($_POST['mobile']), array(0, 1))){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['kid']) && !in_array(intval($_POST['kid']), array(0, 1))){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['share']) && !in_array(intval($_POST['share']), array(0, 1))){
            $msg = "Value is Invalid!";
            return false;
        }
        if(isset($_POST['faces']) && !in_array(intval($_POST['faces']), array(0, 1))){
            $msg = "Value is Invalid!";
            return false;
        }
        return true;
    }

    private function contains($needle, $haystack) {
        return strpos($needle, $haystack) !== false;
    }
    
    public function saveData() {
        $display_val = 0;
        // $_POST['display'] has array value so it is iterated to sanitize its each value. 
        if (isset($_POST['display']) && is_array($_POST['display'])) {
            foreach ($_POST['display'] as $d) {
                $display_val += intval(sanitize_text_field($d));
            }
        }

        // $_POST['except_ids'] has array value so it is iterated to sanitize its each value. 
        $except_ids_arr = array();
        $except_ids = "";
        if (isset($_POST['except_ids']) && is_array($_POST['except_ids']) && $_POST['except_ids'] != NULL) {
            foreach ($_POST['except_ids'] as $ei) {
                $except_ids_arr[] = intval(sanitize_text_field($ei));
            }
            $except_ids = implode(',', $except_ids_arr);
        }
        
        $each_page_url = sanitize_text_field($_POST['each_page_url']);
        
        if ($each_page_url != NULL) {
            if (!$this->contains($each_page_url, 'http://')) {
                if (!$this->contains($each_page_url, 'https://')) {
                    $each_page_url = 'http://' . $each_page_url;
                }
            }
        }
        
        $data1 = array(
            'display' => $display_val,
            'width' => intval(sanitize_text_field($_POST['width'])) ? intval(sanitize_text_field($_POST['width'])) : null,
            'beforeafter' => sanitize_text_field($_POST['beforeafter']),
            'except_ids' => sanitize_text_field($except_ids),
            'where_like' => sanitize_text_field($_POST['eachpage']),
            'layout' => sanitize_text_field($_POST['layout']),
            'action' => sanitize_text_field($_POST['action']),
            'color' => sanitize_text_field($_POST['color']),
            'btn_size' => sanitize_text_field($_POST['btn_size']),
            'language' => sanitize_text_field($_POST['language']),
            'url' => $each_page_url,
            'user_id' => 0,
            'position' => sanitize_text_field($_POST['position']),
            'mobile' => sanitize_text_field($_POST['mobile']),
            'kid' => sanitize_text_field($_POST['kid']),
            'active' => 1,
            'share' => sanitize_text_field($_POST['share']),
            'faces' => sanitize_text_field($_POST['faces']),
            'default_image' => sanitize_text_field($_POST['fblb_default_upload_image']),
            'fb_app_id' => sanitize_text_field($_POST['fb_app_id']),
            'fb_app_admin' => sanitize_text_field($_POST['fb_app_admin']),
            'last_modified' => current_time('mysql')
        );
        global $wpdb;
        $wpdb->update($this->parrent->getTable_name(), $data1, array('id' => 1));
        $this->parrent->reloadDBData();
    }

    public function renderPage() {
        $obj = $this->parrent->getSettingsData();
        ?>
    <div id="test-popup" class="fblb_white-popup fblb_mfp-with-anim fblb_ mfp-hide"></div>
    <div class="fblb_container">
        <div class="fblb_row">
            <div class="fblb_plugin-wrap fblb_col-md-12">
                <div class="fblb_plugin-notify">
                    <div class="fblb_forms-wrap">
                        <div class="fblb_colmain">
                            <div class="fblb_what">
                                <div class="fblb_form-types-wrap">
                                    <input type="hidden" name="fblb" value="<?php echo $notify; ?>">
                                    <div class="fblb_clearfix"></div>
                                </div>
                            </div>
                        </div>
                        <div class="fblb_col" style="width:67%; ">
                            <div class="fblb_where">
                                <form class="fblb_inline-form fblb_form-inline" method="post">
                                    <input type="hidden" name="site_url" value="<?php echo get_site_url(); ?>" id="site_url">
                                    <div class="fblb_control-group">
                                        <label class="fblb_control-label">Settings</label>
                                        <table border="0" width="100%">
                                            <tr>
                                                <td style="width: 160px; vertical-align: top;text-align: right; padding-right: 15px;">
                                                    <label style="margin-top:8px;">Where to display? </label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">
                                                        <input type="checkbox" id="display1" name="display[]" <?php echo ($obj->display & 1) ? 'checked' : ''; ?> value="1" class="fblb_form-control fblb_check" style="float:left"><label for="display1">Homepage</label>
                                                        <input type="checkbox" id="display2" name="display[]" <?php echo ($obj->display & 2) ? 'checked' : ''; ?> value="2" class="fblb_form-control fblb_check" style="float:left"><label for="display2">All pages</label>
                                                        <input type="checkbox" id="display4" name="display[]" <?php echo ($obj->display & 4) ? 'checked' : ''; ?> value="4" class="fblb_form-control fblb_check" style="float:left"><label for="display4">All posts</label>
                                                        <input type="checkbox" id="display16" name="display[]" <?php echo ($obj->display & 16) ? 'checked' : ''; ?> value="16" class="fblb_form-control fblb_check" style="float:left"><label for="display16">All archive pages</label>
                                                        <input type="checkbox" id="display8" onchange="if (this.checked) {
                                                                    jQuery('.fblb_exclude').show(200)
                                                                } else {
                                                                    jQuery('.fblb_exclude').hide(200)
                                                                }" name="display[]" <?php echo @$display['8']; ?> value="8" class="fblb_form-control fblb_check" style="float:left"><label for="display8">Exclude specific pages and posts</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="vertical-align: top;width: 160px; padding-top: 10px;text-align: right; padding-right: 15px;">
                                                    <label class="fblb_exclude" style="display:<?php
                                                if ($obj->display & 8) {
                                                    echo 'block';
                                                } else {
                                                    echo 'none';
                                                }
                                                ?>">Exclude Page/Post</label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group fblb_exclude" style="display:<?php
                                                if ($obj->display & 8) {
                                                    echo 'block';
                                                } else {
                                                    echo 'none';
                                                }
                                                ?>">
                                                        <div id="magicsuggest" value="[<?php echo $obj->except_ids; ?>]" name="except_ids[]" style="width:auto !important; background: #fff; border: thin solid #cccccc;"></div>
                                                    </div>
                                                    <span class="magic-suggest-alert" style="color:#f00; display: none;">Please don’t forget to press the Save Settings button.</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <label style="margin-top:8px;">Enable like button for mobile (Responsive layouts) </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="width: 160px; vertical-align: top;text-align: right; padding-right: 15px;">
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group fblb_beforeafter">
                                                        <input type="radio" id="mobile0" name="mobile" <?php echo ($obj->mobile == 0) ? 'checked' : ''; ?> value="0" class="fblb_form-control" style="float:left"><label style="float:left" for="mobile0">No</label>
                                                        <input type="radio" id="mobile1" name="mobile" <?php echo ($obj->mobile == 1) ? 'checked' : ''; ?> value="1" class="fblb_form-control" style="float:left"><label style="float:left" for="mobile1">Yes</label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="2">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr class="fblb_manual">
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>App ID </label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">   
                                                        <input type="text" placeholder="" onblur="cfb()" name="fb_app_id" id="fb_app_id"  value="<?php echo @$obj->fb_app_id; ?>" class="fblb_form-control" style="width:40%; float: left; margin-top: -10px; margin-right: 10px;">
                                                        <a href="http://developers.facebook.com/setup/" target="_blank" title="Register Your Site on Facebook">Create new App ID</a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="fblb_manual">
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Admin ID </label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">   
                                                        <input type="text" placeholder="" onblur="cfb()" name="fb_app_admin" id="fb_app_admin"  value="<?php echo @$obj->fb_app_admin; ?>" class="fblb_form-control" style="width:40%; float: left; margin-top: -10px; margin-right: 10px;">
                                                        <label>User ID or Username who has access to insights.(Comma separated)</label>
                                                    </div>
                                                </td>
                                            </tr>                                            
                                            <tr class="fblb_manual">
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Kid Directed Site?</label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">   
                                                        <div class="fblb_form-group fblb_beforeafter">
                                                            <input type="radio" id="kid0" name="kid" <?php echo ($obj->kid == 0) ? 'checked' : ''; ?> value="0" class="fblb_form-control" style="float:left"><label style="float:left" for="kid0">No</label>
                                                            <input type="radio" id="kid1" name="kid" <?php echo ($obj->kid == 1) ? 'checked' : ''; ?> value="1" class="fblb_form-control" style="float:left"><label style="float:left" for="kid1">Yes</label>
                                                            <img src="<?php echo plugins_url("/images/help.png", __FILE__) ?>" style="float:left; margin-top: -10px" help_kid="" title="help_kid">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>                                       
                                            <tr class="fblb_manual">
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Default Image</label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">   
                                                        <?php
                                                        $image_preview_status = "";
                                                        if ($obj->default_image == null || $obj->default_image == "") {
                                                            $image_preview_status = "display:none;";
                                                        }
                                                        ?>
                                                        <img src="<?php echo $obj->default_image; ?>" style="max-height:70px;<?php echo $image_preview_status; ?>" id="fblb_default_image_preview"> &nbsp;<a href="javascript://void()" onclick="jQuery('#fblb_default_upload_image').val('');
                                                                jQuery('#fblb_default_image_preview').hide();
                                                                jQuery(this).hide();" id="fblb_default_image_preview_remove" style="<?php echo $image_preview_status; ?>">Remove</a><br>
                                                        <input type="hidden" name="fblb_default_upload_image" id="fblb_default_upload_image" value="<?php echo @$obj->default_image; ?>">
                                                        <a href="#" id="fblbutton_default_upload_image_button">Select/Upload Image</a> Default image that will show on Facebook if article/post doesn't have thumbnail, leave empty to use post thumbnail.
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="2">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr class="fblb_manual">
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Shortcode </label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">   
                                                        Use shortcode <input style="width:80px;" type="text" value="[fblike]" onClick="this.setSelectionRange(0, this.value.length);"> to display like button
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="fblb_manual">
                                                <td style="width: 160px; text-align: right; padding-right: 15px; vertical-align: top;">
                                                    <label style="margin-top:10px;">Code Snippet </label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">
                                                        <span>
                                                            Also, you can use following code and paste it in theme files.<br>
                                                            For instance, add following code to header or footer.php to display like button
                                                        </span>
                                                        <input type="text"  onClick="this.setSelectionRange(0, this.value.length);" name="code_snippet" value="<?php echo("<?php echo fb_like_button(); ?>"); ?>" class="fblb_form-control">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px;">&nbsp;</td>
                                                <td>
                                                    <div class="fblb_form-group fblb_beforeafter">
                                                        <input type="radio" id="before" name="beforeafter" <?php echo ($obj->beforeafter == 'before') ? 'checked' : ''; ?> value="before" class="fblb_form-control" style="float:left"><label style="float:left" for="before">Before</label>
                                                        <input type="radio" id="after" name="beforeafter" <?php echo ($obj->beforeafter == 'after') ? 'checked' : ''; ?> value="after" class="fblb_form-control" style="float:left"><label style="float:left" for="after">After</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px; padding-top: 11px; vertical-align: top;text-align: right; padding-right: 15px;">
                                                    <label>What to like?</label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">
                                                        <table class="fblb_eachpage">
                                                            <tr>
                                                                <td>
                                                                    <input onchange="cfb();" type="radio" id="eachpage" name="eachpage" <?php echo ($obj->where_like == 'eachpage') ? 'checked' : ''; ?> value="eachpage" class="fblb_form-control" style="float:left"><label style="float:left; font-weight: normal;" for="eachpage">Each page/post will have its own like button</label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <input onchange="cfb();" type="radio" id="entiresite" name="eachpage" <?php echo ($obj->where_like == 'entiresite') ? 'checked' : ''; ?> value="entiresite" class="fblb_form-control" style="float:left"><label style="float:left; font-weight: normal;" for="entiresite">Entire Site</label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <input onchange="cfb();" type="radio" id="url" name="eachpage" <?php echo ($obj->where_like == 'url') ? 'checked' : ''; ?> value="url" class="fblb_form-control" style="float:left"><label style="float:left" > <input type="text" placeholder="URL to Like Example - https://facebook.com/wordpress " onblur="cfb()" name="each_page_url" id="url_text"  value="<?php echo $obj->url; ?>" class="fblb_form-control"></label>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>                                                                        
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Language</label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">
                                                        <select class="form-control fblb_form-control select select-default" name="language" data-toggle="select">
                                                            <?php foreach ($this->getLang() as $key => $value) { ?>
                                                                <option <?php echo ($value == $obj->language) ? 'selected="selected"' : ''; ?> value="<?php echo $value ?>"><?php echo $key; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;">
                                                    <label>Width</label>
                                                </td>
                                                <td>
                                                    <input onblur="if (!isNumeric(this.value)) {
                                                                alert('Only digits allowed');
                                                                this.focus();
                                                            }" type="text" id="width" placeholder="" style="width:93%; float:left" name="width" value="<?php echo $obj->width; ?>" class="fblb_form-control">
                                                    <img src="<?php echo plugins_url("/images/help.png", __FILE__) ?>" style="float:right" help="" title="help">
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="width: 160px; text-align: right; padding-right: 15px;"><label>Position</label></td>
                                                <td>
                                                    <div class="fblb_form-group fblb_beforeafter">
                                                        <input onchange="cfb();" type="radio" id="left" name="position" <?php echo ($obj->position == 'left') ? 'checked' : ''; ?> value="left" class="fblb_form-control" style="float:left;font-weight: normal"><label style="float:left;font-weight: normal" for="left">Left</label>
                                                        <input onchange="cfb();" type="radio" id="middle" name="position" <?php echo ($obj->position == 'center') ? 'checked' : ''; ?> value="center" class="fblb_form-control" style="float:left;font-weight: normal"><label style="float:left;font-weight: normal" for="middle">Center</label>
                                                        <input onchange="cfb();" type="radio" id="right" name="position" <?php echo ($obj->position == 'right') ? 'checked' : ''; ?> value="right" class="fblb_form-control" style="float:left"><label style="float:left;font-weight: normal" for="right">Right</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <table width="100%">
                                            <tr>
                                                <td colspan="2">
                                                    <label class="fblb_control-label">Preview</label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label style="padding-right:15px; float: right">Layout</label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">
                                                        <select class="fblb_form-control" name="layout" id="layout" onchange="cfb();">
                                                            <?php foreach ($this->getLayout() as $key => $value) { ?>
                                                                <option <?php echo ($value == $obj->layout) ? 'selected="selected"' : ''; ?> value="<?php echo $value ?>"><?php echo $key; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label style="padding-right:15px; float: right">Action Type</label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">
                                                        <select class="fblb_form-control" name="action" id="action" onchange="cfb();">
                                                            <?php foreach ($this->getAction() as $key => $value) { ?>
                                                                <option <?php echo ($value == $obj->action) ? 'selected="selected"' : ''; ?> value="<?php echo $value ?>"><?php echo $key; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <label style="padding-right:15px; float: right">Color</label>
                                                </td>
                                                <td>
                                                    <div class="fblb_form-group">
                                                        <select class="fblb_form-control" name="color" id="color" onchange="cfb();">
                                                            <?php foreach ($this->getColor() as $key => $value) { ?>
                                                                <option <?php echo ($value == $obj->color) ? 'selected="selected"' : ''; ?> value="<?php echo $value ?>"><?php echo $key; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><label style="padding-right:15px; float: right">Button Size</label></td>
                                                <td>
                                                    <div class="fblb_form-group">
                                                        <select class="fblb_form-control" name="btn_size" id="btn_size" onchange="cfb();">
                                                            <?php foreach ($this->getButtonSize() as $key => $value) { ?>
                                                                <option <?php echo ($value == $obj->action) ? 'selected="selected"' : ''; ?> value="<?php echo $value ?>"><?php echo $key; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <div class="fblb_form-group" id="wpfblikebutton_faces" style="text-align: center;">
                                                        <input onchange=" cfb()" <?php echo ($obj->faces)?'checked':''; ?> type="checkbox" style="float:left" value="1" name="faces" id="faces"><label style="float:left; line-height: 10px; padding-left: 10px;" for="faces">Show Friends' Faces</label>
                                                    </div>
                                                </td>
                                                <td></td>
                                                <td> 
                                                    <div class="fblb_form-group" style="text-align: center">
                                                        <input onchange=" cfb()" <?php echo ($obj->share)?'checked':''; ?>  type="checkbox" style="float:left" value="1" name="share" id="share"><label style="float:left; line-height: 10px; padding-left: 10px;" for="share">Include Share Button</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">
                                                    <div id="u_0_18" class="fblb_preview" style="text-align: <?php echo $obj->position; ?>"></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><button type="submit" name="update_fblb" class="fblb_btn fblb_btn-primary">Save Settings</button></td>
                                                <td colspan="2">
                                                    <div class="fblb_form-group fblb_switch" style="float: right;">
                                                    <?php
                                                    $img = '';
                                                    if ($obj->status == 0) {
                                                        $img = 'off.png';
                                                    } else {
                                                        $img = 'on.png';
                                                    }
                                                    ?>
                                                        <img onclick="fblb_switchonoff(this)" src="<?php echo plugins_url('/images/' . $img, __FILE__); ?>"> 
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" style="text-align: left;">If you enjoy our plugin, please give it 5 stars. [<a href="https://wordpress.org/support/view/plugin-reviews/wp-like-button" target="_blank">Rate the plugin</a>] .</td>    

                                            </tr>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="fblb_col fblb_col-adv" style="width:25%;">
                            <div class="fblb_where">
                                <h2 style="text-align:left; line-height: 28px;">   
                                    <a href="http://crudlab.com" target="_blank">CRUDLab</a> has following plugins for you:
                                </h2>
                                <hr>

                                <div>
                                    <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                                        CRUDLab Facebook Like Box
                                    </div>
                                    <div style="margin-top:10px; margin-bottom: 8px;">
                                        CRUDLab Facebook Like Box allows you to add Facebook like box to your wordpress blog. It allows webmasters to promote their Pages and embed a simple feed of content from a Page into their WordPress sites.
                                    </div>
                                    <div style="text-align: center;">
                                        <a href="https://wordpress.org/plugins/crudlab-facebook-like-box/" target="_blank" class="fblb_btn fblb_btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                                    </div>
                                </div>
                                <hr>
                                <div>
                                    <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                                        Jazz Popups
                                    </div>
                                    <div style="margin-top:10px; margin-bottom: 8px;">
                                        Jazz Popups allow you to add special announcement, message or offers in form of text, image and video.
                                    </div>
                                    <div style="text-align: center;">
                                        <a href="https://wordpress.org/plugins/jazz-popups/" target="_blank" class="fblb_btn fblb_btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                                    </div>
                                </div>
                                <hr>
                                <div>
                                    <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                                        WP Tweet Plus
                                    </div>
                                    <div style="margin-top:10px; margin-bottom: 8px;">
                                        WP Tweet Plus allows you to add tweet button on your wordpress blog. You can add tweet Button homepage, specific pages and posts.
                                    </div>
                                    <div style="text-align: center;">
                                        <a href="https://wordpress.org/plugins/wp-tweet-plus/" target="_blank" class="fblb_btn fblb_btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                                    </div>
                                </div>
                            </div>
                            <div class="fblb_where" style="margin-top:15px;">
                                <span>
                                    Your donation helps us make great products
                                </span>
                                <a href="https://www.paypal.com/cgi-bin/webscr?business=billing@purelogics.net&cmd=_xclick" target="_blank">
                                    <img style="width:100%;" src="<?php echo plugins_url('/images/donate.png', __FILE__); ?>">
                                </a>
                            </div>
                        </div>
                        <div class="fblb_col fblb_col-adv">

                        </div>
                        <div class="fblb_clearfix"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
        <?php
    }

}
