<style>
    /* ===== Educational Feed — পোস্ট কার্ড (ফিড, একক পোস্ট আর অ্যাডমিন পেজে একই) ===== */
    .ef-post { background: #fff; border: 1px solid #E3E9F1; border-radius: 16px; box-shadow: 0 2px 12px rgba(15,27,45,.05); margin-bottom: 18px; overflow: hidden; }
    .ef-ph { display: flex; align-items: center; gap: 11px; padding: 14px 16px 10px; }
    .ef-av { width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-weight: 700; font-size: 15px; text-decoration: none; }
    .ef-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ef-av.sm { width: 34px; height: 34px; font-size: 12px; }
    .ef-who { min-width: 0; flex: 1; }
    .ef-who a.n { font-weight: 700; color: #0F172A; text-decoration: none; font-size: 15px; }
    .ef-who a.n:hover { text-decoration: underline; }
    .ef-role { display: inline-block; font-size: 10.5px; font-weight: 700; padding: 1px 7px; border-radius: 8px; margin-left: 5px; vertical-align: 2px; background: #E0F2FE; color: #0369A1; }
    .ef-role.t { background: #DCFCE7; color: #15803D; }
    .ef-meta { font-size: 12.5px; color: #64748B; margin-top: 1px; display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
    .ef-meta a { color: inherit; text-decoration: none; } .ef-meta a:hover { text-decoration: underline; }
    .ef-subj { display: inline-flex; align-items: center; gap: 5px; background: #F1F5F9; color: #334155; border-radius: 999px; padding: 2px 9px; font-weight: 600; font-size: 11.5px; text-decoration: none; }
    a.ef-subj:hover { background: #E0F2FE; color: #0369A1; }
    .ef-menu { position: relative; }
    .ef-menu > button { width: 34px; height: 34px; border-radius: 50%; border: none; background: transparent; color: #64748B; cursor: pointer; font-size: 15px; }
    .ef-menu > button:hover { background: #F1F5F9; }
    .ef-menu .dd { position: absolute; right: 0; top: 38px; background: #fff; border: 1px solid #E3E9F1; border-radius: 12px; box-shadow: 0 10px 30px rgba(15,27,45,.12); min-width: 170px; padding: 6px; z-index: 20; display: none; }
    .ef-menu.open .dd { display: block; }
    .ef-menu .dd button { display: flex; gap: 9px; align-items: center; width: 100%; border: none; background: none; padding: 9px 10px; border-radius: 8px; cursor: pointer; font-size: 14px; color: #334155; text-align: left; font-family: inherit; }
    .ef-menu .dd button:hover { background: #F1F5F9; }
    .ef-menu .dd button.del { color: #DC2626; }

    .ef-status { margin: 0 16px 10px; border-radius: 10px; padding: 8px 12px; font-size: 13px; font-weight: 600; display: flex; gap: 8px; align-items: flex-start; }
    .ef-status.Pending { background: #FEF3C7; color: #92400E; }
    .ef-status.Rejected { background: #FEE2E2; color: #991B1B; }
    .ef-status.Approved { background: #DCFCE7; color: #166534; }
    .ef-status i { margin-top: 3px; }

    .ef-text { padding: 0 16px 12px; font-size: 15px; line-height: 1.65; color: #1E293B; white-space: pre-line; word-wrap: break-word; overflow-wrap: anywhere; }
    .ef-text a { color: #0369A1; }
    .ef-text.clamp { display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden; }
    .ef-more { border: none; background: none; color: #0369A1; font-weight: 700; cursor: pointer; padding: 0 16px 12px; font-size: 14px; margin-top: -6px; font-family: inherit; }

    /* ছবির গ্রিড */
    .ef-imgs { display: grid; gap: 3px; background: #F1F5F9; }
    .ef-imgs button { border: none; padding: 0; margin: 0; cursor: zoom-in; position: relative; overflow: hidden; background: #E2E8F0; display: block; }
    .ef-imgs img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .3s; }
    .ef-imgs button:hover img { transform: scale(1.02); }
    .ef-imgs.n1 { grid-template-columns: 1fr; }
    /* একটা ছবি: যে সাইজই হোক পুরো ছবি দেখাবে — কাটা যাবে না, পাশে কালো বর্ডার না; খুব লম্বা ছবিতে পাশে ঝাপসা একই ছবি */
    .ef-imgs.n1 button { width: 100%; background: #F1F5F9; }
    .ef-imgs.n1 button::before { content: ""; position: absolute; inset: -20px; background: var(--bg) center / cover no-repeat; filter: blur(18px) brightness(.9); }
    .ef-imgs.n1 img { position: relative; width: 100%; height: auto; max-height: min(80vh, 720px); object-fit: contain; }
    .ef-imgs.n1 button:hover img { transform: none; }
    .ef-imgs.n2 { grid-template-columns: 1fr 1fr; } .ef-imgs.n2 button { aspect-ratio: 1 / 1; }
    .ef-imgs.n3 { grid-template-columns: 2fr 1fr; grid-template-rows: 1fr 1fr; aspect-ratio: 3 / 2; }
    .ef-imgs.n3 button:first-child { grid-row: span 2; }
    .ef-imgs.n4 { grid-template-columns: 1fr 1fr; } .ef-imgs.n4 button { aspect-ratio: 4 / 3; }
    .ef-imgs .plus { position: absolute; inset: 0; background: rgba(8,34,74,.55); color: #fff; font-size: 30px; font-weight: 700; display: flex; align-items: center; justify-content: center; }

    /* ভিডিও */
    .ef-video { background: #000; }
    .ef-video video { width: 100%; max-height: 560px; display: block; background: #000; }
    .ef-yt { position: relative; aspect-ratio: 16 / 9; background: #000; }
    .ef-yt button { position: absolute; inset: 0; border: none; padding: 0; cursor: pointer; background: #000 center / cover no-repeat; width: 100%; }
    .ef-yt button::after { content: '\f04b'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
        width: 68px; height: 48px; border-radius: 14px; background: #FF0000; color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 20px rgba(0,0,0,.35); }
    .ef-yt iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

    .ef-counts { display: flex; justify-content: space-between; padding: 9px 16px; font-size: 13px; color: #64748B; }
    .ef-counts button { border: none; background: none; color: inherit; cursor: pointer; padding: 0; font-family: inherit; font-size: 13px; }
    .ef-counts button:hover { text-decoration: underline; }
    .ef-acts { display: grid; grid-template-columns: repeat(3, 1fr); border-top: 1px solid #EEF2F7; padding: 4px 8px; }
    .ef-acts button, .ef-acts a { display: flex; align-items: center; justify-content: center; gap: 8px; border: none; background: none; padding: 9px 4px; border-radius: 8px; cursor: pointer;
        color: #475569; font-weight: 600; font-size: 14px; text-decoration: none; font-family: inherit; }
    .ef-acts button:hover, .ef-acts a:hover { background: #F1F5F9; color: #334155; }
    .ef-acts .liked { color: #1D63E0; }
    .ef-acts .liked i { animation: efPop .3s; }
    @keyframes efPop { 50% { transform: scale(1.35); } }

    /* কমেন্ট */
    .ef-cbox { border-top: 1px solid #EEF2F7; padding: 12px 16px 14px; background: #FAFCFE; }
    .ef-cbox[hidden] { display: none; }
    .ef-clist { display: flex; flex-direction: column; gap: 10px; margin-bottom: 10px; }
    .ef-c { display: flex; gap: 9px; align-items: flex-start; }
    .ef-cb { background: #EEF2F7; border-radius: 14px; padding: 7px 12px; max-width: calc(100% - 44px); }
    .ef-cb a.n { font-weight: 700; color: #0F172A; font-size: 13.5px; text-decoration: none; }
    .ef-cb p { margin: 2px 0 0; font-size: 14px; color: #1E293B; white-space: pre-line; overflow-wrap: anywhere; }
    .ef-cb p a { color: #0369A1; }
    .ef-cm { font-size: 11.5px; color: #94A3B8; margin: 3px 0 0 12px; display: flex; gap: 10px; }
    .ef-cm button { border: none; background: none; color: #94A3B8; padding: 0; cursor: pointer; font-size: 11.5px; font-weight: 600; font-family: inherit; }
    .ef-cm button:hover { color: #DC2626; }
    .ef-cform { display: flex; gap: 9px; align-items: flex-end; }
    .ef-cform textarea { flex: 1; resize: none; border: 1px solid #DCE5EF; border-radius: 18px; padding: 8px 14px; font-size: 14px; font-family: inherit; outline: none; min-height: 38px; max-height: 140px; line-height: 1.45; background: #fff; }
    .ef-cform textarea:focus { border-color: #02B8F5; }
    .ef-cform button { width: 38px; height: 38px; border-radius: 50%; border: none; background: #02B8F5; color: #fff; cursor: pointer; flex-shrink: 0; }
    .ef-cform button:disabled { opacity: .5; }
    .ef-clogin { font-size: 13.5px; color: #64748B; } .ef-clogin a { color: #0369A1; font-weight: 700; }
    .ef-cempty { font-size: 13px; color: #94A3B8; }

    /* লাইটবক্স */
    .ef-lb { position: fixed; inset: 0; background: rgba(2,8,23,.94); z-index: 5000; display: none; align-items: center; justify-content: center; }
    .ef-lb.open { display: flex; }
    .ef-lb img { max-width: 94vw; max-height: 88vh; object-fit: contain; border-radius: 6px; }
    .ef-lb button { position: absolute; border: none; background: rgba(255,255,255,.12); color: #fff; width: 46px; height: 46px; border-radius: 50%; cursor: pointer; font-size: 18px; }
    .ef-lb button:hover { background: rgba(255,255,255,.25); }
    .ef-lb .x { top: 16px; right: 16px; } .ef-lb .pv { left: 16px; top: 50%; transform: translateY(-50%); } .ef-lb .nx { right: 16px; top: 50%; transform: translateY(-50%); }
    .ef-lb .ct { position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%); color: #CBD5E1; font-size: 13px; }

    .ef-toast { position: fixed; left: 50%; bottom: 24px; transform: translate(-50%, 20px); opacity: 0; background: #08224A; color: #fff; padding: 11px 18px; border-radius: 10px;
        font-size: 14px; transition: opacity .25s, transform .25s; pointer-events: none; z-index: 6000; max-width: 92vw; text-align: center; }
    .ef-toast.show { opacity: 1; transform: translate(-50%, 0); }
    .ef-toast.err { background: #B91C1C; }
    @media (max-width: 640px) {
        .ef-post { border-radius: 12px; margin-bottom: 12px; }
        .ef-ph { padding: 12px 12px 8px; } .ef-text { padding: 0 12px 10px; font-size: 14.5px; } .ef-more { padding: 0 12px 10px; }
        .ef-status { margin: 0 12px 10px; } .ef-counts { padding: 8px 12px; } .ef-cbox { padding: 10px 12px 12px; }
        .ef-acts button, .ef-acts a { font-size: 13px; gap: 6px; }
    }
</style>
