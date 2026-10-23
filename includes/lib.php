<?php
/* Mesin render. Biasanya TIDAK perlu diubah — edit konten di data/content.php */
$C = require __DIR__ . '/../data/content.php';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function nl($s){ return nl2br(e($s)); }
function g($a, $k, $d = ''){ return $a[$k] ?? $d; }

function u($x){
  global $C; $s = $C['site'];
  $m = [
    '@wa'=>'https://wa.me/'.$s['wa'],
    '@ig'=>$s['ig'],
    '@tt'=>$s['tt'],
    '@fb'=>$s['fb'],
    '@mail'=>'mailto:'.$s['email'],
    '@maps'=>$s['maps'],
    '@contact'=>'?page=contact-detail'
  ];
  return e($m[$x] ?? $x);
}

function img($src, $alt = ''){
  if ($src && is_file(dirname(__DIR__).'/'.$src)) return '<img src="'.e($src).'" alt="'.e($alt).'" loading="lazy">';
  return '<div class="ph"><span>'.e($alt).'</span></div>';
}
function btn($b){ $st = $b[2] ?? 'primary'; return '<a class="btn '.e($st).'" href="'.u($b[1]).'">'.e($b[0]).' <i>→</i></a>'; }
function btns($a){ $o = ''; foreach ($a ?: [] as $b) $o .= btn($b); return $o ? '<div class="btns">'.$o.'</div>' : ''; }
function head($s){
  $h = '';
  if (!empty($s['eyebrow'])) $h .= '<p class="eyebrow">'.e($s['eyebrow']).'</p>';
  if (!empty($s['title']))   $h .= '<h2>'.e($s['title']).'</h2>';
  if (!empty($s['text']) && is_string($s['text'])) $h .= '<p class="mut">'.nl($s['text']).'</p>';
  return $h;
}
function ics($items, $cls){   // item = [ikon, judul, teks(opsional)]
  $o = '<ul class="'.$cls.'">';
  foreach ($items as $i) $o .= '<li><span class="ic">'.e($i[0]).'</span><div><b>'.nl($i[1]).'</b>'.(!empty($i[2]) ? '<small>'.nl($i[2]).'</small>' : '').'</div></li>';
  return $o.'</ul>';
}
function chips($f){          // item = [label, kunci-kategori, ikon]
  $o = '<div class="chips">';
  foreach ($f as $k => $c) $o .= '<button type="button" class="chip'.($k ? '' : ' on').'" data-f="'.($k ? e($c[1]) : '*').'">'.($k ? '<span>'.e($c[2] ?? '✦').'</span> ' : '').e($c[0]).'</button>';
  return $o.'</div>';
}
function fields($f){         // [label, name, type, placeholder, wajib, opsi-select]
  $o = '';
  foreach ($f as $x) {
    $r = !empty($x[4]) ? ' required' : '';
    $a = ' name="'.e($x[1]).'" data-l="'.e($x[0]).'"'.$r;
    $o .= '<label class="fld"><span>'.e($x[0]).(!empty($x[4]) ? ' *' : '').'</span>';
    if ($x[2] === 'textarea') $o .= '<textarea rows="4"'.$a.' placeholder="'.e($x[3]).'"></textarea>';
    elseif ($x[2] === 'select') {
      $o .= '<select'.$a.'><option value="">'.e($x[3]).'</option>';
      foreach ($x[5] ?? [] as $op) $o .= '<option>'.e($op).'</option>';
      $o .= '</select>';
    } else $o .= '<input type="'.e($x[2]).'"'.$a.' placeholder="'.e($x[3]).'">';
    $o .= '</label>';
  }
  return $o;
}


function logo(){
    return '<a class="logo" href="index.php">'
        . '<img src="assets/img/logo.png" alt="Logo Yynnails" class="logo-img">'
        . '<span class="logo-text">'
        . '<b>YYnnails</b>'
        . '<small>NAIL ART STUDIO PALOPO</small>'
        . '</span>'
        . '</a>';
}



