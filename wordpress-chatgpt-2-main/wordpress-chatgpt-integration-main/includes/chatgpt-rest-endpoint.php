<?php
/*
Plugin Name: ChatGPT Plugin
Description: Plugin para integrar con ChatGPT.
Version: 1.0
Author: AI DEV
*/

// Configuración del plugin
add_action('admin_init', function () {
    register_setting('mi_grupo_de_configuracion', 'chatgpt_api_key', 'sanitize_chatgpt_api_key');
    add_settings_section('mi_seccion_de_configuracion', 'Configuración de ChatGPT', 'mi_seccion_de_configuracion_callback', 'general');
    add_settings_field('chatgpt_api_key', 'Clave API de ChatGPT', 'mi_campo_de_configuracion_callback', 'general', 'mi_seccion_de_configuracion');
});

function mi_seccion_de_configuracion_callback() {
    echo '<p>Introduce aquí tu clave API de ChatGPT.</p>';
}

function sanitize_chatgpt_api_key($input) {
    // Validar y sanitizar la clave API aquí
    $sanitized_input = sanitize_text_field($input);

    // Realizar cualquier otra validación necesaria, por ejemplo, longitud mínima/máxima, caracteres permitidos, etc.
    if (strlen($sanitized_input) < 10) {
        add_settings_error('chatgpt_api_key', 'chatgpt_api_key_length_error', 'La clave API debe tener al menos 10 caracteres.', 'error');
        return get_option('chatgpt_api_key'); // Retorna el valor anterior
    }

    return $sanitized_input;
}

function mi_campo_de_configuracion_callback() {
    $value = get_option('chatgpt_api_key');
    echo '<input type="text" name="chatgpt_api_key" value="' . esc_attr($value) . '" />';
    echo '<p class="description">Asegúrate de introducir una clave API válida y segura.</p>';
    
    // Muestra mensajes de error si los hay
    settings_errors('chatgpt_api_key');
}

// Función para manejar las solicitudes de ChatGPT
function chatgpt_callback($request) {
    $params = $request->get_json_params();
    if (isset($params['question'])) {
        $question = sanitize_text_field($params['question']);
        $apiKey = get_option('chatgpt_api_key'); // Obtén la clave API desde la configuración del plugin
        $apiEndpoint = 'https://api.openai.com/v1/completions'; // Endpoint de la API OpenAI.

        $response = wp_remote_post($apiEndpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ],
            'body' => json_encode([
                'model' => 'text-davinci-003', // Asegúrate de utilizar el modelo correcto aquí.
                'prompt' => $question,
                'max_tokens' => 150, // Puedes ajustar esto según sea necesario.
            ]),
        ]);

        if (is_wp_error($response)) {
            // Manejo de errores si la petición falló.
            return new WP_Error('chatgpt_error', $response->get_error_message(), ['status' => 500]);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['choices'][0]['text'])) {
            // Envío de la respuesta correcta.
            return new WP_REST_Response(['answer' => $data['choices'][0]['text']], 200);
        } else {
            // Error si la estructura de la respuesta no es la esperada.
            return new WP_Error('chatgpt_error', 'Error inesperado al obtener la respuesta de la API.', ['status' => 500]);
        }
    } else {
        // Error si no se proporcionó una pregunta.
        return new WP_Error('chatgpt_missing_question', 'No se ha proporcionado una pregunta.', ['status' => 400]);
    }
}

// Registra la función en la API de WordPress.
add_action('rest_api_init', function () {
    register_rest_route('tu_prefijo/v1', '/chatgpt/', array(
        'methods' => 'POST',
        'callback' => 'chatgpt_callback',
    ));
});
?>

