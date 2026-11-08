<?php /** কোর্সের সব পেজের কমন স্টাইল (cx-*) */ ?>
<style>
    .cx-page { background: #F3F7FC; min-height: 75vh; padding: 28px 0 70px; }
    .cx-wrap { max-width: 1180px; margin: 0 auto; padding: 0 16px; }
    .cx-wrap.narrow { max-width: 920px; }
    .cx-back { display: inline-flex; align-items: center; gap: 7px; color: #475569; font-weight: 700; font-size: 14px; text-decoration: none; margin-bottom: 12px; }
    .cx-back:hover { color: #0369A1; }
    .cx-h1 { font-family: var(--font-heading); font-size: clamp(24px, 2.6vw, 34px); font-weight: 800; color: #0B1F44; margin: 0; }
    .cx-sub { color: #64748B; font-size: 15px; margin: 6px 0 0; }
    .cx-card { background: #fff; border: 1px solid #E3EAF3; border-radius: 18px; box-shadow: 0 6px 22px rgba(15,52,103,.05); padding: 20px; margin-bottom: 18px; min-width: 0; }
    .cx-card > h3 { margin: 0 0 14px; font-size: 17px; font-weight: 800; color: #0B1F44; display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
    .cx-card > h3 i { color: #0EA5E9; }
    .cx-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 11px; border: none; cursor: pointer; font-family: inherit;
        font-weight: 700; font-size: 14px; text-decoration: none; background: #08224A; color: #fff; white-space: nowrap; transition: filter .15s, transform .15s; }
    .cx-btn:hover { filter: brightness(1.08); color: #fff; }
    .cx-btn:disabled { opacity: .55; cursor: default; }
    .cx-btn.pri { background: linear-gradient(135deg, #0EA5E9, #0369A1); }
    .cx-btn.ok { background: #15803D; } .cx-btn.warn { background: #F59E0B; color: #fff; } .cx-btn.red { background: #DC2626; }
    .cx-btn.live { background: linear-gradient(135deg, #EF4444, #B91C1C); }
    .cx-btn.ghost { background: #F1F5F9; color: #334155; } .cx-btn.ghost:hover { color: #0F172A; background: #E2E8F0; }
    .cx-btn.sm { height: 34px; padding: 0 12px; font-size: 13px; border-radius: 9px; }
    .cx-btn.lg { height: 50px; padding: 0 24px; font-size: 16px; border-radius: 13px; }
    .cx-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; background: #F1F5F9; color: #475569; white-space: nowrap; }
    .cx-pill.ok { background: #DCFCE7; color: #166534; } .cx-pill.warn { background: #FEF3C7; color: #92400E; } .cx-pill.no { background: #FEE2E2; color: #991B1B; }
    .cx-pill.blue { background: #E0F2FE; color: #0369A1; } .cx-pill.live { background: #FEE2E2; color: #B91C1C; } .cx-pill.purple { background: #F3E8FF; color: #7E22CE; }
    .cx-price { font-weight: 800; color: #0B1F44; } .cx-price s { color: #94A3B8; font-weight: 600; font-size: .8em; margin-left: 6px; }
    .cx-price.free { color: #15803D; }
    .cx-thumb { aspect-ratio: 16 / 9; background: linear-gradient(135deg, #0B3B6B, #0EA5E9); color: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
    .cx-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .cx-thumb .ini { font-size: 34px; font-weight: 800; letter-spacing: 1px; opacity: .9; padding: 0 16px; text-align: center; line-height: 1.2; }
    .cx-f { margin-bottom: 14px; }
    .cx-f label { display: block; font-size: 13.5px; font-weight: 700; color: #1E293B; margin-bottom: 6px; }
    .cx-f label small { color: #94A3B8; font-weight: 500; }
    .cx-f input[type=text], .cx-f input[type=number], .cx-f input[type=url], .cx-f input[type=datetime-local], .cx-f select, .cx-f textarea {
        width: 100%; border: 1.5px solid #DCE6F0; border-radius: 11px; padding: 10px 13px; font-size: 14.5px; font-family: inherit; outline: none; background: #fff; color: #0F172A; }
    .cx-f textarea { min-height: 90px; resize: vertical; line-height: 1.55; }
    .cx-f input:focus, .cx-f select:focus, .cx-f textarea:focus { border-color: #0EA5E9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
    .cx-f.bad input, .cx-f.bad textarea, .cx-f.bad select { border-color: #DC2626; }
    .cx-f .hint { font-size: 12.5px; color: #94A3B8; margin-top: 5px; }
    .cx-grid2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 16px; }
    .cx-grid3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0 16px; }
    .cx-msg { font-size: 13.5px; font-weight: 600; } .cx-msg.ok { color: #15803D; } .cx-msg.err { color: #B91C1C; }
    .cx-empty { text-align: center; color: #94A3B8; padding: 34px 10px; }
    .cx-empty i { font-size: 36px; display: block; margin-bottom: 10px; color: #CBD5E1; }
    .cx-bar { height: 9px; background: #E2E8F0; border-radius: 999px; overflow: hidden; }
    .cx-bar > div { height: 100%; background: linear-gradient(90deg, #22C55E, #16A34A); border-radius: 999px; transition: width .4s; }
    .cx-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .cx-stat { background: #fff; border: 1px solid #E3EAF3; border-radius: 14px; padding: 14px 16px; border-top: 4px solid var(--c, #0EA5E9); }
    .cx-stat small { display: block; color: #64748B; font-size: 12px; font-weight: 700; } .cx-stat b { display: block; font-size: 22px; color: #0F172A; margin-top: 3px; }
    .cx-tw { overflow-x: auto; }
    .cx-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 640px; }
    .cx-tbl th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 8px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .cx-tbl td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
    .cx-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 11px 18px; border-radius: 11px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; max-width: 92vw; text-align: center; }
    .cx-dlg { position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 1060; display: none; align-items: flex-start; justify-content: center; padding: 40px 14px; overflow-y: auto; }
    .cx-dlg.open { display: flex; }
    .cx-dlg-box { background: #fff; border-radius: 18px; width: min(680px, 100%); padding: 22px; box-shadow: 0 30px 60px rgba(0,0,0,.25); }
    .cx-dlg-box h3 { margin: 0 0 14px; font-size: 18px; font-weight: 800; color: #0B1F44; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .cx-dlg-box h3 button { border: none; background: #F1F5F9; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; color: #334155; }
    @media (max-width: 860px) { .cx-grid2, .cx-grid3 { grid-template-columns: minmax(0, 1fr); } .cx-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
<script>
window.cxPost = async function (url, data, file) {
    var fd = data instanceof FormData ? data : new FormData();
    if (!(data instanceof FormData)) Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });
    var t = document.querySelector('input[name="__RequestVerificationToken"]'); if (t && !fd.has('__RequestVerificationToken')) fd.append('__RequestVerificationToken', t.value);
    try { return await (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); }
    catch (e) { return { success: false, message: 'ইন্টারনেট / সার্ভারে সমস্যা — আবার চেষ্টা করুন।' }; }
};
window.cxToast = function (m) { var t = document.getElementById('cxToast'); if (!t) { t = document.createElement('div'); t.id = 'cxToast'; t.className = 'cx-toast'; document.body.appendChild(t); } t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 2800); };
</script>