function socials(){
    global $C;

    $icons = [
        '@ig' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/></svg>',

        '@tt' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19.6 7.1a6.5 6.5 0 0 1-4.1-1.5v9.1a6.2 6.2 0 1 1-6.2-6.2c.4 0 .8 0 1.2.1v3.4a3 3 0 1 0 1.8 2.7V2h3.4c.3 2.1 1.8 3.7 3.9 4.1z"/></svg>',

        '@fb' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13.5 21v-8h2.7l.4-3.1h-3.1v-2c0-.9.3-1.5 1.6-1.5h1.7V3.6c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.3V13h2.8v8z"/></svg>'
    ];

    $o = '';

    foreach ($C['site']['social'] as $x) {
        $url = $x[1];

        if (!isset($icons[$url])) {
            continue;
        }

        $o .= '<a href="'.u($url).'" target="_blank" rel="noopener noreferrer" aria-label="'.e($x[0]).'">'
            .$icons[$url].
            '</a>';
    }

    return '<div class="soc">'.$o.'</div>';
}

/* layout dipecah: includes/header.php, navbar.php, footer.php */
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/footer.php';

function render_page($k){
  global $C; $p = $C['pages'][$k];
  top($p);
  foreach ($p['sections'] as $s) { $f = 's_'.$s['type']; if (function_exists($f)) $f($s); }
  foot();
}

/* ---------------- jenis bagian ---------------- */


function s_hero($s){
  echo '<section class="hero">';
  echo '<div class="hero-layout">';
  echo '<div class="hero-copy">';
  echo '<p class="eyebrow">✦ '.e(g($s,'eyebrow')).'</p>';
  echo '<h1>'.e($s['title']).'</h1>';
  echo '<p class="script">'.e(g($s,'script')).'</p>';
  echo '<p class="lead">'.nl(g($s,'text')).'</p>';
  echo btns(g($s,'buttons',[]));
  if (!empty($s['badges'])) echo ics($s['badges'], 'inl');
  echo '</div>';
  echo '<div class="himg">';
  echo img(g($s,'image'), $s['title']);
  echo '</div>';
  echo '</div></section>';
}



function s_split($s){
  echo '<section'.(!empty($s['id']) ? ' id="'.e($s['id']).'"' : '').' class="sec"><div class="wrap two split'.(!empty($s['reverse']) ? ' rev' : '').'"><div class="simg">'.img(g($s,'image'), $s['title'])
     . (!empty($s['deco']) ? '<em class="deco">'.nl($s['deco']).'</em>' : '').'</div><div>'
     . head(['eyebrow'=>g($s,'eyebrow'), 'title'=>g($s,'title')]);
  if (!empty($s['badge'])) echo '<span class="tag">'.e($s['badge']).'</span>';
  foreach (g($s,'paras',[]) as $p) echo '<p>'.nl($p).'</p>';
  if (!empty($s['list'])) echo ics($s['list'], 'inl');
  if (!empty($s['button'])) echo '<div class="btns">'.btn($s['button']).'</div>';
  if (!empty($s['quote'])) echo '<blockquote class="script">'.nl($s['quote']).'</blockquote>';
  echo '</div></div></section>';
}

function s_features($s){
  if (g($s,'style') === 'bar') { echo '<section class="sec"><div class="wrap">'.ics($s['items'], 'fbar').'</div></section>'; return; }
  echo '<section class="sec"><div class="wrap fgrid"><div>'.head($s).'</div>'.ics($s['items'], 'fcards').'</div></section>';
}
function s_cards($s){
  $st = g($s,'style','service');
  echo '<section'.(!empty($s['id']) ? ' id="'.e($s['id']).'"' : '').' class="sec"><div class="wrap" data-filter-scope><div class="shead"><div>'.head($s).'</div>'
     . (!empty($s['badge']) ? '<span class="tag fill">'.e($s['badge']).'</span>' : '')
     . (!empty($s['link']) ? '<a class="lnk" href="'.u($s['link'][1]).'">'.e($s['link'][0]).' →</a>' : '')
     . (!empty($s['button']) ? btn($s['button']) : '').'</div>';
  if (!empty($s['filter'])) echo chips($s['filter']);
  echo '<div class="grid g'.(int)g($s,'cols',3).' k-'.e($st).'">';
  foreach ($s['items'] as $i) {
    echo '<article class="card" data-cat="'.e(g($i,'cat')).'">';
    if (!empty($i['img'])) echo '<div class="cimg">'.img($i['img'], $i['title']).(!empty($i['icon']) ? '<span class="ic">'.e($i['icon']).'</span>' : '').'</div>';
    echo '<div class="cb">';
    if (empty($i['img']) && !empty($i['icon'])) echo '<span class="ic big">'.e($i['icon']).'</span>';
    echo '<h3>'.e($i['title']).'</h3>'.(!empty($i['text']) ? '<p>'.nl($i['text']).'</p>' : '')
       . '<div class="cf">'.(!empty($i['label']) ? '<small>'.e($i['label']).'</small>' : '')
       . '<b class="'.(in_array($st, ['price','photo']) ? 'pill' : 'pr').'">'.e($i['price']).'</b>'
       . '<a class="go" href="'.u(g($i,'url','booking.php')).'">→</a></div></div></article>';
  }
  echo '</div></div></section>';
}

