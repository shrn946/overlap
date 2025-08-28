<?php
/**
 * Plugin Name: CPC Shadow Overlap
 * Description: Overlapping text effect with configurable styles (front, back, alter) and shortcode generator.
 * Version: 2.8
 * Author: WP DESIGN LAB
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue frontend assets
 */
function cpc_shadow_enqueue_assets() {
    // Load Bowlby One + other fonts
    wp_enqueue_style(
        'cpc-shadow-fonts',
        'https://fonts.googleapis.com/css2?family=Bowlby+One&family=Anton&family=Luckiest+Guy&family=Fredoka+One&family=Rubik+Mono+One&family=Oswald&family=Pacifico&family=Press+Start+2P&family=Monoton&family=Abril+Fatface&display=swap',
        [],
        null
    );
    wp_enqueue_style('cpc-shadow-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('cpc-shadow-script', plugin_dir_url(__FILE__) . 'script.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'cpc_shadow_enqueue_assets');

/**
 * Enqueue admin CSS for settings page
 */
function cpc_shadow_admin_assets($hook) {
    if ($hook !== 'toplevel_page_cpc-shadow-settings') return;
    wp_enqueue_style('cpc-shadow-admin', plugin_dir_url(__FILE__) . 'admin.css');
}
add_action('admin_enqueue_scripts', 'cpc_shadow_admin_assets');

/**
 * Admin menu
 */
function cpc_shadow_add_admin_page() {
    add_menu_page(
        'CPC Shadow Settings',
        'CPC Shadow',
        'manage_options',
        'cpc-shadow-settings',
        'cpc_shadow_settings_page',
        'dashicons-editor-textcolor',
        80
    );
}
add_action('admin_menu', 'cpc_shadow_add_admin_page');

/**
 * Admin Settings Page – Shortcode Generator
 */
/**
 * Admin Settings Page – Shortcode Generator
 */
function cpc_shadow_settings_page() { ?>
<div class="wrap cpc-admin-wrapper">
    <h1 class="cpc-title">CPC Shadow Overlap</h1>

    <h2>Shortcode Generator</h2>
    <p>Generate shortcode, then paste into Elementor or any post/page.</p>
    
    <label>Text: 
        <input type="text" id="cpc-text" value="Overlap Text">
    </label>

    <label>Choose Type:
        <select id="cpc-type">
            <option value="front">Front</option>
            <option value="back">Back</option>
            <option value="alter">Alter</option>
        </select>
    </label>

    <label>Font Family:
        <select id="cpc-font">
            <option value="inherit">Inherit (Theme Body Font)</option>
            <option value="Bowlby One">Bowlby One (Default)</option>
            <option value="Anton">Anton</option>
            <option value="Luckiest Guy">Luckiest Guy</option>
            <option value="Fredoka One">Fredoka One</option>
            <option value="Rubik Mono One">Rubik Mono One</option>
            <option value="Oswald">Oswald</option>
            <option value="Pacifico">Pacifico</option>
            <option value="Press Start 2P">Press Start 2P</option>
            <option value="Monoton">Monoton</option>
            <option value="Abril Fatface">Abril Fatface</option>
        </select>
    </label>

    <label>Font Size:
        <input type="text" id="cpc-font-size" value="64px" placeholder="e.g. 64px or clamp(2rem,10vw,12rem)">
    </label>

    <label>Text Color:
        <input type="color" id="cpc-color" value="#ff0000">
    </label>

    <button class="button button-primary" onclick="cpcGenerateShortcode()">Generate Shortcode</button>
    <pre id="cpc-shortcode-box" class="cpc-shortcode-output"></pre>

    <script>
    function cpcGenerateShortcode() {
        let txt   = document.getElementById('cpc-text').value;
        let type  = document.getElementById('cpc-type').value;
        let font  = document.getElementById('cpc-font').value;
        let size  = document.getElementById('cpc-font-size').value;
        let color = document.getElementById('cpc-color').value;

        // Align removed, default centered in shortcode CSS
        let sc = '[cpc-shadow text="'+txt+'" type="'+type+'" font="'+font+'" font_size="'+size+'" color="'+color+'"]';

        document.getElementById('cpc-shortcode-box').innerText = sc;
    }
    </script>
</div>
<?php }


/**
 * Shortcode
 * Example:
 * [cpc-shadow text="Hello World" type="front" font="Bowlby One" font_size="64px" color="#ff0000" align="center"]
 */
function cpc_shadow_shortcode($atts) {
    $atts = shortcode_atts([
        'text'      => 'Overlap Text',
        'type'      => 'front',
        'font'      => 'Bowlby One',
        'font_size' => '64px',
        'color'     => '#ff0000',
        'align'     => 'center'
    ], $atts);

    $text  = esc_html($atts['text']);
    $type  = in_array($atts['type'], ['front','back','alter']) ? $atts['type'] : 'front';
    $font  = ($atts['font'] === 'inherit') ? 'inherit' : esc_attr($atts['font']);
    $color = esc_attr($atts['color']);
    $align = esc_attr($atts['align']);
    $size  = trim($atts['font_size']);

    // ✅ Auto-convert px size to responsive clamp()
    if (strpos($size, 'clamp') === false) {
        if (preg_match('/([0-9]+)px/', $size, $matches)) {
            $px = intval($matches[1]);
            $min = max(16, floor($px / 3));
            $vw  = round($px / 10, 2);
            $size = "clamp({$min}px, {$vw}vw, {$px}px)";
        }
    }

    $style = "font-family:'$font',sans-serif;font-weight:700;font-size:$size;color:$color;text-align:$align;";
    $css_class = 'cpc-shadow-text cpc-type-' . $type;

    return '<div class="cpc-shadow-wrapper '.$css_class.'" style="'.$style.'"><div overlap-text="'.$type.'">'.$text.'</div></div>';
}
add_shortcode('cpc-shadow', 'cpc_shadow_shortcode');

/**
 * Base CSS injected in <head>
 */
function cpc_shadow_base_css() { ?>
    <style>
    .cpc-shadow-wrapper {
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        line-height: 1.1;
        letter-spacing: -0.05em;
        text-transform: uppercase;
    }
    .cpc-shadow-text {
        font-weight: 700;
        display: inline-block;
    }
    [overlap-text] {
        text-shadow: 1px 1px 0.25ch black;
        display: flex;
    }
    [overlap-text] > span {
        z-index: calc(var(--m, 1) * var(--i, 1));
        min-width: 0.25ch;
    }
    [overlap-text="front"] { --m:  1; }
    [overlap-text="back"]  { --m: -1; }
    [overlap-text="alter"] > span:nth-child(even) { --m: -1; }

    /* Admin Styling */
    .cpc-admin-wrapper {
        background: #fff;
        border: 1px solid #ddd;
        padding: 25px 30px;
        border-radius: 12px;
        max-width: 800px;
        margin: 20px auto;
        font-family: "Bowlby One", sans-serif;
    }
    .cpc-title {
        font-size: 32px;
        text-align: center;
        margin-bottom: 25px;
        text-transform: uppercase;
    }
    .cpc-admin-wrapper label {
        display: block;
        margin: 15px 0 6px;
        font-weight: bold;
    }
    .cpc-admin-wrapper input,
    .cpc-admin-wrapper select {
        width: 100%;
        max-width: 400px;
    }
    .cpc-shortcode-output {
        margin-top:15px;
        background:#f9f9f9;
        padding:12px;
        border:1px solid #ddd;
        border-radius:6px;
    }
    </style>
<?php }
add_action('admin_head', 'cpc_shadow_base_css');
add_action('wp_head', 'cpc_shadow_base_css');
