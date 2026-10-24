/* admin.js — skrip bersama halaman admin (login & ubah password) */

// Tampilkan / sembunyikan isi kolom password
function togglePassword(id, button) {
    const input = document.getElementById(id);
    const tampil = input.type === 'password';

    input.type = tampil ? 'text' : 'password';
    button.textContent = tampil ? '🙈' : '👁';
    button.setAttribute('aria-pressed', String(tampil));
    button.setAttribute(
        'aria-label',
        tampil ? 'Sembunyikan password' : 'Tampilkan password'
    );
}