function s_strip($s){
  require_once __DIR__ . '/../config/database.php';

  $images = [];

  if (($s['id'] ?? '') === 'gallery') {
    try {
      $stmt = $pdo->query(
        'SELECT judul, foto
         FROM galeri
         WHERE karya_terbaik = 1
         ORDER BY created_at DESC, id_galeri DESC'
      );

      $karya = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($karya as $item) {
        $foto = $item['foto'] ?? '';
        $pathFoto = dirname(__DIR__) . '/' . ltrim($foto, '/');

        if ($foto !== '' && is_file($pathFoto)) {
          $images[] = [
            'foto' => $foto,
            'judul' => $item['judul']
          ];
        }
      }
    } catch (Throwable $e) {
      error_log('Gagal memuat karya terbaik: ' . $e->getMessage());
    }
  }

  echo '<section'.(!empty($s['id']) ? ' id="'.e($s['id']).'"' : '').' class="sec"><div class="wrap strip"><div>'.head($s).btns([$s['button']]).'</div><div class="row">';

  if (($s['id'] ?? '') === 'gallery') {
    foreach ($images as $item) {
      echo '<figure>'.img($item['foto'], $item['judul']).'</figure>';
    }
  } else {
    foreach ($s['images'] ?? [] as $m) {
      echo '<figure>'.img($m, 'Galeri').'</figure>';
    }
  }

  if (($s['id'] ?? '') === 'gallery' && empty($images)) {
    echo '<p class="mut">Belum ada karya terbaik yang ditampilkan.</p>';
  }

  echo '</div></div></section>';
}

function s_gallery($s){
  require_once __DIR__ . '/../config/database.php';

  $kategoriList = [
    'Nail Art',
    'Press-on Nails',
    'Nail Extensions',
    'Manicure',
    'Lash Lift'
  ];

  try {
    $stmt = $pdo->query(
      'SELECT judul, kategori, foto, deskripsi
       FROM galeri
       ORDER BY created_at DESC, id_galeri DESC'
    );
    $karya = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $e) {
    error_log('Gagal memuat galeri: ' . $e->getMessage());
    $karya = [];
  }

  echo '<section class="sec" id="semua"><div class="wrap" data-filter-scope>';

  // Filter kategori tetap menggunakan desain lama
  $filter = [];
  foreach ($kategoriList as $kategori) {
    $filter[] = [$kategori, $kategori, '✦'];
  }
  echo chips($filter);

  echo '<div class="grid g4 k-gal">';

  foreach ($karya as $i) {
    $kategori = $i['kategori'] ?? '';
    $foto = $i['foto'] ?? '';

    // Pastikan file gambar memang tersedia
    $pathFoto = dirname(__DIR__) . '/' . ltrim($foto, '/');

    if ($foto === '' || !is_file($pathFoto)) {
      continue;
    }

    echo '<article class="card" data-cat="' . e($kategori) . '">';
    echo '<div class="cimg">' . img($foto, $i['judul']) . '</div>';
    echo '<div class="cap"><div>';
    echo '<h3>' . e($i['judul']) . '</h3>';
    echo '<small>' . e($kategori) . '</small>';

    if (!empty($i['deskripsi'])) {
      echo '<p>' . nl($i['deskripsi']) . '</p>';
    }

    echo '</div></div></article>';
  }

  if (empty($karya)) {
    echo '<p class="mut">Belum ada karya yang ditampilkan.</p>';
  }

  echo '</div></div></section>';
}



