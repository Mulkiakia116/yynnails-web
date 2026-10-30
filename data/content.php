<?php
/* ==========================================================================
   SEMUA KONTEN WEBSITE ADA DI FILE INI.
   Ubah teks, harga, gambar, link, urutan bagian, atau tambah/hapus item —
   halaman otomatis ikut berubah. Tidak perlu menyentuh file lain.

   - Gambar : taruh file di assets/img/ lalu tulis path-nya. Kalau file belum
              ada, otomatis tampil kotak pink placeholder.
   - Link   : boleh '@wa' '@ig' '@tt' '@mail' '@maps' (diambil dari 'site').
   - Bagian : tiap halaman = daftar 'sections'. Urutkan / hapus / duplikat bebas.
   ========================================================================== */
$I = 'assets/img/';

$cats = [['Semua'], ['Nail Art','nailart','✦'], ['Press-on Nails','presson','🎀'], ['Nail Tip','tip','🦋'],
         ['Lash Lift','lash','👁'], ['Manicure','mani','🪷'],['Remover Nail Art','remover','♡']];
$catsS = $cats; $catsS[0] = ['Semua Layanan'];

$svc = [
 ['img'=>$I.'nail.jpeg','icon'=>'💅🏻','title'=>'Nail Art','text'=>'Layanan nail art dengan berbagai desain kreatif dan custom sesuai keinginanmu.','label'=>'Mulai dari','price'=>'Rp 50.000','cat'=>'nailart','url'=>'booking.php'],
 ['img'=>$I.'press.jpeg','icon'=>'🎀','title'=>'Press-on Nails','text'=>'Kuku instan dengan desain trendy dan mudah digunakan.','label'=>'Mulai dari','price'=>'Rp 25.000','cat'=>'presson','url'=>'booking.php'],
 ['img'=>$I.'tip.jpeg','icon'=>'🍃','title'=>'Nail Tip','text'=>'Penambahan dan perpanjangan kuku dengan hasil natural dan rapi.','label'=>'Mulai dari','price'=>'Rp 100.000','cat'=>'tip','url'=>'booking.php'],
 ['img'=>$I.'lash.jpeg','icon'=>'👁','title'=>'Lash Lift','text'=>'Perawatan bulu mata agar terlihat lebih lentik dan natural.','label'=>'Mulai dari','price'=>'Rp 45.000','cat'=>'lash','url'=>'booking.php'],
 ['img'=>$I.'manicure.jpg','icon'=>'🪷','title'=>'Manicure','text'=>'Perawatan dasar untuk menjaga kesehatan dan kecantikan kuku.','label'=>'Mulai dari','price'=>'Rp 50.000','cat'=>'mani','url'=>'booking.php'],
 ['img'=>$I.'remov.jpeg','icon'=>'♡','title'=>'Remover Nail Art','text'=>'Layanan penghapusan nail art untuk membantu membersihkan kuku.','label'=>'Mulai dari','price'=>'Rp 3.000','cat'=>'remover','url'=>'booking.php'],
];


$footerCta = ['Booking Sekarang','booking.php'];

