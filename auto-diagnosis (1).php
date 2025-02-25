<?php
/*
Plugin Name: Auto Diagnosis
Description: Плагин для диагностики поломок автомобилей с использованием ChatGPT.
Version: 1.0
Author: Твое имя
*/

if (!defined('ABSPATH')) {
    exit; // Защита от прямого доступа
}

// Подключение стилей и скриптов
function ad_enqueue_scripts() {
    wp_enqueue_style('ad-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('ad-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), null, true);
    wp_localize_script('ad-script', 'ad_params', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'ad_enqueue_scripts');

// Форма диагностики
function ad_diagnosis_form() {
    ob_start(); ?>
    <div id="ad-form-container">
        <h2>Диагностика поломки автомобиля</h2>
        <form id="ad-diagnosis-form">
            <label for="car-make">Марка автомобиля:</label>
            <input type="text" id="car-make" name="car-make" required>

            <label for="car-model">Модель автомобиля:</label>
            <input type="text" id="car-model" name="car-model" required>

            <label for="symptoms">Опишите симптомы поломки:</label>
            <textarea id="symptoms" name="symptoms" required></textarea>

            <button type="submit">Отправить запрос</button>
        </form>
        <div id="result"></div>
        <div id="articles"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('auto_diagnosis_form', 'ad_diagnosis_form');

// Функция для поиска статей через Google Custom Search API
function ad_search_articles($query, $car_make, $car_model) {
    $api_key = 'AIzaSyCkyol69cklJNDbrcBv2i-o7zv3B-xCIcI';  // Ваш API ключ
    $cx = '5706013c71e884db6';  // Ваш ID поисковой системы

    // Добавляем марку и модель автомобиля к запросу
    $full_query = urlencode("{$query} {$car_make} {$car_model}");

    // URL для запроса к Google Custom Search API с ограничением на русский язык и Россию
    $url = "https://www.googleapis.com/customsearch/v1?q={$full_query}&key={$api_key}&cx={$cx}&lr=lang_ru&cr=countryRU";

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        error_log('Ошибка при запросе к Google Custom Search: ' . $response->get_error_message());
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $articles = [];
    if (isset($data['items'])) {
        foreach ($data['items'] as $item) {
            $articles[] = array(
                'title' => $item['title'],
                'link'  => $item['link']
            );
        }
    }
    return $articles;
}

// Обработка запроса OpenAI
function ad_process_diagnosis() {
    
    $symptoms = sanitize_text_field($_POST['symptoms'] ?? '');
    $car_make = sanitize_text_field($_POST['car_make'] ?? '');
    $car_model = sanitize_text_field($_POST['car_model'] ?? '');

    if (empty($symptoms) || empty($car_make) || empty($car_model)) {
        wp_send_json_error(['message' => 'Заполните все поля.']);
    }

    if (!defined('OPENAI_API_KEY')) {
        wp_send_json_error(['message' => 'API ключ OpenAI не определён.']);
    }

    $openai_key = OPENAI_API_KEY;
    $prompt = "Автомобиль {$car_make} {$car_model}. Симптомы: {$symptoms}. 
Определи возможные поломки, способы диагностики и подробные инструкции для каждого шага, поищи информацию в интернете и дай практические советы по устранению неисправностей. Уложись в 800 токенов. Ответ дай в формате HTML с заголовками <h2>, <h3>, жирным шрифтом <strong> и списками <ul>, <li>." ;

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . $openai_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Ты эксперт по авто-диагностике.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 800,
        ]),
    ]);

    if (is_wp_error($response)) {
        error_log('Ошибка при запросе к OpenAI: ' . $response->get_error_message());
        wp_send_json_error(['message' => 'Ошибка API OpenAI: ' . $response->get_error_message()]);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    error_log('Ответ от OpenAI: ' . print_r($body, true));

    if (!isset($body['choices'][0]['message']['content'])) {
        error_log('Неверная структура ответа от OpenAI: ' . print_r($body, true));
        wp_send_json_error(['message' => 'Неверная структура ответа от API OpenAI.']);
    }

    // Ответ от OpenAI (обычный текст)
    $diagnosis_html = $body['choices'][0]['message']['content'];

    // Поиск статей
    $articles = ad_search_articles($symptoms, $car_make, $car_model);

    // Отправляем текст и статьи в ответе
    wp_send_json_success(['diagnosis' => $diagnosis_html, 'articles' => $articles]);
}
add_action('wp_ajax_ad_process_diagnosis', 'ad_process_diagnosis');
add_action('wp_ajax_nopriv_ad_process_diagnosis', 'ad_process_diagnosis');
