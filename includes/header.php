<?php
/* HEADER: <head>, logo, dan pembuka halaman. Memanggil navbar() dari navbar.php */
require_once __DIR__ . '/navbar.php';

function top($p){
  global $C;
  echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
     . '<title>'.e($p['title']).' – '.e($C['site']['name']).'</title>'
     . '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
     . '<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;500;600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">'
     
. '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">'
. '<link rel="stylesheet" href="assets/css/style.css"></head><body data-wa="'.e($C['site']['wa']).'">'

     . '<header class="top"><div class="wrap nav">'.logo().'<button class="burger" aria-label="Menu">☰</button><nav>';
  

  navbar();

  echo '</nav>'.btn($C['cta_nav']).'</div></header><main>';
}
