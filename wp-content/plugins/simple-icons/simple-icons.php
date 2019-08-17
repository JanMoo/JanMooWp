<?php

/*
Plugin Name: Popular Brand SVG Icons - Simple Icons
Plugin URI: https://simpleicons.org/
Description: An easy to use SVG icons plugin with over 500+ brand icons. Use these icons in your menus, widgets, posts, or pages.
Version: 2.3.0
Author: Travis Taylor
Author URI: https://webdesignyorkpa.com/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// global vars
define ( 'SIMPLE_ICONS_VERSION', '2.3.0');
define ( 'SIMPLE_ICONS_DEBUG', false );  

include( plugin_dir_path( __FILE__ ) . 'inc/welcome-screen.php');

// For debugging purposes (clear cache)
// wp_cache_flush();
// delete_transient('simpleicons_json');
// delete_transient('simpleicons_version');

// function debug($data) {
// 	echo '<pre>';
// 		print_r($data);
// 	echo '</pre>';
// }


// trigger shortcodes in custom HTML widgets
add_filter( 'widget_text', 'do_shortcode' );

function simpleicons_debug($message) {
	if (SIMPLE_ICONS_DEBUG === true) {
		echo '<h2>' . $message . '</h2>';
	}
}


function simpleicons_css() {
    ?>
        <style>
            span[class*="simple-icon-"] {
            	width: 1.5rem;
            	height: 1.5rem;
            	display: inline-block;

            }
            span[class*="simple-icon-"] svg {
            	display: inline-block;
            	vertical-align: middle;
                height: inherit;
                width: inherit;
            }
        </style>
    <?php
}
add_action('wp_head', 'simpleicons_css');
add_action('admin_head', 'simpleicons_css');


class SimpleIcons {
	public static function display_icon($data) {
		$slug = self::slugify_name($data['name']);
		$icon = self::get_icon($data['name'], $data['cache']);

		if ($icon) {
			// vars
			$color = $data['color'] ? $data['color'] : '#' . $icon->hex;
			$class = ($data['class']) ? ' ' . $data['class'] : '';

			// build the output
			$output = '<span class="simple-icon-' . $slug . $class . '"';
				$output .= ' style="fill:' . $color . ';';
				$output .= $data['size'] ? ' height:' . $data['size'] . '; width:' . $data['size'] . ';' : '';
				$output .= '">';
					$output .= $icon->svg;
			$output .= '</span>';
			return $output;
		} else {
			// no icon found
			return false;
		}
	}

	private static function get_icon($name, $cache = true) {
		$slug = self::slugify_name($name);
		$icon = get_transient( 'simpleicons_icon_' . $slug);
		$ver_changed = self::version_changed();

		// check if the icon is cached, or if the plugin version has changed
		if ( false === $icon || $ver_changed ) {
			$all_icons_data = self::get_all_icons();

			// check if the icon exists in the icon array
			if ( !empty( $all_icons_data ) && array_key_exists($slug, $all_icons_data)) {
				$icon = $all_icons_data[$slug];
				// cache the icon data and version number
				if ($cache === true) {
					set_transient( 'simpleicons_icon_' . $slug, $icon  ); // cache icon permanently
				}

				return $icon;
			} else {
				// icon does not exist

				simpleicons_debug('Icon not in the array');

				return false;
			}
		} else {
			// icon was previously cached, return it from the cache
			return $icon;
		}
	}

	private static function version_changed() {
		$version = get_transient( 'simpleicons_version' );
		$ver_has_changed = wp_cache_get('simpleicons_version_changed');

		if ($version === false) {
			// version transient never set (occurs first time loading the plugin)
			set_transient( 'simpleicons_version', SIMPLE_ICONS_VERSION  ); 
			simpleicons_debug('Version Transient set (occurs first time loading the plugin)');
			return true;
		} else {
			if ($version != SIMPLE_ICONS_VERSION || $ver_has_changed) {
				// version has changed, update transient and cache
				set_transient( 'simpleicons_version', SIMPLE_ICONS_VERSION  ); 
				wp_cache_set('simpleicons_version_changed', true);

				simpleicons_debug('Version has changed');
				return true;
			} else {
				// version did not changed
				return false;
			}
		}
	}

	public static function get_placeholder_icon($name) {
		// check if name contains a #
		if (strpos($name, '#') !== false) {
			return self::get_icon($name);
		} else {
			return false;
		}
	}

	public static function get_all_icons() {
		$plugin_path = plugin_dir_path( __FILE__ );
		$all_icons_data = wp_cache_get('simpleicons_all_icons_data');

		simpleicons_debug('Icon not cached, version change, or backend loaded');

		// check to see if the json file has already been cached during this php page load, or if the plugin version has changed
		if ( false === $all_icons_data ) {
			$json_url = $plugin_path . "icons.json";
			$json = file_get_contents($json_url);
			$all_icons_data = json_decode($json);

			// slugify all keys
			$all_icons_data_modified = array();
			foreach ($all_icons_data as $k => $v) {
				$all_icons_data_modified[self::slugify_name($k)] = $v;
			}
			$all_icons_data = $all_icons_data_modified;

			if( !empty( $all_icons_data ) ) {
				// cache the json data
				wp_cache_add( 'simpleicons_all_icons_data', $all_icons_data);

				simpleicons_debug('JSON file cached (This message should appear one time on the front end if an icon was not cached)');
			}				
		}

		return $all_icons_data;
	}

	// slugify name to allow easier names used as shortcode or placeholders
	// ie: user can use WordPress, Wordpress, or wordpress
	private static function slugify_name($name) {
		// make name lowercase and remove spaces and dots
		$name = str_replace(array(' ', '.', '#'), '', strtolower($name));
		$name = str_replace('+', 'plus', $name);
		return $name;
	}
}


function simpleicons_shortcode_func($atts) {
    $a = shortcode_atts( array(
        'name' => null,
        'color' => null,
        'size' => null,
        'class' => null,
        'cache' => true
    ), $atts );

    if (isset($a['name'])) {
		return SimpleIcons::display_icon($a);  
    } else {
    	return false;
    }
}
add_shortcode( 'simple_icon', 'simpleicons_shortcode_func' );


// Filters all menu item titles for a #placeholder# 
function simpleicons_menu_items( $menu_items ) {
    foreach ( $menu_items as $menu_item ) {
    	// check if placeholder exists
        if ( $icon = SimpleIcons::get_placeholder_icon($menu_item->title) ) {
        	// get all shortcodes
            global $shortcode_tags;

            if ( array_key_exists( 'simple_icon', $shortcode_tags ) ) {
            	// call the shortcode within the title
                $menu_item->title = call_user_func( 
                    $shortcode_tags['simple_icon'], 
                	array(
                		'name' => $icon->title
                	)
                );
                // add a simple-icon class to the menu-item
                array_push($menu_item->classes, 'simple-icon');
            }
        }

    }
    return $menu_items;
}
add_filter( 'wp_nav_menu_objects', 'simpleicons_menu_items' );


 
function simpleicons_setup_menu(){
	add_options_page( 'Popular Brand SVG Icons - Simple Icons', 'Simple Icons', 'edit_posts', 'simpleicons', 'simpleicons_admin_page_init' );
}
add_action('admin_menu', 'simpleicons_setup_menu');


function simpleicons_admin_page_init(){
	$copy_icon = '<svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M433.941 65.941l-51.882-51.882A48 48 0 0 0 348.118 0H176c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h224c26.51 0 48-21.49 48-48v-48h80c26.51 0 48-21.49 48-48V99.882a48 48 0 0 0-14.059-33.941zM266 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h74v224c0 26.51 21.49 48 48 48h96v42a6 6 0 0 1-6 6zm128-96H182a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h106v88c0 13.255 10.745 24 24 24h88v202a6 6 0 0 1-6 6zm6-256h-64V48h9.632c1.591 0 3.117.632 4.243 1.757l48.368 48.368a6 6 0 0 1 1.757 4.243V112z"></path></svg>';
	
	echo '<div class="wrap">';
		echo '<div style="display:flex; justify-content:space-between; align-items: center;">';
			echo '<h1>Popular Brand SVG Icons - Simple Icons</h1>';
			echo '<p>Like this plugin? Please <a href="https://wordpress.org/support/plugin/simple-icons/reviews/#new-post" target="_blank">leave us a review!</a></p>';
		echo '</div>';
		echo '<hr class="wp-header-end">';
		echo '<div class="card" style="max-width:100%">';
			echo '<h2 class="title">Shortcode Generator</h2>';
			echo '<p>Select an icon below to generate your icon shortcode or menu text. <a target="_blank" href="https://wordpress.org/plugins/simple-icons/#description" title="Simple Icons">View documentation</a></p>';
		
			echo '<pre><strong id="shortcode-preview">[simple_icon name="wordpress"]</strong> <span id="copy-shortcode" class="copy-icon">' . $copy_icon . '</span> <span id="copy-shortcode-msg" class="copy-msg"></span></pre>';
			echo '<pre><strong id="placeholder-preview">#wordpress#</strong> <span id="copy-placeholder" class="copy-icon">' . $copy_icon . '</span> <span id="copy-placeholder-msg" class="copy-msg"></span></pre>';
			echo '<div id="icon-preview"></div>';
		echo '</div>';

		echo '<div class="simple-icons-search-wrapper">';
			echo '<input type="text" id="simple-icons-search" placeholder="Search by brand..." />';
		echo '</div>';
		echo '<hr>';

    	$icons = SimpleIcons::get_all_icons();

    	// debug($icons);

    	echo '<div class="simpleicons-list-wrapper">';
	    	echo '<ul class="simpleicons-list">';
	    	foreach ($icons as $slug => $icon) {
	    		echo '<li data-icontitle="' . $slug . '">';
	    			echo do_shortcode('[simple_icon name="' . $icon->title . '" size="30px" cache=false]');
	    		echo '</li>';
	    	}
	    	echo '</ul>';
	    echo '</div>';

	echo '</div>';
}

function simpleicons_admin_page_css() {
	$screen = get_current_screen();

	if ($screen->id === 'settings_page_simpleicons') :
	    ?>
	        <style>
				.card {
					max-width: 100%;
					min-height: 170px;
				}

				pre strong {
					font-size: 1.4em;
				}

				.copy-icon {
					width: 18px;
					height: 18px;
					display: inline-block;
					vertical-align: middle;
					cursor: pointer;
					transition: fill 250ms, opacity 250ms;
					margin-left: 10px;
					opacity: .4;
					margin-bottom: 4px;
				}

				.copy-icon:hover {
					fill: #00b9eb !important;
					opacity: 1;
				}

				.copy-msg.fadeOut {
					animation: fade 1.5s forwards;
				}

				#icon-preview {
					position: absolute;
					top: 50%;
					right: 25px;
					width: 150px;
					height: 150px;
					transform: translateY(-50%);
				}

				#icon-preview span {
					width: 150px !important;
					height: 150px !important;
				}

				@keyframes fade {
				  0%, 10% {
				  	opacity: 1;
				  }
				  100% {
				    opacity: 0;
				  }
				}

				.simple-icons-search-wrapper {
					margin-top: 20px;
					display: -webkit-flex;
					display: -moz-flex;
					display: -ms-flex;
					display: -o-flex;
					display: flex;
					justify-content: center;
					-ms-align-items: center;
					align-items: center;
				}

				#simple-icons-search {
					padding: 10px 15px;
					border-radius: 10px;
					width: 300px;
				}

	        	ul.simpleicons-list {
					display: -webkit-flex;
					display: -moz-flex;
					display: -ms-flex;
					display: -o-flex;
					display: flex;
					flex-wrap: wrap;
					justify-content: space-between;
	        	}

	        	ul.simpleicons-list:after {
	        		content: '';
	        		flex: auto;
	        	}

	        	ul.simpleicons-list li {
	        		padding: 10px;
	        		margin: 5px;
	        		transition: background 250ms;
	        		cursor: pointer;
	        	}

				ul.simpleicons-list li span {
	        		transition: fill 250ms;
				}

	        	ul.simpleicons-list li:hover,
	        	ul.simpleicons-list li.selected {
	        		background: #ccc;
	        	}

				ul.simpleicons-list li:hover span,
				ul.simpleicons-list li.selected span {
					fill: #111 !important;
				}

	        </style>

	        <script>        	
	        	document.addEventListener('DOMContentLoaded', function(){
	        		// states
	        		var selectedIconSlug = null;
	        		var prevSelectedIconSlug = null;

	        		// nodes
					var preview 			= document.querySelector('#shortcode-preview');
					var copyShortcodeBtn 	= document.querySelector('#copy-shortcode');
					var copyShortcodeMsg 	= document.querySelector('#copy-shortcode-msg');
					var placeholder 		= document.querySelector('#placeholder-preview');
					var copyPlaceholderBtn 	= document.querySelector('#copy-placeholder');
					var copyPlaceholderMsg 	= document.querySelector('#copy-placeholder-msg');
					var iconPreview 		= document.querySelector('#icon-preview');
					var icons 				= document.querySelectorAll('ul.simpleicons-list li');
					var iconListWrapper 	= document.querySelector('.simpleicons-list-wrapper');
					var iconList 			= document.querySelector('ul.simpleicons-list');
					var iconListOrigin		= iconList.cloneNode(true);
					var search 				= document.querySelector('#simple-icons-search');

					search.addEventListener('input', function(){
						var slug = slugify(this.value);

						if (slug.length > 0) {
							// console.log(slugify(this.value));
							var results = iconListOrigin.querySelectorAll("[data-icontitle*='" + slugify(this.value) + "']");
							// console.log(results);
							displayResults(results, this.value);
						} else {
							resetResults();
						}
					});

					function displayResults(results, search) {
						if (results.length !== 0) {
							iconList.innerHTML = '';
							for (let result of results) {
								iconList.appendChild(result.cloneNode(true));
								resetClickHandlers();
							}
						} else {
							iconList.innerHTML = 'No icons found.';
						}
					}

					function resetResults() {
						iconListWrapper.innerHTML = '';
						iconListWrapper.appendChild(iconListOrigin.cloneNode(true));
						iconList = document.querySelector('ul.simpleicons-list');
						resetClickHandlers();
					}

					function slugify(text) {
						// convert + to plus
						text = text.replace(/\+/g, "plus");
						// remove spaces
						text = text.replace(/ /g, "");
						// make all text lowercase
						text = text.toLowerCase();

						return text;
					}

					function resetClickHandlers() {
						icons = document.querySelectorAll('ul.simpleicons-list li');
						for (let icon of icons) {
							icon.addEventListener('click', function(){
								updateSelectedIconSlug(this.dataset.icontitle);
								updateUI();							
							});
						}
					} resetClickHandlers();

					function updateSelectedIconSlug(slug) {
						prevSelectedIconSlug = selectedIconSlug;
						selectedIconSlug = slug;
					}

					function updateUI() {
						var prevIcon = document.querySelector(`[data-icontitle="${prevSelectedIconSlug}"]`);
						var currentIcon = document.querySelector(`[data-icontitle="${selectedIconSlug}"]`);

						if (prevIcon) {
							prevIcon.classList.remove('selected');
						}

						preview.textContent = `[simple_icon name="${selectedIconSlug}"]`;
						copyShortcodeMsg.textContent = '';
						placeholder.textContent = `#${selectedIconSlug}#`;
						copyPlaceholderMsg.textContent = '';

						currentIcon.classList.add('selected');

						iconPreview.innerHTML = '';
						$svg = currentIcon.firstChild.cloneNode(true);
						iconPreview.appendChild($svg);

						window.scrollTo(0, 0);
					}

					copyShortcodeBtn.addEventListener('click', function(){
						var text = preview.textContent;
						copyTextToClipboard(copyShortcodeMsg, text);
					});

					copyPlaceholderBtn.addEventListener('click', function(){
						var text = placeholder.textContent;
						copyTextToClipboard(copyPlaceholderMsg, text);
					});


					function fallbackCopyTextToClipboard(message, text) {
						var textArea = document.createElement("textarea");
						message.classList.remove('fadeOut');
						textArea.value = text;
						preview.appendChild(textArea);
						textArea.focus();
						textArea.select();

						try {
							var successful = document.execCommand('copy');
							var msg = successful ? 'copied to clipboard!' : 'error';
							message.textContent = msg;
							message.classList.add('fadeOut');
						} catch (err) {
							message.textContent = 'error: ' + err;
						}

						preview.removeChild(textArea);
					}
					function copyTextToClipboard(message, text) {
						if (!navigator.clipboard) {
							fallbackCopyTextToClipboard(message, text);
							return;
						}
						navigator.clipboard.writeText(text).then(function() {
							message.textContent = 'copied to clipboard!';
							message.classList.add('fadeOut');
						}, function(err) {
							message.textContent = 'error: ' + err;
						});
					}
	        	});
	        </script>
	    <?php
	endif;
}
add_action('admin_head', 'simpleicons_admin_page_css');


function simple_icons_add_action_links ( $links ) {
	$mylinks = array(
    	'<a href="' . admin_url( 'index.php?page=simpleicons-welcome' ) . '">Get Started</a>',
		'<a href="' . admin_url( 'options-general.php?page=simpleicons' ) . '">Settings</a>',
	);
	return array_merge( $links, $mylinks );
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'simple_icons_add_action_links' );



register_activation_hook( __FILE__, 'simple_icons_activate' );
function simple_icons_activate() {
  set_transient( '_simple_icons_activation_redirect', true, 30 );
}

