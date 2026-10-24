<?php
/* FOOTER ADMIN: penutup halaman + load assets/js/admin.js (kalau dipakai). */

function admin_foot(array $p): void {
    if (!empty($p['js'])) {
        echo '<script src="../assets/js/admin.js"></script>' . "\n";
    }
    echo '</body>' . "\n" . '</html>' . "\n";
}