function s_detail($s){
  echo '<section class="sec" id="detail"><div class="wrap detail"><div class="slider" data-imgs="'.e(json_encode($s['images'])).'">'.img($s['images'][0], $s['title'])
     . '<button type="button" class="prev">‹</button><button type="button" class="next">›</button><span class="cnt">1 / '.count($s['images']).'</span></div>'
     . '<div><p class="eyebrow">'.e($s['eyebrow']).'</p><h2>'.e($s['title']).'</h2><p>'.nl($s['text']).'</p><div class="tags">';

  foreach ($s['tags'] as $t) {
    echo '<span class="tag">'.e($t).'</span>';
  }

  echo '</div><div class="share"><span>Share:</span>';

  foreach ($s['share'] as $x) {
    $label = strtolower($x[0]);

    if ($x[1] === '@copy') {
      echo '<button type="button" class="sq copy-link" aria-label="Salin tautan" title="Salin tautan">'
         . '<i class="fa-solid fa-link"></i></button>';
      continue;
    }

    if (str_contains($label, 'whatsapp') || $label === 'wa') {
      $icon = '<i class="fa-brands fa-whatsapp"></i>';
      $name = 'WhatsApp';
    } elseif (str_contains($label, 'instagram') || $label === 'ig') {
      $icon = '<i class="fa-brands fa-instagram"></i>';
      $name = 'Instagram';
    } elseif (str_contains($label, 'tiktok') || $label === 'tt') {
      $icon = '<i class="fa-brands fa-tiktok"></i>';
      $name = 'TikTok';
    } else {
      $icon = '<i class="fa-solid fa-link"></i>';
      $name = $x[0];
    }

    echo '<a class="sq" href="'.u($x[1]).'" aria-label="'.e($name).'" title="'.e($name).'">'.$icon.'</a>';
  }

  echo '</div></div></div></section>';
}


function s_steps($s){
  echo '<section class="sec"><div class="wrap two steps"><div class="simg">'.img(g($s,'image'), $s['title']).'<em class="deco">'.nl(g($s,'deco')).'</em></div><div>'.head($s).'<ol>';
  foreach ($s['items'] as $n => $i) echo '<li><span class="num">'.sprintf('%02d', $n + 1).'</span><span class="ic">'.e($i[0]).'</span><b>'.e($i[1]).'</b><small>'.e($i[2]).'</small></li>';
  echo '</ol></div></div></section>';
}
function s_cta($s){
  $right = !empty($s['side']) || !empty($s['images']);
  echo '<section class="sec"><div class="wrap cta">';
  if (!empty($s['image'])) echo '<div class="cta-img">'.img($s['image'], $s['title']).'</div>';
  elseif (!empty($s['deco'])) echo '<div class="cta-sc">'.nl($s['deco']).'</div>';
  echo '<div class="cta-t">'.(!empty($s['eyebrow']) ? '<p class="eyebrow">'.e($s['eyebrow']).'</p>' : '').'<h2>'.e($s['title']).'</h2><p>'.nl($s['text']).'</p>'
     . ($right && !empty($s['button']) ? btn($s['button']) : '').'</div>';
  if (!empty($s['images'])) { echo '<div class="cta-ph">'; foreach ($s['images'] as $m) echo img($m, 'Inspirasi'); echo '</div>'; }
  if (!empty($s['side'])) echo ics($s['side'], 'fbar sm');
  if (!$right && !empty($s['button'])) echo btn($s['button']);
  echo '</div></section>';
}

function s_extras($s){
  echo '<section class="sec"><div class="wrap">';

  echo '<div class="box"><h3>💡 '.e($s['notes_title']).'</h3><ul class="chk">';
  foreach ($s['notes'] as $n) {
    echo '<li>'.e($n).'</li>';
  }
  echo '</ul></div>';

  echo '</div></section>';
}

