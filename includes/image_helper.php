<?php
/**
 * Dobavlja putanju do slike proizvoda
 * @param int $product_id - ID proizvoda
 * @param string $product_name - Naziv proizvoda (fallback)
 * @return string - Putanja do slike ili placeholder
 */
function getProductImage($product_id, $product_name = '') {
    $base_path = '/supplementShop/assets/images/products/';
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/supplementShop/assets/images/products/';
    
    // Podržani formati
    $extensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    
    // 1. Traži po ID-u (PREFERIRANO)
    foreach ($extensions as $ext) {
        if (file_exists($full_path . $product_id . '.' . $ext)) {
            return $base_path . $product_id . '.' . $ext;
        }
    }
    
    // 2. Ako nema slike po ID-u, traži po nazivu (fallback)
    if ($product_name) {
        $clean_name = strtolower(trim($product_name));
        $clean_name = preg_replace('/[^a-z0-9-]/', '-', $clean_name);
        $clean_name = preg_replace('/-+/', '-', $clean_name);
        
        foreach ($extensions as $ext) {
            if (file_exists($full_path . $clean_name . '.' . $ext)) {
                return $base_path . $clean_name . '.' . $ext;
            }
        }
    }
    
    // 3. Placeholder po kategoriji (opciono)
    return getCategoryPlaceholder();
}

/**
 * Placeholder slika po kategoriji
 */
function getCategoryPlaceholder($category = null) {
    $placeholders = [
        'Protein' => '/supplementShop/assets/images/placeholders/protein.jpg',
        'Kreatin' => '/supplementShop/assets/images/placeholders/kreatin.jpg',
        'Pre-workout' => '/supplementShop/assets/images/placeholders/preworkout.jpg',
        'default' => '/supplementShop/assets/images/placeholders/default.jpg',
    ];
    
    if ($category && isset($placeholders[$category])) {
        return $placeholders[$category];
    }
    return $placeholders['default'];
}

/**
 * Provjerava da li slika postoji
 */
function productImageExists($product_id) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/supplementShop/assets/images/products/';
   $extensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    
    foreach ($extensions as $ext) {
        if (file_exists($full_path . $product_id . '.' . $ext)) {
            return true;
        }
    }
    return false;
}
?>