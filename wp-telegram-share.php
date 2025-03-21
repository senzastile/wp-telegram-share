<?php
/**
 * Plugin Name: WP Telegram Share
 * Plugin URI: https://www.senzastile.it/wp-telegram-share
 * Description: Un plugin che condivide automaticamente i post di WordPress su un canale Telegram.
 * Version: 1.0.0
 * Author: Senza Stile
 * Author URI: https://www.senzastile.it
 * License: GPL-2.0+
 * Text Domain: wp-telegram-share
 */

// Evita l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class WP_Telegram_Share {

    /**
     * Inizializzazione del plugin
     */
    public function __construct() {
        // Aggiunge il menu delle impostazioni
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Registra le impostazioni
        add_action('admin_init', array($this, 'register_settings'));
        
        // Hook per la pubblicazione di nuovi post
        add_action('publish_post', array($this, 'share_post_on_telegram'), 10, 2);
        
        // Aggiungi il metabox per la condivisione manuale
        add_action('add_meta_boxes', array($this, 'add_telegram_metabox'));
        
        // Salva le impostazioni del metabox
        add_action('save_post', array($this, 'save_telegram_metabox'));
    }

    /**
     * Aggiungi il menu delle impostazioni
     */
    public function add_admin_menu() {
        add_options_page(
            'WP Telegram Share', 
            'WP Telegram Share', 
            'manage_options', 
            'wp-telegram-share', 
            array($this, 'settings_page')
        );
    }

    /**
     * Registra le impostazioni del plugin
     */
    public function register_settings() {
        register_setting('wp_telegram_share', 'wp_telegram_share_token');
        register_setting('wp_telegram_share', 'wp_telegram_share_channel');
        register_setting('wp_telegram_share', 'wp_telegram_share_message_template');
        register_setting('wp_telegram_share', 'wp_telegram_share_include_image');
    }

    /**
     * Pagina delle impostazioni
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_telegram_share');
                do_settings_sections('wp_telegram_share');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wp_telegram_share_token"><?php _e('Token Bot Telegram', 'wp-telegram-share'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="wp_telegram_share_token" name="wp_telegram_share_token" 
                                value="<?php echo esc_attr(get_option('wp_telegram_share_token')); ?>" class="regular-text">
                            <p class="description">
                                <?php _e('Inserisci il token del tuo bot Telegram ottenuto da @BotFather', 'wp-telegram-share'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wp_telegram_share_channel"><?php _e('Canale Telegram', 'wp-telegram-share'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="wp_telegram_share_channel" name="wp_telegram_share_channel" 
                                value="<?php echo esc_attr(get_option('wp_telegram_share_channel')); ?>" class="regular-text">
                            <p class="description">
                                <?php _e('Inserisci l\'ID o il nome del canale (es: @tuocanale)', 'wp-telegram-share'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wp_telegram_share_message_template"><?php _e('Template Messaggio', 'wp-telegram-share'); ?></label>
                        </th>
                        <td>
                            <textarea id="wp_telegram_share_message_template" name="wp_telegram_share_message_template" 
                                rows="5" class="large-text"><?php echo esc_textarea(get_option('wp_telegram_share_message_template', "📢 Nuovo articolo: {title}\n\n{excerpt}\n\n🔗 {permalink}")); ?></textarea>
                            <p class="description">
                                <?php _e('Usa i segnaposto: {title}, {excerpt}, {permalink}, {author}, {date}', 'wp-telegram-share'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wp_telegram_share_include_image"><?php _e('Includi Immagine', 'wp-telegram-share'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="wp_telegram_share_include_image" name="wp_telegram_share_include_image" 
                                value="1" <?php checked(1, get_option('wp_telegram_share_include_image', 0)); ?>>
                            <p class="description">
                                <?php _e('Invia l\'immagine in evidenza insieme al messaggio', 'wp-telegram-share'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <div class="card">
                <h2><?php _e('Test di Connessione', 'wp-telegram-share'); ?></h2>
                <p><?php _e('Clicca sul pulsante per verificare la connessione con Telegram', 'wp-telegram-share'); ?></p>
                <button id="test-telegram-connection" class="button button-secondary">
                    <?php _e('Testa Connessione', 'wp-telegram-share'); ?>
                </button>
                <div id="test-result" style="margin-top: 10px;"></div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#test-telegram-connection').on('click', function() {
                    var token = $('#wp_telegram_share_token').val();
                    var channel = $('#wp_telegram_share_channel').val();
                    
                    if (!token || !channel) {
                        $('#test-result').html('<div class="notice notice-error"><p>Inserisci token e canale prima di eseguire il test</p></div>');
                        return;
                    }
                    
                    $('#test-result').html('<p>Verifica in corso...</p>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'test_telegram_connection',
                            token: token,
                            channel: channel,
                            nonce: '<?php echo wp_create_nonce('test_telegram_connection'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#test-result').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                            } else {
                                $('#test-result').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                            }
                        },
                        error: function() {
                            $('#test-result').html('<div class="notice notice-error"><p>Errore di connessione</p></div>');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
    }

    /**
     * Aggiunge il metabox nella pagina di modifica del post
     */
    public function add_telegram_metabox() {
        add_meta_box(
            'wp_telegram_share_metabox',
            __('Condivisione Telegram', 'wp-telegram-share'),
            array($this, 'telegram_metabox_callback'),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Callback per il metabox
     */
    public function telegram_metabox_callback($post) {
        wp_nonce_field('wp_telegram_share_metabox', 'wp_telegram_share_nonce');
        
        $disable_share = get_post_meta($post->ID, '_wp_telegram_disable_share', true);
        $already_shared = get_post_meta($post->ID, '_wp_telegram_already_shared', true);
        
        ?>
        <p>
            <label>
                <input type="checkbox" name="wp_telegram_disable_share" value="1" <?php checked($disable_share, 1); ?>>
                <?php _e('Non condividere su Telegram', 'wp-telegram-share'); ?>
            </label>
        </p>
        
        <?php if ($already_shared) : ?>
            <p class="description">
                <?php _e('Questo post è già stato condiviso su Telegram', 'wp-telegram-share'); ?>
            </p>
        <?php endif; ?>
        
        <?php if ($post->post_status == 'publish') : ?>
            <p>
                <button type="button" id="telegram-manual-share" class="button button-secondary">
                    <?php _e('Condividi ora su Telegram', 'wp-telegram-share'); ?>
                </button>
                <span id="manual-share-result"></span>
            </p>
            
            <script>
            jQuery(document).ready(function($) {
                $('#telegram-manual-share').on('click', function() {
                    $('#manual-share-result').html('<?php _e('Invio in corso...', 'wp-telegram-share'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'manual_telegram_share',
                            post_id: <?php echo $post->ID; ?>,
                            nonce: '<?php echo wp_create_nonce('manual_telegram_share'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#manual-share-result').html('<span style="color:green">' + response.data + '</span>');
                            } else {
                                $('#manual-share-result').html('<span style="color:red">' + response.data + '</span>');
                            }
                        },
                        error: function() {
                            $('#manual-share-result').html('<span style="color:red">Errore di connessione</span>');
                        }
                    });
                });
            });
            </script>
        <?php endif; ?>
        <?php
    }

    /**
     * Salva i dati del metabox
     */
    public function save_telegram_metabox($post_id) {
        if (!isset($_POST['wp_telegram_share_nonce']) || !wp_verify_nonce($_POST['wp_telegram_share_nonce'], 'wp_telegram_share_metabox')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $disable_share = isset($_POST['wp_telegram_disable_share']) ? 1 : 0;
        update_post_meta($post_id, '_wp_telegram_disable_share', $disable_share);
    }

    /**
     * Condivide il post su Telegram quando viene pubblicato
     */
    public function share_post_on_telegram($post_id, $post) {
        // Verifica se il post è stato già condiviso
        $already_shared = get_post_meta($post_id, '_wp_telegram_already_shared', true);
        if ($already_shared) {
            return;
        }
        
        // Verifica se la condivisione è disabilitata per questo post
        $disable_share = get_post_meta($post_id, '_wp_telegram_disable_share', true);
        if ($disable_share) {
            return;
        }
        
        // Condividi solo i post originali, non le revisioni o gli autosalvataggi
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Invia il messaggio a Telegram
        $result = $this->send_telegram_message($post_id);
        
        // Se l'invio è avvenuto con successo, aggiorna il meta
        if ($result['success']) {
            update_post_meta($post_id, '_wp_telegram_already_shared', 1);
            update_post_meta($post_id, '_wp_telegram_share_message_id', $result['message_id']);
        }
    }
    
    /**
     * Invia il messaggio a Telegram
     */
    public function send_telegram_message($post_id) {
        $token = get_option('wp_telegram_share_token');
        $channel = get_option('wp_telegram_share_channel');
        $include_image = get_option('wp_telegram_share_include_image', 0);
        
        if (empty($token) || empty($channel)) {
            return array(
                'success' => false,
                'message' => __('Token o canale Telegram non configurati', 'wp-telegram-share')
            );
        }
        
        $post = get_post($post_id);
        
        if (!$post) {
            return array(
                'success' => false,
                'message' => __('Post non trovato', 'wp-telegram-share')
            );
        }
        
        // Prepara il messaggio sostituendo i segnaposto
        $template = get_option('wp_telegram_share_message_template', "📢 Nuovo articolo: {title}\n\n{excerpt}\n\n🔗 {permalink}");
        
        $excerpt = $post->post_excerpt;
        if (empty($excerpt)) {
            $excerpt = wp_trim_words(strip_shortcodes(strip_tags($post->post_content)), 40, '...');
        }
        
        $author = get_the_author_meta('display_name', $post->post_author);
        
        $replacements = array(
            '{title}' => $post->post_title,
            '{excerpt}' => $excerpt,
            '{permalink}' => get_permalink($post_id),
            '{author}' => $author,
            '{date}' => get_the_date('', $post_id)
        );
        
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        // Verifica se includere l'immagine in evidenza
        if ($include_image && has_post_thumbnail($post_id)) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'large');
            
            if ($thumbnail_url) {
                return $this->send_telegram_photo($token, $channel, $thumbnail_url, $message);
            }
        }
        
        // Invia solo il testo se non c'è immagine o se non è richiesta
        return $this->send_telegram_text($token, $channel, $message);
    }
    
    /**
     * Invia un messaggio di testo a Telegram
     */
    private function send_telegram_text($token, $channel, $message) {
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        
        $args = array(
            'body' => array(
                'chat_id' => $channel,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($body && isset($body['ok']) && $body['ok']) {
            return array(
                'success' => true,
                'message' => __('Messaggio inviato con successo', 'wp-telegram-share'),
                'message_id' => $body['result']['message_id'] ?? 0
            );
        }
        
        return array(
            'success' => false,
            'message' => isset($body['description']) ? $body['description'] : __('Errore sconosciuto', 'wp-telegram-share')
        );
    }
    
    /**
     * Invia una foto con didascalia a Telegram
     */
    private function send_telegram_photo($token, $channel, $photo_url, $caption) {
        $url = "https://api.telegram.org/bot{$token}/sendPhoto";
        
        $args = array(
            'body' => array(
                'chat_id' => $channel,
                'photo' => $photo_url,
                'caption' => $caption,
                'parse_mode' => 'HTML'
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($body && isset($body['ok']) && $body['ok']) {
            return array(
                'success' => true,
                'message' => __('Messaggio con foto inviato con successo', 'wp-telegram-share'),
                'message_id' => $body['result']['message_id'] ?? 0
            );
        }
        
        // Se fallisce l'invio con la foto, prova con solo testo
        return $this->send_telegram_text($token, $channel, $caption);
    }
}

// Inizializza gli hook AJAX
function wp_telegram_share_ajax_hooks() {
    add_action('wp_ajax_test_telegram_connection', 'wp_telegram_share_test_connection');
    add_action('wp_ajax_manual_telegram_share', 'wp_telegram_share_manual_share');
}
add_action('init', 'wp_telegram_share_ajax_hooks');

/**
 * Test della connessione Telegram
 */
function wp_telegram_share_test_connection() {
    check_ajax_referer('test_telegram_connection', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permessi insufficienti', 'wp-telegram-share'));
        return;
    }
    
    $token = sanitize_text_field($_POST['token']);
    $channel = sanitize_text_field($_POST['channel']);
    
    if (empty($token) || empty($channel)) {
        wp_send_json_error(__('Token o canale Telegram mancanti', 'wp-telegram-share'));
        return;
    }
    
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    
    $args = array(
        'body' => array(
            'chat_id' => $channel,
            'text' => __('Test di connessione WordPress - Telegram eseguito con successo!', 'wp-telegram-share')
        ),
        'timeout' => 30
    );
    
    $response = wp_remote_post($url, $args);
    
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
        return;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($body && isset($body['ok']) && $body['ok']) {
        wp_send_json_success(__('Connessione riuscita! Messaggio inviato al canale.', 'wp-telegram-share'));
    } else {
        $error = isset($body['description']) ? $body['description'] : __('Errore sconosciuto', 'wp-telegram-share');
        wp_send_json_error($error);
    }
}

/**
 * Condivisione manuale su Telegram
 */
function wp_telegram_share_manual_share() {
    check_ajax_referer('manual_telegram_share', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(__('Permessi insufficienti', 'wp-telegram-share'));
        return;
    }
    
    $post_id = intval($_POST['post_id']);
    
    if (!$post_id) {
        wp_send_json_error(__('ID post non valido', 'wp-telegram-share'));
        return;
    }
    
    $telegram_share = new WP_Telegram_Share();
    $result = $telegram_share->send_telegram_message($post_id);
    
    if ($result['success']) {
        update_post_meta($post_id, '_wp_telegram_already_shared', 1);
        update_post_meta($post_id, '_wp_telegram_share_message_id', $result['message_id']);
        wp_send_json_success(__('Post condiviso con successo!', 'wp-telegram-share'));
    } else {
        wp_send_json_error($result['message']);
    }
}

// Inizializza il plugin
$wp_telegram_share = new WP_Telegram_Share();