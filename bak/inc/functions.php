<?php

// Define the default language
$default_lang = 'pl';

// Function to get supported languages
function get_supported_languages() {
    $file = __DIR__ . '/../assets/content.json';
    if (!file_exists($file)) return [];
    $articles = json_decode(file_get_contents($file), true);
    $langs = [];
    foreach ($articles[0] as $key => $value) {
        if (strpos($key, 'title_') === 0 || strpos($key, 'content_') === 0) {
            $langs[] = substr($key, strpos($key, '_') + 1);
        }
    }
    return array_unique($langs);
}

$lang = get_supported_languages();

// Set the current language
if (isset($_GET['lang']) && in_array($_GET['lang'], $lang)) {
    $_SESSION['current_lang'] = $_GET['lang'];
}

$current_lang = $_SESSION['current_lang'] ?? $default_lang;

// Simple slugify (ASCII-ish). Good enough for admin use.
function slugify($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    return $text ?: ('article-'.time());
}

/**
 * Return a single article by slug (array) or null.
 */
function get_article_by_slug($slug) {
    $file = __DIR__ . '/../assets/content.json';
    if (!file_exists($file)) return null;
    $articles = json_decode(file_get_contents($file), true) ?: [];
    foreach ($articles as $a) {
        if (($a['slug'] ?? '') === $slug) return $a;
    }
    return null;
}


function spawn_content($slug, $options = []) {
    $defaults = ['wrap' => true, 'show_title' => true, 'show_content' => true, 'title_tag' => 'h2'];
    $opt = array_merge($defaults, $options);

    $article = get_article_by_slug($slug);
    if (!$article) return '';

    global $current_lang, $default_lang;
    $title = $article['title_'.$current_lang]  ?? ($article['title_'.$default_lang]  ?? '');
    $body  = $article['content_'.$current_lang]?? ($article['content_'.$default_lang]?? '');

    $html = '';
    if ($opt['wrap']) $html .= '<article class="cms-article">';
    if ($opt['show_title'] && $title !== '') {
        $tag = preg_replace('/[^a-z0-9]/i', '', $opt['title_tag']);
        if ($tag === '') $tag = 'h2';
        $html .= "<{$tag}>".htmlspecialchars($title)."</{$tag}>";
    }
    if ($opt['show_content']) {
        $html .= '<div class="cms-body">'.$body.'</div>';
    }
    if ($opt['wrap']) $html .= '</article>';

    return $html;
}