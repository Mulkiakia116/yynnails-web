<?php
/* NAVBAR ADMIN: menu samping (sidebar) dashboard.
   Daftar menu ada di data/content.php ('nav'). */

function admin_navbar(string $aktif = ''): void {
    global $A;
    echo '<nav class="nav">' . "\n";
    foreach ($A['nav'] as [$label, $href, $key]) {
        $kelas = ($key !== '' && $key === $aktif) ? ' class="active"' : '';
        echo '    <a' . $kelas . ' href="' . h($href) . '">' . h($label) . '</a>' . "\n";
    }
    echo '</nav>' . "\n";
}