function s_contact($s){
  $f = $s['form'];
  echo '<section class="sec"><div class="wrap two cc"><div class="box"><h2>'.e($s['title']).'</h2><p class="mut">'.nl($s['text']).'</p><ul class="clist">';
  foreach ($s['items'] as $i) echo '<li><a href="'.u($i[4]).'"><span class="ic big">'.e($i[0]).'</span><div><small>'.e($i[1]).'</small><b>'.nl($i[2]).'</b><small>'.e($i[3]).'</small></div><i>›</i></a></li>';
  echo '</ul><p class="hours">🕒 <b>'.e($s['hours'][0]).'</b><br>'.e($s['hours'][1]).'<br><small>'.e($s['hours'][2]).'</small></p></div>'
     . '<div class="box"><h2>'.e($f['title']).'</h2><p class="mut">'.nl($f['text']).'</p><form class="frm" data-wa-form>'.fields($f['fields'])
     . '<label class="chk-l"><input type="checkbox" required> '.e($f['consent']).'</label><button class="btn primary wide">'.e($f['submit']).' <i>➤</i></button></form></div></div></section>';
}
function s_map($s){
  echo '<section'.(!empty($s['id']) ? ' id="'.e($s['id']).'"' : '').' class="sec"><div class="wrap two mp"><div>'.head($s);
  if (!empty($s['items'])) echo ics($s['items'], 'inl col');
  echo btns(g($s,'buttons',[])).'</div><div class="mapbox">';
  if (!empty($s['embed'])) echo '<iframe src="'.e($s['embed']).'" loading="lazy"></iframe>';
  else echo '<div class="pin"><span>📍</span><b>'.e($s['place']).'</b><small>'.nl($s['address']).'</small></div>';
  echo '</div></div></section>';
}
function s_follow($s){
  $c = $s['chat'];
  echo '<section class="sec"><div class="wrap two fol"><div class="box"><h2>'.e($s['title']).'</h2><p class="mut">'.nl($s['text']).'</p>'.socials().'<p class="handle">'.e($s['handle']).'</p></div>'
     . '<div class="box chat"><span class="ic big">💬</span><div><b>'.e($c[0]).'</b><p>'.nl($c[1]).'</p></div>'.btn([$c[2], '@wa']).'</div></div></section>';
}
function s_booking($s){
  $P = $s['panes']; $sm = $s['summary']; $b = $s['btn'];
  
echo '<section class="sec"><div class="wrap bk"><form class="box" id="bk" method="post" action="actions/proses_booking.php" enctype="multipart/form-data"><ol class="stp">';

  foreach ($s['steps'] as $k => $t) echo '<li'.($k ? '' : ' class="on"').'>'.e($t).'</li>';
  echo '</ol>';
  echo '<div class="pane on"><h3>'.e($P[0][0]).'</h3><p class="mut">'.e($P[0][1]).'</p><div class="two2">'.fields($s['fields']).'</div></div>';
  echo '<div class="pane"><h3>'.e($P[1][0]).'</h3><p class="mut">'.e($P[1][1]).'</p><div class="opts">';
  foreach ($s['services'] as $k => $v) echo '<label><input type="radio" name="service" value="'.e($v[1]).'" data-p="'.e($v[2]).'" data-l="Layanan"'.($k ? '' : ' required').'><span class="ic">'.e($v[0]).'</span><span><b>'.e($v[1]).'</b><small>'.e($v[2]).'</small></span></label>';
  echo '</div></div>';
  
echo '<div class="pane"><h3>'.e($P[2][0]).'</h3><p class="mut">'.e($P[2][1]).'</p><label class="drop"><input type="file" name="referensi" accept="image/jpeg,image/png,image/webp" data-l="Referensi Desain" hidden><b>⬆ '.e($s['upload'][0]).'</b><br><small>'.e($s['upload'][1]).'</small></label>'
     . '<label class="fld"><span>'.e($s['notes'][0]).'</span><textarea name="notes" rows="4" data-l="Catatan" placeholder="'.e($s['notes'][1]).'"></textarea></label></div>';

 
echo '<div class="pane"><h3>'.e($P[3][0]).'</h3><p class="mut">'.e($P[3][1]).'</p><ul id="review" class="sum"></ul><label class="chk-l"><input type="checkbox" name="consent" value="1" required> '.e($s['consent']).'</label></div>';

  echo '<div class="bnav"><button type="button" class="btn ghost" id="prev" hidden>'.e($b[2]).'</button><button type="button" class="btn primary" id="next" data-next="'.e($b[0]).'" data-send="'.e($b[1]).'">'.e($b[0]).' 2 →</button></div></form>';
 

echo '<aside><div class="box"><h3>'.e($sm['title']).'</h3><div class="sumimg" style="position:relative;overflow:hidden;">'.img($sm['image'], 'Yynnails').'<div id="summary-reference" style="position:absolute;inset:0;display:none;background:#1b0d15;padding:12px;box-sizing:border-box;"><img id="summary-reference-img" alt="Referensi desain pelanggan" style="width:100%;height:100%;object-fit:contain;border-radius:10px;"></div></div><ul class="sum">';


  foreach ($sm['rows'] as $r) echo '<li><span class="ic">'.e($r[0]).'</span><div><small>'.e($r[1]).'</small><b data-s="'.e($r[2]).'">-</b></div></li>';

echo '</ul><p class="note">ⓘ '.e($sm['info']).'</p><p class="script">'.e($sm['script']).'</p></div>'
     . '<div class="box" style="margin-top:18px"><h3>'.e($s['help'][0]).'</h3><p>'.e($s['help'][1]).'</p>'.btn([$s['help'][2], '@wa']).'</div>';

if (!empty($s['status_button'])) {
    echo '<div class="box" style="margin-top:18px">'
       . '<h3>'.e($s['status_button'][0]).'</h3>'
       . '<p>'.e($s['status_button'][1]).'</p>'
       . '<a class="btn primary wide" href="status.php">'
       . e($s['status_button'][2])
       . '</a></div>';
}

echo '</aside></div></section>';
}