return [
'site' => [
    'name' => 'Yynnails',
    'sub' => 'NAIL STUDIO',
    'footer_text' => 'Cantik bukan hanya tentang penampilan, tapi juga tentang rasa percaya diri. ♡',
    'copyright' => '© 2026 Yynnails. All rights reserved.',
    'footer_note' => 'Nails make everything better ♡',

    'wa' => '6282319646189',
    'wa_label' => '082319646189',

    'ig' => 'https://www.instagram.com/yynnails/',
    'tt' => 'https://www.tiktok.com/@yuyun_amoy',
    'fb' => 'https://www.facebook.com/profile.php?id=61569649580988&mibextid=rS40aB7S9Ucbxw6v',

    
    
    'email' => 'yynailsstudio@gmail.com',
    'maps' => 'https://maps.app.goo.gl/j1anmmJVYCEPiHG99?g_st=aw',

    'social' => [
        ['IG', '@ig'],
        ['TT', '@tt'],
        ['FB', '@fb'],
    ],
],




'nav' => [
  ['Home', 'index.php'],
  ['About', 'index.php#about'],
  ['Services', 'index.php#services'],
  ['Gallery', 'index.php#gallery'],
  ['Price List', 'index.php#pricelist'],
  ['Contact', 'index.php#contact']
],
'cta_nav' => ['Booking Sekarang', 'booking.php'],

/* ---- ATURAN KUOTA HARIAN ----------------------------------------------
   Layanan di daftar 'layanan' dibatasi 'batas_harian' pelanggan per hari
   (dihitung gabungan). Layanan lain tidak dibatasi kuota ini.
   Nama layanan harus sama persis dengan yang ada di form booking.
   Ubah angka / daftar di sini saja, proses booking otomatis ikut. */
'kuota' => [
    'batas_harian' => 5,
    'layanan'      => ['Nail Art', 'Nail Tip'],
],

'pages' => [

/* ------------------------------ HOME ------------------------------ */
'home' => ['title'=>'Home', 'sections'=>[
  ['type'=>'hero','eyebrow'=>'NAIL ART STUDIO','title'=>'More Than Just Nails,','script'=>"It's a Form of Self Care ♡",
   'text'=>"Di Yynnails, kami percaya bahwa kuku yang cantik bukan hanya soal penampilan, tetapi juga soal rasa percaya diri. Dengan layanan nail art berkualitas dan hasil yang detail, kami siap mewujudkan desain impianmu!",
   'buttons'=>[['Booking Sekarang','booking.php'],['Lihat Layanan','services.php','ghost']],
   
   'badges'=>[['🛡','Kualitas Terbaik'],['✏','Desain Custom'],['🤲','Pelayanan Ramah']],
   'image'=>$I.'banner home.jpeg',],
   
['type'=>'split','id'=>'about','image'=>$I.'piagam.jpeg','deco'=>"Sertifikat pengalaman,\nTerpercaya ♡",'eyebrow'=>'TENTANG KAMI','title'=>'Yynnails Nail Studio',

   'paras'=>['Yynnails adalah home studio nail art yang fokus pada kualitas, kenyamanan, dan kepuasan pelanggan. Kami menyediakan berbagai layanan perawatan kuku dengan hasil yang detail dan sesuai dengan keinginanmu.'],
   'list'=>[['🏠','Home Studio','Nyaman & Private'],['✏','Desain Custom','Sesuai Keinginanmu'],['💎','Produk Berkualitas','Aman & Terpercaya']],
   'button'=>['Lebih Lanjut Tentang Kami','about.php']],
  ['type'=>'cards','id'=>'services','style'=>'mini','cols'=>5,'eyebrow'=>'LAYANAN KAMI','title'=>'Our Services','text'=>'Dapatkan perawatan terbaik untuk kuku dan penampilanmu.',
   'link'=>['Lihat Semua Layanan','services.php'],'items'=>array_slice($svc,0,5)],
  ['type'=>'strip','id'=>'gallery','eyebrow'=>'GALERI','title'=>'Hasil Karya Kami','text'=>'Lihat beberapa hasil nail art terbaik dari Yynnails sebagai inspirasi untuk tampilanmu!',
   'button'=>['Lihat Galeri Lengkap','gallery.php'],'images'=>[$I.'g1.jpg',$I.'g2.jpg',$I.'g3.jpg',$I.'g4.jpg',$I.'g5.jpg']],
  ['type'=>'cards','id'=>'pricelist','style'=>'tile','cols'=>4,'eyebrow'=>'PRICE LIST','title'=>'Daftar Harga','text'=>'Berikut adalah daftar harga untuk beberapa layanan unggulan di Yynnails.',
   'button'=>['Lihat Selengkapnya','pricelist.php'],
   'items'=>[
     ['icon'=>'🌸','title'=>'Nail Art','label'=>'Mulai dari','price'=>'Rp 50.000','url'=>'pricelist.php'],
     ['icon'=>'🎀','title'=>'Press-On Nails','label'=>'Mulai dari','price'=>'Rp 25.000','url'=>'pricelist.php'],
     ['icon'=>'✨','title'=>'Nail Tip','label'=>'Mulai dari','price'=>'Rp 100.000','url'=>'pricelist.php'],
     ['icon'=>'👁','title'=>'Lash Lift','label'=>'Mulai dari','price'=>'Rp 45.000','url'=>'pricelist.php']]],
  ['type'=>'cta','image'=>$I.'rawat.jpeg','title'=>'Yuk, rawat kuku kamu sekarang juga!','text'=>'Tampil lebih percaya diri dengan kuku yang cantik dan sehat ♡',
   'button'=>['Booking Sekarang','booking.php'],
   'side'=>[['📅','Pilih Layanan & Tanggal'],['🕒','Isi Form Booking'],['✔','Konfirmasi Dari Kami']]],
  ['type'=>'map','id'=>'contact','eyebrow'=>'KONTAK & LOKASI','title'=>'Hubungi Kami','text'=>'Jangan ragu untuk menghubungi kami jika ada pertanyaan atau ingin melakukan reservasi.',
   'items'=>[['💬','+62 823-1964-6189','Chat via WhatsApp'],['📍',"Jl. Nyiur, Perumahan Rahmindo\nPalopo, Sulawesi Selatan",'']],
   
'buttons'=>[
    ['Lihat di Google Maps','@maps','ghost'],
    ['Lihat Kontak Lengkap','contact.php','primary']
],

   
   'place' => 'Yynnails',
   'address' => "Jl. Nyiur, Perumahan Rahmindo\nPalopo, Sulawesi Selatan",
   'embed' => 'https://www.google.com/maps/embed?pb=!4v1792123227201!6m8!1m7!1sdljIufDnauzpbVieWtC-lg!2m2!1d-3.008578232000756!2d120.2112421855604!3f182.62!4f-15.939999999999998!5f0.7820865974627469'
],
]],


/* ------------------------------ ABOUT ------------------------------ */
'about' => ['title'=>'About', 'sections'=>[
  ['type'=>'hero','eyebrow'=>'TENTANG KAMI','title'=>'About Yynnails','script'=>'Small Studio, Big Dreams ♡',
   'text'=>'Yynnails adalah home studio nail art yang berfokus pada kualitas, kenyamanan, dan kepuasan pelanggan. Kami percaya bahwa setiap detail kecil, termasuk pada kuku, dapat memberikan dampak besar pada penampilan dan rasa percaya diri.',
   'buttons'=>[['Lihat Layanan','services.php'],['Hubungi Kami','contact.php','ghost']],'image'=>$I.'studio.jpeg',],
  ['type'=>'split','reverse'=>true,'image'=>$I.'sertifikat.png','eyebrow'=>'OUR STORY','title'=>'Perjalanan Kami',
   'paras'=>['Yynnails dimulai dari kecintaan terhadap seni nail art dan keinginan untuk menghadirkan layanan perawatan kuku yang berkualitas dengan sentuhan personal. Berawal dari studio kecil di rumah, kami terus berkembang dengan dukungan pelanggan yang luar biasa.',
             'Hingga kini, Yynnails telah menjadi tempat favorit bagi banyak pelanggan yang mencari perawatan kuku yang tidak hanya indah, tetapi juga nyaman dan aman.'],
   'button'=>['Kenal Lebih Dekat','contact.php']],
  ['type'=>'features','style'=>'cards','eyebrow'=>'OUR VALUES','title'=>'Nilai-Nilai Kami','text'=>'Kami selalu berkomitmen untuk memberikan yang terbaik melalui nilai-nilai yang kami pegang teguh.',
   'items'=>[['💎','Kualitas','Menggunakan bahan terbaik dan teknik profesional.'],['❤','Kenyamanan','Ruang studio yang bersih, nyaman, dan privat.'],
             ['🛡','Kepercayaan','Menjaga keamanan dan kebersihan setiap alat.'],['🪷','Kepuasan','Pelayanan ramah dan hasil sesuai harapan pelanggan.']]],
  ['type'=>'split','image'=>$I.'owner.jpeg','deco'=>"Meet\nThe Owner",'eyebrow'=>'OUR OWNER','title'=>'Yuyun Riswan, S.Pd','badge'=>'Owner & Nail Artist',
   'paras'=>['Dengan pengalaman dan ketertarikan di bidang kecantikan, Owner memimpin Yynnails dengan penuh dedikasi. Owner selalu berusaha memberikan hasil terbaik sesuai dengan keinginan dan kebutuhan setiap pelanggan.'],
   'list'=>[['✔','Berpengalaman'],['✔','Detail & Teliti'],['✔','Ramah'],['✔','Custom Design']],
   'quote'=>'Kuku yang indah bukan hanya tentang penampilan, tapi juga tentang rasa percaya diri ♡'],
  ['type'=>'cta','image'=>$I.'manis.jpeg','eyebrow'=>'READY TO BE OUR CLIENT?','title'=>"Let's Make Your Nails More Special!",
   'text'=>'Yuk, percayakan perawatan kuku kamu di Yynnails. Kami siap memberikan hasil terbaik untuk kamu dengan sepenuh hati ♡','button'=>['Book Your Appointment','booking.php'],
   'side'=>[['🌿','Hasil Premium'],['✨','Desain Eksklusif'],['❤','Pelayanan Personal']]],
]],

/* ----------------------------- SERVICES ----------------------------- */
'services' => ['title'=>'Services', 'sections'=>[
  ['type'=>'hero','eyebrow'=>'LAYANAN KAMI','title'=>'Our Services','script'=>'Nail Care & Beauty Treatment ♡',
   'text'=>'Di Yynnails, kami menyediakan berbagai layanan perawatan kuku dan kecantikan yang dirancang khusus untuk meningkatkan kepercayaan diri dan menonjolkan sisi terbaikmu.',
   'buttons'=>[['Booking Sekarang','booking.php'],['Lihat Harga','pricelist.php','ghost']],'image'=>$I.'banner services.jpeg','deco'=>"Good\nNails\nGood\nMood ♡"],
  ['type'=>'cards','style'=>'service','cols'=>3,'filter'=>$catsS,'items'=>$svc],
  ['type'=>'steps','image'=>$I.'BA.jpeg','deco'=>"Step\nby Step ♡",'eyebrow'=>'PROSES LAYANAN','title'=>'Bagaimana Cara Kerjanya?',
   'text'=>'Kami selalu memastikan setiap proses dilakukan dengan hati-hati, higienis, dan sesuai dengan standar kualitas untuk hasil terbaik.',
   'items'=>[['💬','Konsultasi','Desain & Kebutuhan'],['💅','Persiapan','Kuku & Alat'],['✨','Proses','Perawatan'],['❤','Hasil Akhir','Yang Memuaskan']]],
  ['type'=>'features','style'=>'bar','items'=>[['💎','Produk Berkualitas','Menggunakan produk terbaik dan aman untuk kulit.'],['🛡','Higienis & Aman','Alat dan ruang kerja selalu steril dan terjaga kebersihannya.'],
     ['✨','Tenaga Profesional','Dikerjakan oleh ahli yang berpengalaman di bidangnya.'],['❤','Custom Design','Desain sesuai keinginan dan karakter kamu.']]],
  ['type'=>'cta','image'=>$I.'servis.jpeg','eyebrow'=>'SIAP TAMPIL LEBIH CANTIK?','title'=>'Book Your Appointment Now!','text'=>'Dapatkan pengalaman perawatan kuku terbaik bersama Yynnails.','button'=>$footerCta],
]],

/* ------------------------------ GALLERY ------------------------------ */
'gallery' => ['title'=>'Gallery', 'sections'=>[
  ['type'=>'hero','eyebrow'=>'GALERI YYNNAILS','title'=>'Our Gallery','script'=>'Real Nails, Real Beauty ♡',
   'text'=>'Koleksi hasil karya terbaik dari Yynnails, mulai dari nail art custom, press-on nails, nail tip, hingga berbagai desain lainnya. Temukan inspirasi untuk tampilan kuku impianmu di sini!',
   'buttons'=>[['Lihat Semua Foto','#semua']],
'image'=>$I.'banner galeri.jpeg',],

  ['type'=>'gallery','filter'=>$cats,'items'=>[
     ['img'=>$I.'g1.jpg','title'=>'Nail Art','count'=>'12 Foto','cat'=>'nailart'],['img'=>$I.'g2.jpg','title'=>'Press-on Nails','count'=>'10 Foto','cat'=>'presson'],
     ['img'=>$I.'g3.jpg','title'=>'Nail Tips','count'=>'14 Foto','cat'=>'tip'],['img'=>$I.'lash-lift.jpg','title'=>'Lash Lift','count'=>'8 Foto','cat'=>'lash'],
     ['img'=>$I.'manicure.jpg','title'=>'Manicure','count'=>'9 Foto','cat'=>'mani'],
     ['img'=>$I.'studio-vibes.jpg','title'=>'Studio Vibes','count'=>'7 Foto','cat'=>'studio'],['img'=>$I.'custom.jpg','title'=>'Custom Design','count'=>'15 Foto','cat'=>'nailart']]],
  ['type'=>'detail','eyebrow'=>'NAIL ART','title'=>'Nail Art Custom','text'=>'Desain nail art yang dapat disesuaikan dengan keinginan pelanggan.',
   'images'=>[$I.'11.jpeg',$I.'22.jpeg',$I.'33.jpeg',$I.'44.jpeg',$I.'55.jpeg',$I.'66.jpeg'],'tags'=>['#NailArt','#Custom'],
   'share'=>[['WA','@wa'],['IG','@ig'],['TT','@tt'],['🔗','@copy']]],
  ['type'=>'cta','deco'=>"Get Inspired\nWith Yynnails ♡",'title'=>'Punya desain favorit?','text'=>'Konsultasikan ide kuku impianmu bersama kami!','button'=>$footerCta,
   'images'=>[$I.'1.jpeg',$I.'2.jpeg',$I.'3.jpeg']],
]],

/* -----------------------------]
/* ----------------------------- PRICE LIST ----------------------------- */
'pricelist' => ['title'=>'Price List', 'sections'=>[
  ['type'=>'hero','eyebrow'=>'HARGA LAYANAN','title'=>'Price List','script'=>'Make Your Nails Worth It ♡',
   'text'=>'Temukan berbagai layanan perawatan kuku dan kecantikan lainnya di Yynnails dengan harga yang transparan dan bersahabat. Kami selalu berusaha memberikan hasil terbaik dengan kualitas premium untuk setiap pelanggan.',
   'buttons'=>[['Booking Sekarang','booking.php'],['Lihat Galeri','gallery.php','ghost']],
   'image'=>$I.'banner price.jpeg',
   'deco'=>"Good\nNails\nGood\nMood ♡"],

  ['type'=>'cards','style'=>'price','cols'=>3,'title'=>'Layanan Nail Art',
   'text'=>'Berbagai pilihan desain untuk mempercantik kuku kamu.',
   'badge'=>'Populer ♡','items'=>[
     ['img'=>$I.'art.jpg','title'=>'Nail Art','text'=>'Desain kuku custom sesuai keinginan, mulai dari yang simple hingga detail.','price'=>'Mulai dari Rp 50.000','url'=>'booking.php'],
     ['img'=>$I.'on.jpg','title'=>'Press-On Nails','text'=>'Kuku instan dengan desain trendy dan mudah digunakan.','price'=>'Mulai dari Rp 25.000','url'=>'booking.php'],
     ['img'=>$I.'tip price.jpg','title'=>'Nail Tip','text'=>'Penambahan dan perpanjangan kuku dengan hasil natural dan rapi.','price'=>'Mulai dari Rp 100.000','url'=>'booking.php'],
     ['img'=>$I.'manicure.jpg','title'=>'Manicure','text'=>'Perawatan dasar untuk menjaga kesehatan dan kecantikan kuku.','price'=>'Mulai dari Rp 50.000','url'=>'booking.php'],
     ['img'=>$I.'1.jpeg','title'=>'Custom Design','text'=>'Desain sesuai tema, warna, dan request khusus kamu.','price'=>'Mulai dari Rp 50.000','url'=>'booking.php']
   ]],

  ['type'=>'cards','style'=>'photo','cols'=>3,'title'=>'Layanan Lainnya',
   'text'=>'Perawatan tambahan untuk hasil yang lebih maksimal.',
   'items'=>[
     ['img'=>$I.'lift.png','title'=>'Lash Lift','text'=>'Membuat bulu mata terlihat lebih lentik dan natural.','price'=>'Mulai dari Rp 45.000','url'=>'booking.php'],
     ['img'=>$I.'remov.jpeg','title'=>'Remover','text'=>'Pembersihan/pelepasan nail art dengan aman dan terpercaya','price'=>'Mulai dari Rp 3.000/Jari','url'=>'booking.php'],
     
   ]],

  
  
  
  ['type'=>'extras','title'=>'Catatan Penting',
   'rows'=>[],
   'notes_title'=>'Catatan Penting',
   'notes'=>[
     'Harga dapat berubah sewaktu-waktu.',
     'Untuk desain custom, harga menyesuaikan tingkat kesulitan.',
     'Mohon melakukan booking terlebih dahulu untuk memastikan ketersediaan jadwal.'
   ]],



  ['type'=>'cta','image'=>$I.'ayo rawat.png',
   'title'=>'Yuk, rawat kuku kamu sekarang juga!',
   'text'=>'Dapatkan tampilan kuku yang cantik, sehat, dan percaya diri bersama Yynnails.',
   'button'=>$footerCta]
]],


/* ------------------------------ CONTACT ------------------------------ */
'contact' => ['title'=>'Contact', 'sections'=>[
  ['type'=>'hero','eyebrow'=>'CONTACT US','title'=>'Get In Touch','script'=>"We're Here for You ♡",
   'text'=>'Punya pertanyaan, ingin konsultasi desain, atau butuh informasi lebih lanjut? Jangan ragu untuk menghubungi kami. Tim Yynnails siap membantu kamu dengan senang hati!',
   'badges'=>[['💬','Fast Response','via WhatsApp'],['🛡','Konsultasi','Gratis'],['❤','Mudah','& Cepat']],'image'=>$I.'banner contact.jpeg','deco'=>"Nail Art\nMake You\nConfident ♡"],
  ['type'=>'contact','title'=>'Informasi Kontak','text'=>'Hubungi kami melalui berbagai cara berikut:',
   'items'=>[['💬','WhatsApp','+62 823 1964 6189','(Chat langsung untuk respon cepat)','@wa'],['📷','Instagram','@yynnails_','(Lihat hasil karya terbaru)','@ig'],
             ['✉','Email','yynailsstudio@gmail.com','(Untuk pertanyaan lebih detail)','@mail'],['📍','Alamat',"Nyiur, Perumahan Rahmindo\nPalopo, Sulawesi Selatan",'(Lihat lokasi di peta)','@maps']],
   'hours'=>['Jam Operasional','10:00 – 21:00 WITA','(Bisa lebih lama jika sedang ramai)'],
   'form'=>['title'=>'Kirim Pesan','text'=>'Tulis pesan kamu di bawah ini, kami akan segera membalasnya secepat mungkin ♡',
     'fields'=>[['Nama Lengkap','nama','text','Masukkan nama lengkap kamu',1],['Email (opsional)','email','email','Masukkan email kamu'],
                ['No. WhatsApp','wa','tel','Contoh: 0823 1964 6189',1],['Pesan','pesan','textarea','Tulis pesan, pertanyaan, atau kebutuhan kamu di sini...',1]],
     'consent'=>'Saya menyetujui bahwa data yang saya kirim akan digunakan untuk keperluan komunikasi dan pelayanan dari Yynnails.','submit'=>'Kirim Pesan']],
  ['type'=>'map','title'=>'Lokasi Kami','buttons'=>[['Lihat di Google Maps','@maps','ghost']],'place'=>'Yynnails',
'address'=>"Jl. Nyiur, Perumahan Rahmindo\nPalopo, Sulawesi Selatan",
'embed'=>'https://maps.google.com/maps?q=Perumahan%20Rahmindo%20Palopo&output=embed'],   // tempel URL embed Google Maps di sini jika ingin peta asli
  ['type'=>'follow','title'=>'Follow Kami','text'=>'Jangan lupa follow akun sosial media kami untuk update terbaru, promo, dan hasil nail art lainnya!','handle'=>'@yynnails_',
   'chat'=>['Ada pertanyaan lain?','Kamu juga bisa langsung chat via WhatsApp untuk respon lebih cepat!','Chat via WhatsApp']],
]],

/* ------------------------------ BOOKING ------------------------------ */
'booking' => ['title'=>'Booking', 'sections'=>[
  ['type'=>'hero','eyebrow'=>'BOOKING SEKARANG','title'=>'Pesan Jadwal Nail Art Kamu','script'=>'dengan Mudah ♡',
   'text'=>'Isi form di bawah ini untuk melakukan pemesanan layanan nail art. Tim Yynnails akan segera menghubungi kamu untuk konfirmasi jadwal.',
   'badges'=>[['🛡','Mudah','& Cepat'],['🛡','Konfirmasi','Via WhatsApp'],['❤','Tanpa','Biaya Online']],'image'=>$I.'banner booking 1.png','deco'=>"Pretty\nNails\nHappy\nYou ♡"],
  ['type'=>'booking','steps'=>['Data Diri','Pilih Layanan','Upload Referensi','Konfirmasi'],
   'panes'=>[['Data Diri','Lengkapi informasi kamu untuk melanjutkan pemesanan.'],['Pilih Layanan','Pilih layanan yang kamu butuhkan.'],
             ['Desain / Referensi (Opsional)','Upload foto desain yang kamu inginkan atau contoh referensi lainnya.'],['Konfirmasi','Periksa kembali data pemesananmu sebelum dikirim.']],
   'fields'=>[['Nama Lengkap','name','text','Masukkan nama lengkap kamu',1],['Nomor WhatsApp','phone','tel','Contoh: 0812 3456 7890',1],
              ['Tanggal Booking','date','date','',1],['Waktu Pilih','time','select','Pilih waktu',1,['10:00','11:00','13:00','14:00','15:00','16:00','17:00','19:00']]],
   'services'=>[['✦','Nail Art','Rp 50.000 – 150.000'],['🎀','Press-on Nails','Rp 80.000 – 200.000'],['🍃','Nail Tip','Rp 70.000 – 180.000'],
                ['👁','Lash Lift','Rp 100.000 – 200.000'],['🪷','Manicure','Rp 50.000 – 100.000']],
   'upload'=>['Klik untuk mengunggah gambar','Format: JPG, PNG | Maks. 5MB'],
   'notes'=>['Catatan Tambahan (Opsional)','Contoh: desain khusus, warna, ukuran, atau permintaan lainnya...'],
   'consent'=>'Saya telah membaca dan menyetujui syarat & ketentuan pemesanan dari Yynnails.',
   'btn'=>['Lanjut ke Langkah','Kirim Booking via WhatsApp','‹ Kembali'],
   'summary'=>['title'=>'Ringkasan Pemesanan','image'=>$I.'summary.jpg','rows'=>[['📅','Tanggal','date'],['🕒','Waktu','time'],['💅','Layanan','service'],['🏷','Estimasi Harga','price']],
               'info'=>'Setelah mengisi form, tim Yynnails akan menghubungi kamu melalui WhatsApp untuk konfirmasi jadwal.','script'=>'"See you soon" ♡'],
   'help'=>['Butuh Bantuan?','Hubungi kami via WhatsApp','Chat di WhatsApp'],
'status_button'=>['Sudah booking?','Cek status pesananmu di sini','Cek Status Booking →']],
]],

/* --------------------------- CEK STATUS BOOKING --------------------------- */
'status' => ['title'=>'Cek Status Booking', 'sections'=>[
  ['type'=>'status','eyebrow'=>'CHECK BOOKING STATUS','title'=>'Cek Status Booking Kamu','text'=>'Masukkan kode booking untuk melihat informasi pesanan kamu dengan mudah.',
   'tabs'=>['Cek Status Booking','Informasi'],'label'=>'Kode Booking','placeholder'=>'Contoh: YN20250925-001','button'=>'Cek Sekarang',
   'info'=>'Kamu bisa menggunakan kode booking yang sudah diberikan.',
   'notfound'=>'Booking tidak ditemukan. Periksa kembali kode booking kamu.',
   // contoh data booking — ganti / tambah sesuai kebutuhan (nanti bisa disambungkan ke database)
   'bookings'=>[['code'=>'YN20250925-001','wa'=>'0858 2528 0212','name'=>'Contoh Pelanggan','service'=>'Nail Art','date'=>'25 September 2025','time'=>'14:00 WITA','status'=>'Dikonfirmasi','note'=>'Sampai jumpa di studio ya ♡']]],
  ['type'=>'features','style'=>'bar','items'=>[['🛡','Aman & Terpercaya','Data kamu terlindungi'],['⚡','Proses Cepat','Cek hanya dalam hitungan detik'],['❤','Mudah Digunakan','Tanpa ribet']]],
]],

/* ---- HALAMAN KUOTA PENUH ------------------------------------------------
   Tampil kalau pelanggan memilih tanggal yang kuotanya sudah habis.
   Tulisan yang bisa dipakai di teks:
     {layanan} = layanan yang dipilih pelanggan
     {tanggal} = tanggal yang dipilih (contoh: 12 Desember 2026)
     {batas}   = batas per hari (dari 'kuota' di atas)
   Tombol: ['Tulisan tombol', 'tujuan', 'gaya'] — gaya boleh dikosongkan. */
'kuota_penuh' => ['title'=>'Kuota Penuh', 'sections'=>[
  ['type'=>'kuota',
   'icon'=>'📅',
   'title'=>'Kuota Sudah Penuh',
   'text'=>'Maaf, jadwal {layanan} pada tanggal {tanggal} sudah penuh.',
   'label_layanan'=>'Layanan',
   'label_tanggal'=>'Tanggal yang dipilih',
   'hint_title'=>'ℹ️ Kenapa bisa penuh?',
   'hint'=>'Nail Art dan Nail Tip dibatasi maksimal {batas} pelanggan per hari. Silakan pilih tanggal lain, ya. Layanan lain masih bisa dipesan di tanggal ini selama jamnya tersedia.',
   'buttons'=>[
     ['Pilih Tanggal Lain', 'booking.php?isi=1'],
     ['Tanya Jadwal via WhatsApp', '@wa', 'ghost'],
     ['Kembali ke Beranda', 'index.php', 'ghost'],
   ]],
]],

],
];