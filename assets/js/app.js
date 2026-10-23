(() => {
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const WA = document.body.dataset.wa;
  const wa = t => window.open(`https://wa.me/${WA}?text=${encodeURIComponent(t)}`, '_blank');

  // menu mobile
  $('.burger')?.addEventListener('click', () => $('.top').classList.toggle('open'));

  // Navigasi aktif saat scroll
  const navLinks = $$('.top nav a[data-nav]');
  const sections = navLinks
    .map(link => document.getElementById(link.dataset.nav))
    .filter(Boolean);

  const setActive = id => {
    navLinks.forEach(link => {
      const active = link.dataset.nav === id;
      link.classList.toggle('on', active);

      if (active) {
        link.setAttribute('aria-current', 'location');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  };

  if (sections.length) {
    const observer = new IntersectionObserver(entries => {
      const visible = entries
        .filter(entry => entry.isIntersecting)
        .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

      if (visible.length) {
        setActive(visible[0].target.id);
      }
    }, {
      rootMargin: '-100px 0px -55% 0px',
      threshold: [0, 0.1, 0.25, 0.5]
    });

    sections.forEach(section => observer.observe(section));
  }

  // filter kategori (kartu layanan & galeri)
  $$('[data-filter-scope]').forEach(sc => $$('.chip', sc).forEach(c => c.onclick = () => {
    $$('.chip', sc).forEach(x => x.classList.toggle('on', x === c));
    $$('.card', sc).forEach(k => k.hidden = !(c.dataset.f === '*' || k.dataset.cat === c.dataset.f));
  }));

  // slider detail galeri
  $$('.slider').forEach(s => {
    const im = JSON.parse(s.dataset.imgs); let i = 0;
    const show = () => {
      const t = $('img', s); if (t) t.src = im[i]; else { const p = $('.ph span', s); if (p) p.textContent = im[i].split('/').pop(); }
      $('.cnt', s).textContent = `${i + 1} / ${im.length}`;
    };
    $('.prev', s).onclick = () => { i = (i - 1 + im.length) % im.length; show(); };
    $('.next', s).onclick = () => { i = (i + 1) % im.length; show(); };
  });

  // form kontak -> WhatsApp
  $$('[data-wa-form]').forEach(f => f.addEventListener('submit', e => {
    e.preventDefault();
    wa('Halo Yynnails!\n' + $$('[data-l]', f).filter(x => x.value).map(x => `${x.dataset.l}: ${x.value}`).join('\n'));
  }));

  // booking 4 langkah
  const bk = $('#bk');
  if (bk) {
    const P = $$('.pane', bk), S = $$('.stp li', bk), N = $('#next'), B = $('#prev'); let n = 0;
    const lines = () => $$('[data-l]', bk).filter(x => x.type === 'radio' ? x.checked : x.value)
      .map(x => `${x.dataset.l}: ${x.type === 'file' ? x.files[0]?.name : (x.type === 'radio' ? x.value + ' (' + x.dataset.p + ')' : x.value)}`);
    const upd = () => {
      const v = k => $(`[name=${k}]`, bk).value || '-', r = $('[name=service]:checked', bk);
      $$('[data-s]').forEach(e => e.textContent = ({ date: v('date'), time: v('time'), service: r ? r.value : '-', price: r ? r.dataset.p : '-' })[e.dataset.s]);
    };
    const go = i => {
      n = i;
      P.forEach((p, k) => p.classList.toggle('on', k === i));
      S.forEach((s, k) => s.classList.toggle('on', k <= i));
      B.hidden = i === 0;
      N.textContent = i === P.length - 1 ? N.dataset.send : `${N.dataset.next} ${i + 2} →`;
      
      if (i === P.length - 1) {
        const fileInput = $('[name=referensi]', bk);
        const file = fileInput.files[0];

        const daftar = lines().map(l => `<li>${l}</li>`).join('');

        let gambar = '';
        if (file && file.type.startsWith('image/')) {
          const url = URL.createObjectURL(file);
          gambar = `
            <li style="display:block">
              <b>Referensi Desain:</b><br>
              <img
                src="${url}"
                alt="Referensi desain pilihan pelanggan"
                style="display:block;width:100%;max-width:260px;max-height:260px;object-fit:contain;margin-top:10px;border-radius:12px"
              >
            </li>`;
        }

        $('#review').innerHTML = daftar + gambar;
      }

    };
    bk.addEventListener('input', upd); bk.addEventListener('change', upd);
    B.onclick = () => go(n - 1);
    N.onclick = () => {
      const bad = $$('[required]', P[n]).find(x => !x.checkValidity());
      if (bad) return bad.reportValidity();
      if (n < P.length - 1) return go(n + 1);
      bk.submit();
    };
      
    
    $('.drop input', bk).addEventListener('change', e => {
      const file = e.target.files[0];
      $('.drop b', bk).textContent = '✔ ' + (file?.name || '');

      const oldPreview = $('#preview-referensi');
      if (oldPreview) oldPreview.remove();

      const summary = $('#summary-reference');
      const summaryImg = $('#summary-reference-img');

      if (summary) summary.style.display = 'none';
      if (summaryImg) summaryImg.removeAttribute('src');

      if (file && file.type.startsWith('image/')) {
        const url = URL.createObjectURL(file);

        const preview = document.createElement('div');
        preview.id = 'preview-referensi';
        preview.style.marginTop = '12px';

        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Pratinjau referensi desain';
        img.style.width = '100%';
        img.style.maxWidth = '240px';
        img.style.maxHeight = '240px';
        img.style.objectFit = 'contain';
        img.style.borderRadius = '12px';
        img.style.display = 'block';

        preview.appendChild(img);
        $('.drop', bk).appendChild(preview);

        if (summary && summaryImg) {
          summaryImg.src = url;
          summary.style.display = 'block';
        }
      }
    });

  }

  // tab cek status
  $$('.tabs button').forEach(b => b.onclick = () => {
    $$('.tabs button').forEach(x => x.classList.toggle('on', x === b));
    $$('.tp').forEach(p => p.hidden = p.id !== b.dataset.t);
  });
})();

document.addEventListener('click', async function (event) {
  const button = event.target.closest('.copy-link');
  if (!button) return;

  event.preventDefault();

  try {
    await navigator.clipboard.writeText(window.location.href);
    alert('Tautan berhasil disalin!');
  } catch (error) {
    const input = document.createElement('textarea');
    input.value = window.location.href;
    input.style.position = 'fixed';
    input.style.left = '-9999px';
    document.body.appendChild(input);
    input.select();

    const berhasil = document.execCommand('copy');
    input.remove();

    alert(
      berhasil
        ? 'Tautan berhasil disalin!'
        : 'Gagal menyalin. Salin alamat dari browser.'
    );
  }
});