function s_status($s){
    $q = trim($_GET['q'] ?? '');
    $r = null;
    $dicari = false;

    if ($q !== '') {
        $dicari = true;

        try {
            require_once __DIR__ . '/../config/database.php';

            $stmt = $pdo->prepare(
                'SELECT
                    p.kode_booking AS code,
                    p.layanan AS service,
                    p.tanggal AS date,
                    p.jam AS time,
                    p.status AS status,
                    p.catatan AS note,
                    pl.nama AS name
                 FROM pesanan p
                 INNER JOIN pelanggan pl
                    ON p.id_pelanggan = pl.id_pelanggan
                 WHERE p.kode_booking = :kode
                 LIMIT 1'
            );

            $stmt->execute(['kode' => $q]);
            $r = $stmt->fetch();

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errorDatabase = true;
        }
    }

    echo '<section class="sec"><div class="wrap stat ctr">'
        . head($s)
        . '<div class="box" style="text-align:left"><div class="tabs">'
        . '<button type="button" class="on" data-t="t1">🔍 '
        . e($s['tabs'][0])
        . '</button><button type="button" data-t="t2">ⓘ '
        . e($s['tabs'][1])
        . '</button></div>'
        . '<div class="tp" id="t1"><form method="get" class="frm">'
        . '<label class="fld"><span>' . e($s['label']) . ' *</span>'
        . '<input name="q" value="' . e($q) . '" placeholder="Masukkan kode booking" required>'
        . '</label><button class="btn primary wide">🔍 '
        . e($s['button']) . ' <i>→</i></button></form>';

    if ($dicari) {
        if (!empty($errorDatabase)) {
            echo '<div class="note">Maaf, status booking belum dapat diperiksa. Silakan coba lagi.</div>';
        } elseif ($r) {
            echo '<div class="note">'
                . '<b>' . e($r['code']) . '</b> — ' . e($r['status'])
                . '<br>Nama: ' . e($r['name'])
                . '<br>Layanan: ' . e($r['service'])
                . '<br>Tanggal: ' . e($r['date'])
                . '<br>Waktu: ' . e($r['time'])
                . '<br><small>' . e($r['note'] ?? '') . '</small>'
                . '</div>';
        } else {
            echo '<div class="note">Kode booking tidak ditemukan. Periksa kembali kode yang kamu masukkan.</div>';
        }
    }

    echo '</div><div class="tp" id="t2" hidden><div class="note">💡 '
        . nl($s['info'])
        . '</div></div></div></div></section>';
}
