<?php
/* NAVBAR: menu utama + penanda halaman aktif */

function navbar(){
  global $C;
  $cur = basename($_SERVER['SCRIPT_NAME']);
  $pageKey = pathinfo($cur, PATHINFO_FILENAME);
foreach ($C['nav'] as $n) {
  $navKey = $n[0] === 'Price List'
    ? 'pricelist'
    : strtolower($n[0]);

  $isActive = ($pageKey === 'index' && $navKey === 'home')
    || ($pageKey === $navKey);

  echo '<a href="'.e($n[1]).'" data-nav="'.e($navKey).'"'
    .($isActive ? ' class="on"' : '')
    .'>'.e($n[0]).'</a>';
}
}
