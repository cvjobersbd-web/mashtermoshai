<?php /** KYC ফর্মের কমন স্টাইল (ky-*) + পোস্ট হেল্পার */ ?>
<style>
    .ky-page { background: linear-gradient(180deg, #EEF6FC, #F6F9FC 320px); min-height: 80vh; padding: 30px 0 70px; }
    .ky-wrap { max-width: 880px; margin: 0 auto; padding: 0 16px; }
    .ky-h1 { font-family: var(--font-heading); font-size: clamp(24px, 2.6vw, 32px); font-weight: 800; color: #0B1F44; margin: 0; }
    .ky-sub { color: #64748B; font-size: 15px; margin: 6px 0 20px; line-height: 1.6; }
    .ky-card { background: #fff; border: 1px solid #E3EAF3; border-radius: 18px; box-shadow: 0 6px 22px rgba(15,52,103,.05); padding: 22px; margin-bottom: 16px; }
    .ky-card > h3 { margin: 0 0 16px; font-size: 17px; font-weight: 800; color: #0B1F44; display: flex; align-items: center; gap: 10px; }
    .ky-card > h3 .n { width: 28px; height: 28px; border-radius: 50%; background: #E0F2FE; color: #0369A1; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }
    .ky-g2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 16px; }
    .ky-g3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0 16px; }
    .ky-f { margin-bottom: 14px; min-width: 0; }
    .ky-f label.l { display: block; font-size: 13.5px; font-weight: 700; color: #1E293B; margin-bottom: 6px; }
    .ky-f label.l .r { color: #DC2626; } .ky-f label.l small { color: #94A3B8; font-weight: 500; }
    .ky-f input[type=text], .ky-f input[type=tel], .ky-f input[type=number], .ky-f select, .ky-f textarea {
        width: 100%; border: 1.5px solid #DCE6F0; border-radius: 11px; padding: 10px 13px; font-size: 15px; font-family: inherit; outline: none; background: #fff; color: #0F172A; height: 46px; }
    .ky-f textarea { height: auto; min-height: 70px; resize: vertical; line-height: 1.5; }
    .ky-f input:focus, .ky-f select:focus, .ky-f textarea:focus { border-color: #0EA5E9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
    .ky-f.bad input, .ky-f.bad select, .ky-f.bad textarea, .ky-f.bad .ky-drop { border-color: #DC2626; }
    .ky-f .h { font-size: 12.5px; color: #94A3B8; margin-top: 5px; }
    .ky-chk { display: inline-flex; gap: 7px; align-items: center; font-size: 13px; color: #475569; margin-top: 6px; cursor: pointer; }
    .ky-ex { border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 14px 16px 2px; margin-bottom: 14px; background: #FAFCFF; }
    .ky-ex.bad { border-color: #DC2626; }
    .ky-ex h4 { margin: 0 0 10px; font-size: 15.5px; font-weight: 800; color: #0F172A; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .ky-seg { display: inline-flex; background: #EEF2F7; border-radius: 10px; padding: 3px; gap: 3px; }
    .ky-seg label { padding: 6px 14px; border-radius: 8px; font-size: 13.5px; font-weight: 700; color: #475569; cursor: pointer; display: inline-flex; gap: 6px; align-items: center; }
    .ky-seg input { display: none; }
    .ky-seg label:has(input:checked) { background: #fff; color: #0369A1; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .ky-seg label.run:has(input:checked) { color: #B45309; }
    .ky-drop { display: flex; align-items: center; justify-content: center; border: 2px dashed #BFE3F2; border-radius: 14px; background: #F5FBFF; min-height: 180px; cursor: pointer; overflow: hidden; text-align: center; padding: 10px; }
    .ky-drop:hover { border-color: #0EA5E9; }
    .ky-drop img { max-width: 100%; max-height: 230px; border-radius: 10px; }
    .ky-drop span { display: flex; flex-direction: column; align-items: center; gap: 4px; color: #475569; font-size: 14px; }
    .ky-drop span i { font-size: 32px; color: #0EA5E9; } .ky-drop small { color: #94A3B8; font-size: 12px; }
    .ky-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 50px; padding: 0 26px; border-radius: 13px; border: none; cursor: pointer; font-family: inherit;
        font-weight: 800; font-size: 16px; text-decoration: none; background: linear-gradient(135deg, #0EA5E9, #0369A1); color: #fff; }
    .ky-btn:disabled { opacity: .6; } .ky-btn.ghost { background: #F1F5F9; color: #334155; }
    .ky-msg { font-size: 14px; font-weight: 600; } .ky-msg.err { color: #B91C1C; } .ky-msg.ok { color: #15803D; }
    .ky-state { display: flex; gap: 16px; align-items: flex-start; border-radius: 18px; padding: 20px 22px; margin-bottom: 18px; border: 1.5px solid; }
    .ky-state > i { font-size: 30px; margin-top: 2px; }
    .ky-state b { display: block; font-size: 18px; margin-bottom: 4px; } .ky-state p { margin: 0; font-size: 14.5px; line-height: 1.6; }
    .ky-state.p { background: #FFFBEB; border-color: #FDE68A; color: #92400E; }
    .ky-state.ok { background: #F0FDF4; border-color: #86EFAC; color: #166534; }
    .ky-state.no { background: #FEF2F2; border-color: #FECACA; color: #991B1B; }
    .ky-sum { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 20px; font-size: 14px; }
    .ky-sum div small { display: block; color: #64748B; font-size: 12px; font-weight: 700; } .ky-sum div b { color: #0F172A; overflow-wrap: anywhere; }
    .ky-ids { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 14px; }
    .ky-ids img { width: 100%; max-height: 200px; object-fit: contain; background: #F1F5F9; border-radius: 12px; border: 1px solid #E2E8F0; }
    .ky-lock { font-size: 13px; color: #475569; background: #F1F5F9; border-radius: 10px; padding: 9px 12px; margin-bottom: 14px; }
    @media (max-width: 720px) { .ky-g2, .ky-g3, .ky-sum, .ky-ids { grid-template-columns: minmax(0, 1fr); } .ky-card { padding: 18px 16px; } }
</style>
<script>
window.kyPost = async function (url, form) {
    var fd = new FormData(form);
    var t = document.querySelector('input[name="__RequestVerificationToken"]'); if (t && !fd.has('__RequestVerificationToken')) fd.append('__RequestVerificationToken', t.value);
    try { var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }); return await r.json(); }
    catch (e) { return { success: false, message: 'ইন্টারনেট / সার্ভারে সমস্যা — ছবি বড় হলে ছোট করে আবার চেষ্টা করুন।' }; }
};
window.kyBind = function (form, url, btn, msg) {
    form.querySelectorAll('[data-file]').forEach(function (inp) {
        inp.addEventListener('change', function () { var f = inp.files[0]; if (!f) return; var box = document.getElementById(inp.dataset.file);
            box.querySelector('img').src = URL.createObjectURL(f); box.querySelector('img').hidden = false; box.querySelector('span').hidden = true; });
    });
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        form.querySelectorAll('.bad').forEach(function (x) { x.classList.remove('bad'); });
        btn.disabled = true; msg.className = 'ky-msg'; msg.textContent = 'জমা হচ্ছে...';
        var d = await kyPost(url, form);
        btn.disabled = false; msg.className = 'ky-msg ' + (d.success ? 'ok' : 'err'); msg.textContent = d.message || '';
        if (!d.success && d.field) { var x = form.querySelector('[data-f="' + d.field + '"]'); if (x) { x.classList.add('bad'); x.scrollIntoView({ behavior: 'smooth', block: 'center' }); } }
        if (d.success && d.redirect) setTimeout(function () { location.href = d.redirect; }, 700);
    });
};
</script>
