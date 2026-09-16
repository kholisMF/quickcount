<?php
/**
 * Menghasilkan URL aset dengan query ?v=<waktu file terakhir diubah>,
 * supaya browser otomatis mengambil versi terbaru setiap kali file
 * CSS/JS diedit, tanpa perlu hard refresh manual.
 */
function asset_url($relativePath)
{
    $fullPath = __DIR__ . '/../' . $relativePath;
    $version  = is_file($fullPath) ? filemtime($fullPath) : time();
    return $relativePath . '?v=' . $version;
}
