<?php
/* FOOTER: penutup halaman + load assets/js/app.js */

function foot(){
  global $C; $s = $C['site'];
  echo '</main><footer><div class="wrap foot">'.logo().'<p>'.e($s['footer_text']).'</p>'.socials().'<nav class="fnav">';
  foreach ($C['nav'] as $n) echo '<a href="'.e($n[1]).'">'.e($n[0]).'</a>';
  echo '</nav></div><div class="wrap copy"><span>'.e($s['copyright']).'</span><span>'.e($s['footer_note']).'</span></div></footer>'
     . '<script src="assets/js/app.js"></script></body></html>';
}
