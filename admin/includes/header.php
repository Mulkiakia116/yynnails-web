<?php
/* HEADER ADMIN: <head> (judul, font, CSS) dan pembuka <body>.
   Pengaturan tiap halaman (judul, class body, CSS) ada di data/content.php. */

function admin_top(array $p): void {
    global $A;
    echo '<!DOCTYPE html>' . "\n"
       . '<html lang="id">' . "\n"
       . '<head>' . "\n"
       . '<meta charset="UTF-8">' . "\n"
       . '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n"
       . '<title>' . h($p['title'] . ' | ' . $A['site']) . '</title>' . "\n";

    if (!empty($p['font'])) {
        echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">' . "\n";
    }

    // Path CSS ditulis dari root proyek; halaman admin ada 1 folder di bawahnya
    foreach ($p['css'] as $file) {
        echo '<link rel="stylesheet" href="../' . h($file) . '">' . "\n";
    }

    echo '</head>' . "\n"
       . '<body' . ($p['body'] !== '' ? ' class="' . h($p['body']) . '"' : '') . '>' . "\n";
}
