<?php /** প্রোফাইল পেজের কমন স্টাইল (স্টুডেন্ট + টিচার) */ ?>
<style>
    :root {
        --pf-bg: #F4F7FB; --pf-card: #fff; --pf-line: #E8EEF5; --pf-text: #0F1B2D; --pf-muted: #64748B; --pf-soft: #94A3B8;
        --pf-blue: #1D63E0; --pf-blue-soft: #EAF1FE; --pf-shadow: 0 1px 2px rgba(15,27,45,.04), 0 4px 16px rgba(15,27,45,.04);
    }
    .pf-page { background: var(--pf-bg); padding: 20px 0 60px; min-height: 80vh; color: var(--pf-text); }
    .pf-page h1, .pf-page h2, .pf-page h3, .pf-page h4, .pf-page h5 { color: var(--pf-text); font-family: inherit; }
    .pf-wrap { max-width: 1460px; margin: 0 auto; padding: 0 20px; }
    .pf-card { background: var(--pf-card); border: 1px solid var(--pf-line); border-radius: 16px; box-shadow: var(--pf-shadow); }

    /* ================= HEADER ================= */
    .pf-head { position: relative; overflow: visible; }
    .ps-cover {
        height: 178px; border-radius: 16px 16px 0 0; position: relative; overflow: hidden;
        background: linear-gradient(115deg, #0B2447 0%, #19376D 45%, #1D63E0 100%);
    }
    .ps-cover::before { content: ''; position: absolute; inset: 0; z-index: 0;
        background: radial-gradient(circle at 85% 30%, rgba(255,214,102,.35) 0%, transparent 35%), radial-gradient(circle at 15% 120%, rgba(2,184,245,.45) 0%, transparent 40%); }
    .ps-cover::after { content: ''; position: absolute; inset: 0; opacity: .1; z-index: 0; background-image: radial-gradient(#fff 1px, transparent 1.2px); background-size: 20px 20px; }
    .ps-cover > img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; user-select: none; -webkit-user-drag: none; cursor: zoom-in; }
    .ps-cover.has-img::before, .ps-cover.has-img::after { display: none; }
    .ps-cover.repo > img { cursor: grab; touch-action: none; }
    .ps-cover.repo.dragging > img { cursor: grabbing; }
    .cv-edit-wrap { position: absolute; right: 22px; bottom: 16px; z-index: 4; }
    .cv-btn { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 10px; border: none; font-weight: 600; font-size: 14.5px; cursor: pointer; }
    .cv-btn.light { background: #fff; color: var(--pf-text); box-shadow: 0 4px 14px rgba(0,0,0,.18); }
    .cv-btn.light:hover { background: #F4F7FB; }
    .cv-btn.primary { background: var(--pf-blue); color: #fff; }
    .cv-btn.ghost { background: rgba(255,255,255,.2); color: #fff; border: 1px solid rgba(255,255,255,.45); }
    .cv-btn:disabled { opacity: .6; }
    .cv-bar { position: absolute; z-index: 5; left: 0; right: 0; top: 0; display: none; justify-content: flex-end; gap: 8px; padding: 12px 16px; background: linear-gradient(rgba(0,0,0,.55), transparent); }
    .cv-hint { position: absolute; z-index: 5; left: 50%; top: 50%; transform: translate(-50%, -50%); display: none; pointer-events: none;
        background: rgba(0,0,0,.6); color: #fff; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; white-space: nowrap; }
    .ps-cover.repo .cv-bar { display: flex; } .ps-cover.repo .cv-hint { display: block; } .ps-cover.repo .cv-edit-wrap { display: none; }
    .av-menu, .cv-menu { position: absolute; z-index: 30; min-width: 230px; background: #fff; border-radius: 12px; padding: 6px; display: none; text-align: left;
        box-shadow: 0 14px 34px rgba(15,27,45,.2), 0 0 0 1px rgba(15,27,45,.05); }
    .cv-menu { right: 0; bottom: calc(100% + 6px); }
    .av-menu { left: 0; top: calc(100% + 6px); }
    .av-menu.open, .cv-menu.open { display: block; }
    .av-menu button, .cv-menu button { display: flex; gap: 12px; align-items: center; width: 100%; padding: 10px 12px; border: none; background: none; border-radius: 8px;
        font-weight: 600; font-size: 14.5px; color: var(--pf-text); cursor: pointer; text-align: left; }
    .av-menu button[hidden], .cv-menu button[hidden] { display: none; }
    .av-menu button:hover, .cv-menu button:hover { background: #F1F5F9; }
    .av-menu button i, .cv-menu button i { width: 18px; color: var(--pf-muted); }
    .cv-menu button.danger, .cv-menu button.danger i { color: #DC2626; }

    .pf-info { display: flex; gap: 26px; align-items: flex-start; padding: 0 28px 22px 32px; }
    .ps-avatar-wrap { position: relative; margin-top: -96px; flex-shrink: 0; z-index: 3; }
    .ps-avatar { width: 184px; height: 184px; border-radius: 50%; border: 6px solid #fff; position: relative; overflow: hidden; cursor: pointer;
        background: linear-gradient(135deg, #0B2447, #1D63E0); color: #fff; font-size: 58px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 28px rgba(15,27,45,.18); }
    .ps-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ps-avatar.uploading::after { content: ''; position: absolute; inset: 0; z-index: 2; background: rgba(255,255,255,.6); }
    .ps-avatar-edit { position: absolute; right: 10px; bottom: 14px; z-index: 3; width: 42px; height: 42px; border-radius: 50%; background: #fff; border: none;
        color: var(--pf-text); display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; box-shadow: 0 3px 10px rgba(15,27,45,.2); }
    .ps-avatar-edit:hover { background: #F1F5F9; }
    .pf-id { flex: 1; min-width: 0; padding-top: 16px; }
    .pf-name { font-size: 30px; font-weight: 700; margin: 0; line-height: 1.2; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; word-break: break-word; }
    .pf-name .fa-circle-check { color: var(--pf-blue); font-size: 22px; }
    .pf-warn { display: inline-flex; gap: 6px; align-items: center; background: #FDECEC; color: #B91C1C; font-size: 12.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }
    .pf-meta { display: flex; flex-wrap: wrap; gap: 8px 26px; margin-top: 10px; color: var(--pf-muted); font-size: 14.5px; }
    .pf-meta span { display: inline-flex; align-items: center; gap: 8px; }
    .pf-meta i { color: #7C8798; }
    .pf-bio { margin-top: 10px; font-size: 15px; color: #334155; display: flex; align-items: center; gap: 10px; }
    .pf-bio.empty { color: var(--pf-soft); font-style: italic; }
    .pf-pen { border: none; background: none; color: var(--pf-muted); cursor: pointer; padding: 4px; border-radius: 6px; }
    .pf-pen:hover { background: #F1F5F9; color: var(--pf-text); }
    .pf-actions { padding-top: 22px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .pf-actions .fr-actions { margin: 0; width: auto; min-width: 220px; }
    .pf-actions .fr-btn { height: 42px; font-size: 14.5px; }
    .pf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 44px; padding: 0 20px; border-radius: 12px; border: 1px solid var(--pf-line);
        background: #F8FAFC; color: var(--pf-text); font-weight: 600; font-size: 15px; cursor: pointer; text-decoration: none; white-space: nowrap; }
    .pf-btn:hover { background: #EEF2F7; color: var(--pf-text); }
    .pf-btn.primary { background: var(--pf-blue); border-color: var(--pf-blue); color: #fff; }
    .pf-btn.primary:hover { background: #1853BF; color: #fff; }
    .pf-btn.on { background: var(--pf-blue-soft); border-color: #C7D9FB; color: var(--pf-blue); }

    /* ================= BODY ================= */
    .pf-body { display: grid; grid-template-columns: 250px minmax(0, 1fr); gap: 18px; margin-top: 18px; align-items: start; }
    .pf-nav-col { position: sticky; top: 96px; display: flex; flex-direction: column; gap: 18px; }
    .pf-nav { padding: 12px; }
    .pf-nav a { display: flex; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 12px; color: #334155; text-decoration: none; font-size: 15.5px; font-weight: 500; }
    .pf-nav a i { width: 22px; text-align: center; font-size: 17px; color: #64748B; }
    .pf-nav a:hover { background: #F4F7FB; color: var(--pf-text); }
    .pf-nav a.active { background: var(--pf-blue-soft); color: var(--pf-blue); font-weight: 600; }
    .pf-nav a.active i { color: var(--pf-blue); }
    .pf-nav a .pf-n { margin-left: auto; font-size: 12px; font-weight: 700; background: #F1F5F9; color: #475569; padding: 1px 8px; border-radius: 10px; }
    .pf-nav a.active .pf-n { background: #fff; color: var(--pf-blue); }
    .pf-promo-img { display: block; overflow: hidden; padding: 0; transition: transform .2s, box-shadow .2s; }
    .pf-promo-img:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(15,27,45,.1); }
    .pf-promo-img img { display: block; width: 100%; height: auto; }
    .pf-promo { position: relative; overflow: hidden; padding: 20px 18px 18px; min-height: 196px; display: flex; flex-direction: column; justify-content: space-between;
        background: linear-gradient(160deg, #FFF7E8 0%, #EAF6FF 100%); border-color: #F2E7D0; }
    .pf-promo h5 { position: relative; z-index: 1; margin: 0; font-size: 22px; line-height: 1.35; font-weight: 800; color: #0B3B8C; text-align: right; }
    .pf-promo-art { position: absolute; left: 14px; bottom: 54px; font-size: 64px; line-height: 1; filter: drop-shadow(0 6px 10px rgba(29,99,224,.18)); }
    .pf-promo-art span { position: absolute; font-size: 30px; }
    .pf-promo a { position: relative; z-index: 1; align-self: flex-end; display: inline-flex; align-items: center; gap: 8px; height: 36px; padding: 0 14px; border-radius: 10px;
        background: var(--pf-blue); color: #fff; font-size: 13.5px; font-weight: 600; text-decoration: none; }
    .pf-promo a:hover { background: #1853BF; color: #fff; }

    .pf-sec { display: none; }
    .pf-sec.active { display: block; animation: pfIn .25s ease; }
    @keyframes pfIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .pf-sec-h { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .pf-sec-h h2 { margin: 0; font-size: 22px; font-weight: 700; }
    .pf-sec-h p { margin: 2px 0 0; color: var(--pf-muted); font-size: 14px; }

    /* stats */
    .pf-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 18px; }
    .st { display: flex; gap: 14px; padding: 18px; min-height: 120px; text-decoration: none; color: inherit; position: relative; transition: transform .15s, box-shadow .15s; }
    a.st:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15,27,45,.08); color: inherit; }
    .st-ico { width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 21px; }
    .st-body { flex: 1; min-width: 0; }
    .st-t { font-size: 14.5px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .st-t i.info { color: var(--pf-soft); font-size: 13px; cursor: help; }
    .st-v { display: flex; align-items: baseline; gap: 8px; margin-top: 8px; }
    .st-v b { font-size: 28px; line-height: 1; font-weight: 700; }
    .st-v span { color: var(--pf-muted); font-size: 15px; }
    .st-fire { background: #FFF1E6; color: #F97316; }
    .st-goal { background: #E7F8EE; color: #16A34A; }
    .st-tut { background: #F1EAFE; color: #7C3AED; }
    .st-save { background: #FDECEE; color: #E11D48; }
    .st-friend { background: #E8F3FF; color: var(--pf-blue); }
    .st-post { background: #FFF6DB; color: #D97706; }
    .st.fire-card { background: linear-gradient(135deg, #FFF8F1 0%, #fff 60%); }
    .st-dots { display: flex; gap: 7px; margin-top: 12px; }
    .st-dots i { width: 13px; height: 13px; border-radius: 50%; background: #E5E7EB; display: block; }
    .st-dots i.on { background: #F59E0B; box-shadow: 0 0 0 2px #FFEDD5; }
    .st-dots i.today { outline: 2px solid #FDBA74; outline-offset: 1px; }
    .st-goal-t { font-size: 18px; font-weight: 700; margin-top: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .st-prog { display: flex; align-items: center; gap: 10px; margin-top: 12px; }
    .st-prog .bar { flex: 1; height: 8px; background: #E8EEF5; border-radius: 4px; overflow: hidden; }
    .st-prog .bar span { display: block; height: 100%; background: linear-gradient(90deg, #2F7BF5, #1D63E0); border-radius: 4px; transition: width .6s; }
    .st-prog b { color: var(--pf-blue); font-size: 14.5px; }
    .st-empty { font-size: 13.5px; color: var(--pf-muted); margin-top: 8px; }
    .st-empty b { color: var(--pf-blue); }
    .st-faces { display: flex; align-items: center; margin-top: 10px; }
    .st-faces .fc, .st-faces .plus { width: 34px; height: 34px; border-radius: 50%; border: 2px solid #fff; margin-left: -8px; overflow: hidden; position: relative;
        background: linear-gradient(135deg, #0B2447, #1D63E0); color: #fff; font-size: 11.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .st-faces .fc:first-child { margin-left: 0; }
    .st-faces .fc img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .st-faces .plus { background: #F1F5F9; color: #475569; font-size: 14px; border-color: #fff; }
    .st-thumbs { position: absolute; right: 44px; bottom: 18px; display: flex; }
    .st-thumbs span { width: 46px; height: 56px; border-radius: 8px; overflow: hidden; margin-left: -26px; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(15,27,45,.15); background: #E2E8F0; }
    .st-thumbs span:first-child { margin-left: 0; }
    .st-thumbs img { width: 100%; height: 100%; object-fit: cover; }
    .st-arrow { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--pf-line);
        background: #fff; display: flex; align-items: center; justify-content: center; color: #475569; font-size: 12px; }

    .ov-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 18px; align-items: start; }
    .ov-right { display: flex; flex-direction: column; gap: 18px; position: sticky; top: 96px; }

    /* composer */
    .pf-compose { padding: 16px 18px; margin-bottom: 18px; }
    .pf-compose-top { display: flex; gap: 12px; align-items: center; }
    .pf-compose-top .me-avatar { width: 46px; height: 46px; }
    .pf-compose-in { flex: 1; text-align: left; height: 48px; border-radius: 24px; border: 1px solid var(--pf-line); background: #F4F7FB; padding: 0 20px; color: #7C8798;
        font-size: 15.5px; cursor: pointer; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .pf-compose-in:hover { background: #EEF2F7; }
    .pf-chips { display: flex; gap: 12px; margin-top: 14px; padding-left: 58px; overflow-x: auto; scrollbar-width: none; }
    .pf-chips::-webkit-scrollbar { display: none; }
    .pf-chip { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 9px; height: 40px; padding: 0 18px; border-radius: 12px; border: none; cursor: pointer;
        font-size: 14px; font-weight: 500; color: #334155; transition: transform .12s, filter .12s; }
    .pf-chip:hover { filter: brightness(.97); transform: translateY(-1px); }
    .pf-chip i { font-size: 17px; }
    .ch-photo { background: #EAF7EE; } .ch-photo i { color: #22A35A; }
    .ch-study { background: #F2EEFF; } .ch-study i { color: #7C3AED; }
    .ch-q { background: #F2EEFF; } .ch-q i { color: #6D28D9; }
    .ch-ach { background: #FFF4E2; } .ch-ach i { color: #F59E0B; }
    .ch-poll { background: #FDECEE; } .ch-poll i { color: #E11D48; }

    .pf-feed-h { display: flex; align-items: center; justify-content: space-between; margin: 0 2px 12px; }
    .pf-feed-h h3 { margin: 0; font-size: 18px; font-weight: 700; }
    .pf-feed-h span { color: var(--pf-muted); font-size: 14px; }
    #psFeed .pc { border-radius: 16px; border-color: var(--pf-line); box-shadow: var(--pf-shadow); }

    /* right column cards */
    .rc { padding: 18px; }
    .rc-h { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .rc-h h4 { margin: 0; font-size: 17px; font-weight: 700; }
    .rc-h a, .rc-h button { font-size: 14px; color: var(--pf-blue); text-decoration: none; border: none; background: none; cursor: pointer; font-weight: 500; padding: 0; }
    .rc-h a:hover, .rc-h button:hover { text-decoration: underline; }
    .am-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 13px; }
    .am-list li { display: flex; gap: 12px; align-items: center; font-size: 14.5px; color: #334155; }
    .am-list li i { width: 20px; text-align: center; color: #7C8798; font-size: 15px; }
    .am-list li.muted { color: var(--pf-soft); font-style: italic; }
    .am-subj { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0 0 32px; }
    .am-subj span { padding: 5px 12px; border-radius: 16px; background: #F1F5F9; font-size: 12.5px; color: #334155; border: 1px solid #E8EEF5; }
    .am-quote { margin-top: 16px; padding: 14px 44px 14px 46px; border-radius: 12px; background: #F4F7FB; color: #475569; font-size: 14.5px; position: relative; line-height: 1.5; }
    .am-quote::before, .am-quote::after { position: absolute; font-size: 34px; color: #C9D3E1; font-family: Georgia, serif; line-height: 1; }
    .am-quote::before { content: '\201C'; left: 14px; top: 8px; }
    .am-quote::after { content: '\201D'; right: 14px; bottom: -4px; }
    .tu-list { display: flex; flex-direction: column; }
    .tu-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-top: 1px solid #F1F5F9; }
    .tu-item:first-child { border-top: none; padding-top: 0; }
    .tu-av { width: 50px; height: 50px; border-radius: 50%; overflow: hidden; position: relative; flex-shrink: 0; background: linear-gradient(135deg, #0B2447, #1D63E0);
        color: #fff; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .tu-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .tu-who { flex: 1; min-width: 0; }
    .tu-who a { display: block; font-weight: 600; font-size: 15px; color: var(--pf-text); text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tu-who a:hover { text-decoration: underline; }
    .tu-who small { color: var(--pf-muted); font-size: 13.5px; }
    .tu-view { height: 38px; padding: 0 18px; border-radius: 10px; background: var(--pf-blue-soft); color: var(--pf-blue); font-weight: 600; font-size: 14px;
        display: inline-flex; align-items: center; text-decoration: none; flex-shrink: 0; }
    .tu-view:hover { background: #DCE8FD; color: var(--pf-blue); }
    .rc-empty { color: var(--pf-muted); font-size: 14px; margin: 0; line-height: 1.6; }
    .rc-empty a { color: var(--pf-blue); font-weight: 600; }

    /* empty */
    .pf-empty { padding: 44px 20px; text-align: center; color: var(--pf-muted); }
    .pf-empty .ei { width: 70px; height: 70px; border-radius: 50%; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; font-size: 28px; background: #F1F5F9; color: #94A3B8; }
    .pf-empty h5 { color: var(--pf-text); font-weight: 700; margin: 0 0 6px; font-size: 17px; }
    .pf-empty p { margin: 0 auto; max-width: 420px; font-size: 14px; }
    .pf-empty .pf-btn { margin-top: 16px; }

    /* about (profile section) */
    .ab-sections { display: flex; flex-direction: column; gap: 16px; }
    .ab-wrap { display: block; }
    .ab-nav { display: none; }
    .ab-card { background: #fff; border-radius: 16px; border: 1px solid var(--pf-line); padding: 20px 22px; box-shadow: var(--pf-shadow); scroll-margin-top: 100px; }
    .ab-card-h { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .ab-card-h h3 { font-size: 17px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px; }
    .ab-card-h h3 i { color: var(--pf-blue); }
    .ab-st { font-size: 10.5px; font-weight: 800; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; }
    .ab-st.ok { background: #E7F8EE; color: #15803D; } .ab-st.no { background: #FFF4E5; color: #B45309; }
    .ab-edit { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 600; color: var(--pf-blue); text-decoration: none;
        padding: 7px 14px; border-radius: 10px; background: var(--pf-blue-soft); }
    .ab-edit:hover { background: #DCE8FD; color: var(--pf-blue); }
    .ab-rows { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 24px; }
    .ab-row { display: flex; gap: 12px; align-items: flex-start; min-width: 0; }
    .ab-row > i { width: 38px; height: 38px; border-radius: 10px; background: #F4F7FB; color: var(--pf-blue); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ab-row small { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase; color: var(--pf-soft); }
    .ab-row span, .ab-row a { display: block; font-size: 15px; font-weight: 600; color: var(--pf-text); word-break: break-word; }
    .ab-row a { color: var(--pf-blue); text-decoration: none; }
    .ab-row .lock { color: var(--pf-soft); font-weight: 500; font-size: 13.5px; }
    .ab-row .empty { color: #B6C0CD; font-weight: 500; font-style: italic; font-size: 14px; }
    .ab-none { color: var(--pf-soft); font-size: 14px; margin: 0; }
    .ab-tutor { display: flex; gap: 14px; align-items: center; padding: 14px; border-radius: 12px; background: #F4F7FB; margin-bottom: 14px; }
    .ab-tutor i { font-size: 28px; }
    .ab-tutor strong { display: block; font-size: 15.5px; }
    .ab-tutor p { margin: 2px 0 0; color: var(--pf-muted); font-size: 13.5px; }
    .ps-btn { display: inline-flex; align-items: center; gap: 7px; height: 40px; padding: 0 16px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; text-decoration: none; }
    .ps-btn.primary { background: var(--pf-blue); color: #fff; }

    /* goals */
    .gl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
    .gl-card { background: #fff; border: 1px solid var(--pf-line); border-radius: 16px; padding: 18px; box-shadow: var(--pf-shadow); }
    .gl-card.primary { border-color: #BFD3F7; box-shadow: 0 0 0 3px #EAF1FE, var(--pf-shadow); }
    .gl-top { display: flex; gap: 14px; align-items: flex-start; }
    .gl-ico { width: 46px; height: 46px; border-radius: 14px; background: #E7F8EE; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
    .gl-card.done .gl-ico { background: #DCFCE7; }
    .gl-head { flex: 1; min-width: 0; }
    .gl-head h4 { margin: 6px 0 0; font-size: 18px; font-weight: 700; word-break: break-word; }
    .gl-tags { display: flex; gap: 6px; flex-wrap: wrap; }
    .gl-cat, .gl-main, .gl-ok { font-size: 11px; font-weight: 800; padding: 2px 9px; border-radius: 10px; text-transform: uppercase; letter-spacing: .3px; }
    .gl-cat { background: #F1F5F9; color: #475569; } .gl-main { background: #FEF3C7; color: #B45309; } .gl-ok { background: #DCFCE7; color: #15803D; }
    .gl-date { font-size: 13px; color: var(--pf-muted); margin-top: 4px; }
    .gl-date b { color: #EA580C; }
    .gl-menu-wrap { position: relative; }
    .gl-menu-btn { width: 34px; height: 34px; border-radius: 50%; border: none; background: none; color: var(--pf-muted); cursor: pointer; }
    .gl-menu-btn:hover { background: #F1F5F9; }
    .gl-menu { position: absolute; right: 0; top: 38px; z-index: 20; min-width: 190px; background: #fff; border-radius: 12px; padding: 6px; display: none;
        box-shadow: 0 14px 34px rgba(15,27,45,.18), 0 0 0 1px rgba(15,27,45,.05); }
    .gl-menu.open { display: block; }
    .gl-menu button { display: flex; gap: 10px; align-items: center; width: 100%; padding: 9px 12px; border: none; background: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; text-align: left; color: var(--pf-text); }
    .gl-menu button:hover { background: #F1F5F9; }
    .gl-menu button.danger { color: #DC2626; }
    .gl-prog { display: flex; align-items: center; gap: 12px; margin-top: 16px; }
    .gl-bar { flex: 1; height: 10px; background: #E8EEF5; border-radius: 5px; overflow: hidden; }
    .gl-bar span { display: block; height: 100%; background: linear-gradient(90deg, #2F7BF5, #1D63E0); border-radius: 5px; transition: width .6s; }
    .gl-card.done .gl-bar span { background: linear-gradient(90deg, #22C55E, #16A34A); }
    .gl-prog b { font-size: 15px; color: var(--pf-blue); min-width: 42px; text-align: right; }
    .gl-sub { font-size: 12.5px; color: var(--pf-soft); margin-top: 6px; }
    .gl-tasks { list-style: none; margin: 12px 0 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
    .gl-tasks li { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 8px; font-size: 14px; }
    .gl-tasks li:hover { background: #F8FAFC; }
    .gl-tasks label { flex: 1; display: flex; gap: 10px; align-items: center; margin: 0; cursor: pointer; min-width: 0; }
    .gl-tasks input { width: 18px; height: 18px; accent-color: #16A34A; flex-shrink: 0; }
    .gl-tasks li.done span { text-decoration: line-through; color: var(--pf-soft); }
    .gl-tasks li > i { color: #16A34A; }
    .gl-x { border: none; background: none; color: #CBD5E1; cursor: pointer; width: 26px; height: 26px; border-radius: 50%; }
    .gl-x:hover { background: #FEE2E2; color: #DC2626; }
    .gl-add { display: flex; gap: 8px; margin-top: 10px; }
    .gl-add input { flex: 1; min-width: 0; height: 38px; border: 1.5px dashed #CBD5E1; border-radius: 10px; padding: 0 12px; font-size: 13.5px; outline: none; background: #fff; }
    .gl-add input:focus { border-style: solid; border-color: var(--pf-blue); }
    .gl-add button { width: 38px; height: 38px; border-radius: 10px; border: none; background: var(--pf-blue-soft); color: var(--pf-blue); cursor: pointer; }

    /* activities */
    .act-top { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 16px; }
    .act-big { padding: 20px; display: flex; gap: 16px; align-items: center; }
    .act-big .st-ico { width: 56px; height: 56px; font-size: 26px; border-radius: 16px; }
    .act-big b { font-size: 30px; display: block; line-height: 1.1; }
    .act-big small { color: var(--pf-muted); font-size: 13.5px; }
    .hm { padding: 20px; margin-bottom: 16px; }
    .hm h4 { margin: 0 0 14px; font-size: 16px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
    .hm h4 small { font-weight: 500; color: var(--pf-muted); font-size: 13px; }
    .hm-scroll { overflow-x: auto; padding-bottom: 4px; }
    .hm-grid { display: grid; grid-auto-flow: column; grid-template-rows: repeat(7, 15px); gap: 4px; width: max-content; }
    .hm-grid i { width: 15px; height: 15px; border-radius: 4px; background: #EEF2F7; display: block; }
    .hm-grid i.l1 { background: #CDE2FF; } .hm-grid i.l2 { background: #8DBBFB; } .hm-grid i.l3 { background: #4A8DF2; } .hm-grid i.l4 { background: #1D63E0; }
    .hm-grid i.fut { visibility: hidden; }
    .hm-legend { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--pf-muted); margin-top: 10px; justify-content: flex-end; }
    .hm-legend i { width: 12px; height: 12px; border-radius: 3px; display: inline-block; }
    .act-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 14px; }
    .act-tile { padding: 16px; display: flex; gap: 12px; align-items: center; }
    .act-tile .st-ico { width: 42px; height: 42px; font-size: 17px; border-radius: 12px; }
    .act-tile b { display: block; font-size: 21px; line-height: 1.1; }
    .act-tile small { color: var(--pf-muted); font-size: 12.5px; }

    /* saved */
    .sv-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .sv-filter { height: 36px; padding: 0 16px; border-radius: 18px; border: 1px solid var(--pf-line); background: #fff; font-size: 13.5px; font-weight: 600; color: #475569; cursor: pointer; }
    .sv-filter.active { background: var(--pf-text); border-color: var(--pf-text); color: #fff; }
    .sv-list { display: flex; flex-direction: column; gap: 12px; }
    .sv-item { display: flex; gap: 14px; align-items: center; padding: 14px; transition: opacity .3s, transform .3s; }
    .sv-item.gone { opacity: 0; transform: scale(.98); }
    .sv-thumb { width: 72px; height: 72px; border-radius: 12px; overflow: hidden; flex-shrink: 0; background: #F1F5F9; color: #94A3B8; display: flex; align-items: center; justify-content: center; font-size: 24px; text-decoration: none; }
    .sv-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .sv-body { flex: 1; min-width: 0; }
    .sv-type { font-size: 11px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; color: var(--pf-blue); }
    .sv-body a.sv-title { display: block; font-weight: 600; font-size: 15px; color: var(--pf-text); text-decoration: none; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sv-body a.sv-title:hover { text-decoration: underline; }
    .sv-body small { color: var(--pf-muted); font-size: 13px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sv-un { height: 38px; padding: 0 14px; border-radius: 10px; border: 1px solid var(--pf-line); background: #fff; color: #475569; font-weight: 600; font-size: 13.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; flex-shrink: 0; }
    .sv-un:hover { background: #FEF2F2; color: #DC2626; border-color: #FECACA; }
    .sv-item.unavail .sv-title { color: var(--pf-soft); pointer-events: none; }

    /* circulars */
    .ci-item { padding: 16px 18px; }
    .ci-item + .ci-item { margin-top: 12px; }
    .ci-text { font-size: 15px; white-space: pre-wrap; word-break: break-word; margin: 0 0 10px; }
    .ci-tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .ci-tags span { font-size: 12.5px; background: #F1F5F9; padding: 4px 10px; border-radius: 12px; color: #334155; }
    .ci-foot { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin-top: 12px; padding-top: 12px; border-top: 1px solid #F1F5F9; font-size: 13.5px; color: var(--pf-muted); }
    .ci-foot a { margin-left: auto; }
    .ci-pend { background: #FEF3C7; color: #B45309; font-weight: 700; padding: 2px 9px; border-radius: 10px; font-size: 12px; }

    /* settings */
    .set-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    .set-card { padding: 20px 22px; }
    .set-card h4 { margin: 0 0 4px; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .set-card h4 i { color: var(--pf-blue); }
    .set-card > p { color: var(--pf-muted); font-size: 13.5px; margin: 0 0 14px; }
    .set-links { display: flex; flex-direction: column; gap: 4px; }
    .set-links a, .set-links button { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: 10px; color: var(--pf-text); text-decoration: none; font-weight: 500; font-size: 14.5px;
        border: none; background: none; cursor: pointer; text-align: left; width: 100%; }
    .set-links a:hover, .set-links button:hover { background: #F4F7FB; color: var(--pf-text); }
    .set-links i { width: 20px; color: var(--pf-muted); text-align: center; }
    .set-links .chev { margin-left: auto; font-size: 12px; color: var(--pf-soft); }
    .pw-form { display: grid; gap: 12px; }
    .pw-field { position: relative; }
    .pw-field label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 5px; color: #334155; }
    .pw-field input { width: 100%; height: 44px; border: 1.5px solid var(--pf-line); border-radius: 10px; padding: 0 44px 0 14px; font-size: 15px; outline: none; }
    .pw-field input:focus { border-color: var(--pf-blue); box-shadow: 0 0 0 3px var(--pf-blue-soft); }
    .pw-eye { position: absolute; right: 6px; bottom: 4px; width: 36px; height: 36px; border: none; background: none; color: var(--pf-soft); cursor: pointer; }
    .pw-msg { font-size: 13.5px; font-weight: 600; min-height: 18px; }
    .pw-msg.ok { color: #15803D; } .pw-msg.err { color: #B91C1C; }

    /* friends section card */
    .fr-wrap-h { padding: 16px 18px; margin-bottom: 16px; display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; }

    /* modals (intro + goal) */
    .pf-ov { position: fixed; inset: 0; z-index: 2500; background: rgba(15,27,45,.45); display: none; align-items: center; justify-content: center; padding: 16px; }
    .pf-ov.open { display: flex; }
    .pf-modal { width: 100%; max-width: 520px; max-height: calc(100vh - 32px); overflow-y: auto; background: #fff; border-radius: 18px; box-shadow: 0 24px 60px rgba(15,27,45,.3); animation: pfIn .2s ease; }
    .pf-modal-h { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid var(--pf-line); }
    .pf-modal-h h3 { margin: 0; font-size: 19px; font-weight: 700; }
    .pf-x { width: 36px; height: 36px; border-radius: 50%; border: none; background: #F1F5F9; cursor: pointer; font-size: 16px; color: #475569; }
    .pf-modal-b { padding: 18px 20px; display: flex; flex-direction: column; gap: 14px; }
    .pf-f label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; }
    .pf-f label small { font-weight: 500; color: var(--pf-soft); }
    .pf-f input[type=text], .pf-f input[type=date], .pf-f select, .pf-f textarea { width: 100%; border: 1.5px solid var(--pf-line); border-radius: 10px; padding: 10px 12px; font-size: 15px; outline: none; background: #fff; font-family: inherit; }
    .pf-f textarea { resize: vertical; min-height: 70px; }
    .pf-f input:focus, .pf-f select:focus, .pf-f textarea:focus { border-color: var(--pf-blue); box-shadow: 0 0 0 3px var(--pf-blue-soft); }
    .pf-f .cnt { font-size: 12px; color: var(--pf-soft); text-align: right; margin-top: 3px; }
    .pf-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .pf-quick { display: flex; flex-wrap: wrap; gap: 6px; }
    .pf-quick button { height: 30px; padding: 0 12px; border-radius: 15px; border: 1px solid var(--pf-line); background: #F8FAFC; font-size: 12.5px; font-weight: 600; cursor: pointer; color: #334155; }
    .pf-quick button:hover { border-color: var(--pf-blue); color: var(--pf-blue); }
    .pf-range { display: flex; align-items: center; gap: 12px; }
    .pf-range input { flex: 1; accent-color: var(--pf-blue); }
    .pf-range b { min-width: 46px; text-align: right; color: var(--pf-blue); }
    .pf-check { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; }
    .pf-check input { width: 18px; height: 18px; accent-color: var(--pf-blue); }
    .pf-modal-f { padding: 0 20px 20px; display: flex; gap: 10px; justify-content: flex-end; }
    .sj-box { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px; border: 1.5px solid var(--pf-line); border-radius: 10px; min-height: 46px; align-items: center; cursor: text; }
    .sj-box:focus-within { border-color: var(--pf-blue); box-shadow: 0 0 0 3px var(--pf-blue-soft); }
    .sj-box span { display: inline-flex; align-items: center; gap: 6px; background: var(--pf-blue-soft); color: var(--pf-blue); font-size: 13px; font-weight: 600; padding: 4px 6px 4px 10px; border-radius: 14px; }
    .sj-box span button { border: none; background: none; color: inherit; cursor: pointer; padding: 0 2px; font-size: 12px; }
    .sj-box input { flex: 1; min-width: 120px; border: none !important; outline: none; box-shadow: none !important; padding: 4px !important; font-size: 14.5px; }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 1280px) {
        .ov-grid { grid-template-columns: minmax(0, 1fr); }
        .ov-right { position: static; display: grid; grid-template-columns: 1fr 1fr; order: -1; }
        .pf-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 992px) {
        .pf-wrap { padding: 0 12px; }
        .pf-body { grid-template-columns: minmax(0, 1fr); }
        .pf-nav-col { position: sticky; top: 72px; z-index: 30; }
        .pf-nav { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; padding: 8px; border-radius: 14px; }
        .pf-nav::-webkit-scrollbar { display: none; }
        .pf-nav a { flex: 0 0 auto; padding: 9px 14px; font-size: 14px; gap: 8px; }
        .pf-nav a i { width: auto; font-size: 15px; }
        .pf-promo, .pf-promo-img { display: none; }
        .pf-info { flex-direction: column; align-items: center; text-align: center; gap: 8px; padding: 0 16px 18px; }
        .ps-avatar-wrap { margin-top: -84px; }
        .ps-avatar { width: 156px; height: 156px; font-size: 48px; }
        .pf-id { padding-top: 0; width: 100%; }
        .pf-name { justify-content: center; font-size: 25px; }
        .pf-meta { justify-content: center; gap: 6px 16px; font-size: 13.5px; }
        .pf-bio { justify-content: center; }
        .pf-actions { padding-top: 6px; justify-content: center; width: 100%; }
        .pf-actions .fr-actions { flex-direction: row; min-width: 0; flex: 1; }
        .pf-actions .fr-actions > * { flex: 1 1 0; width: auto; }
        .pf-actions .fr-icon-only { flex: 0 0 42px !important; width: 42px; }
        .pf-actions .fr-icon-only span { display: none; }
        .set-grid { grid-template-columns: minmax(0, 1fr); }
        .act-top { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .pf-page { padding-top: 0; }
        .pf-wrap { padding: 0; }
        .pf-head { border-radius: 0; border-left: 0; border-right: 0; }
        .ps-cover { height: 150px; border-radius: 0; }
        .cv-edit-wrap { right: 10px; bottom: 10px; }
        .cv-edit-wrap .cv-btn.light span { display: none; }
        .cv-edit-wrap .cv-btn.light { width: 40px; padding: 0; justify-content: center; }
        .cv-hint { font-size: 12px; white-space: normal; text-align: center; width: 80%; }
        .av-menu { left: 50%; transform: translateX(-50%); }
        .pf-body { padding: 0 12px; margin-top: 12px; gap: 12px; }
        .pf-nav-col { top: 64px; margin: 0 -12px; }
        .pf-nav { border-radius: 0; border-left: 0; border-right: 0; }
        .pf-stats { gap: 10px; margin-bottom: 12px; }
        .st { padding: 14px; gap: 10px; min-height: 0; flex-direction: column; }
        .st-ico { width: 40px; height: 40px; font-size: 17px; border-radius: 12px; }
        .st-v b { font-size: 24px; }
        .st-goal-t { font-size: 16px; }
        .st-thumbs { display: none; }
        .ov-right { grid-template-columns: minmax(0, 1fr); gap: 12px; }
        .pf-chips { padding-left: 0; gap: 8px; }
        .pf-chip { height: 36px; padding: 0 12px; font-size: 13px; }
        .ab-rows { grid-template-columns: 1fr; }
        .gl-grid { grid-template-columns: minmax(0, 1fr); }
        .pf-2 { grid-template-columns: 1fr; }
        .pf-ov { padding: 0; align-items: flex-end; }
        .pf-modal { max-width: none; border-radius: 18px 18px 0 0; max-height: 92vh; }
        .sv-thumb { width: 56px; height: 56px; }
        .sv-un span { display: none; }
        #psFeed .pc { border-radius: 0; }
    }
</style>
