<style>
    /* ===== হোম: Categories / Educational Feed / Packages সেকশন ===== */
    .hs-sec { padding: 64px 16px; }
    .hs-wrap { max-width: 1180px; margin: 0 auto; }
    .hs-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 30px; }
    .hs-h2 { font-family: var(--font-heading); font-weight: 700; color: #08224A; font-size: clamp(26px, 3.4vw, 38px); line-height: 1.25; margin: 0; }
    .hs-h2 .hl { color: #02B8F5; }
    .hs-sub { color: #64748B; font-size: 16px; line-height: 1.6; margin: 8px 0 0; max-width: 640px; }
    .hs-more { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; border-radius: 999px; background: #02B8F5; color: #fff; font-weight: 700; font-size: 14.5px;
        text-decoration: none; white-space: nowrap; box-shadow: 0 6px 18px rgba(2,184,245,.28); transition: transform .2s, background .2s; flex-shrink: 0; }
    .hs-more:hover { background: #0099D6; color: #fff; transform: translateY(-2px); }
    .hs-more.dark { background: #08224A; box-shadow: 0 6px 18px rgba(8,34,74,.22); } .hs-more.dark:hover { background: #0F172A; }
    .hs-foot { display: none; text-align: center; margin-top: 24px; }

    /* Categories (কার্ডের স্টাইল Categories/_CardStyles থেকে) */
    .hs-cats .cc-img { height: 170px; }
    .hs-cats .cc-ic { width: 128px; height: 128px; }

    /* Educational Feed */
    .hf-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
    .hf-card { background: #fff; border: 1px solid #DCECF5; border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; text-decoration: none; color: inherit;
        box-shadow: 0 4px 18px rgba(8,34,74,.04); transition: transform .25s, box-shadow .25s; }
    .hf-card:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(8,34,74,.1); color: inherit; }
    .hf-media { aspect-ratio: 16 / 10; position: relative; background: #0F172A; overflow: hidden; }
    .hf-media img, .hf-media video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .hf-media .play { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 58px; height: 58px; border-radius: 50%; background: rgba(255,255,255,.92); color: #E11D48;
        display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 8px 24px rgba(0,0,0,.3); padding-left: 4px; }
    .hf-media .yt { background: #FF0000; color: #fff; border-radius: 14px; width: 64px; height: 46px; }
    .hf-media .cnt { position: absolute; right: 10px; bottom: 10px; background: rgba(15,23,42,.75); color: #fff; font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 999px; }
    .hf-media.txt { background: linear-gradient(135deg, #08224A, #0F6E86); display: flex; align-items: center; padding: 22px 24px; }
    .hf-media.txt p { margin: 0; color: #fff; font-size: 17px; line-height: 1.5; font-weight: 600; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
    .hf-media.txt i.q { position: absolute; top: 14px; right: 18px; font-size: 40px; color: rgba(255,255,255,.12); }
    .hf-body { padding: 16px 18px 18px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
    .hf-who { display: flex; gap: 10px; align-items: center; }
    .hf-av { width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-weight: 700; font-size: 13px;
        display: flex; align-items: center; justify-content: center; }
    .hf-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .hf-who b { display: block; font-size: 14.5px; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .hf-who small { color: #64748B; font-size: 12px; }
    .hf-role { font-size: 10.5px; font-weight: 700; padding: 1px 7px; border-radius: 8px; background: #E0F2FE; color: #0369A1; margin-left: 4px; }
    .hf-role.t { background: #DCFCE7; color: #15803D; }
    .hf-subj { align-self: flex-start; display: inline-flex; gap: 6px; align-items: center; background: #F1F5F9; color: #334155; border-radius: 999px; padding: 3px 11px; font-size: 12px; font-weight: 700; }
    .hf-text { margin: 0; color: #334155; font-size: 14.5px; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .hf-meta { margin-top: auto; display: flex; justify-content: space-between; align-items: center; color: #64748B; font-size: 13px; padding-top: 10px; border-top: 1px solid #EEF2F7; }
    .hf-meta span { display: inline-flex; gap: 12px; } .hf-meta i { color: #94A3B8; margin-right: 4px; }
    .hf-meta b { color: #02B8F5; font-size: 13.5px; }
    .hf-empty { display: grid; grid-template-columns: auto 1fr auto; gap: 22px; align-items: center; background: #fff; border: 1px dashed #9FDDF5; border-radius: 20px; padding: 26px 28px; }
    .hf-empty .ic { width: 64px; height: 64px; border-radius: 18px; background: #E3F6FD; color: #028EA5; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .hf-empty h3 { margin: 0 0 6px; color: #08224A; font-size: 19px; font-weight: 700; }
    .hf-empty p { margin: 0; color: #64748B; font-size: 14.5px; line-height: 1.6; }

    /* Packages */
    .hp-tabs { display: inline-flex; gap: 6px; background: #fff; border: 1px solid #DCECF5; border-radius: 999px; padding: 5px; margin: 0 auto 26px; }
    .hp-tabs button { border: none; background: none; padding: 10px 22px; border-radius: 999px; font-weight: 700; font-size: 14.5px; color: #475569; cursor: pointer; font-family: inherit; display: inline-flex; gap: 8px; align-items: center; }
    .hp-tabs button.on { background: #08224A; color: #fff; }
    .hp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
    .hp-grid[hidden] { display: none; }
    .hp-card { background: #fff; border: 1.5px solid #DCECF5; border-radius: 22px; padding: 24px 22px; display: flex; flex-direction: column; position: relative; transition: transform .25s, box-shadow .25s; }
    .hp-card:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(8,34,74,.1); }
    .hp-card.ft { border-color: #02B8F5; box-shadow: 0 14px 34px rgba(2,184,245,.16); }
    .hp-badge { position: absolute; top: -12px; left: 22px; background: linear-gradient(135deg, #F5A623, #F97316); color: #fff; font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 999px; }
    .hp-name { font-size: 18px; font-weight: 800; color: #08224A; }
    .hp-dur { color: #64748B; font-size: 13.5px; margin-top: 2px; }
    .hp-price { display: flex; align-items: baseline; gap: 8px; margin: 14px 0 4px; flex-wrap: wrap; }
    .hp-price b { font-size: 32px; color: #08224A; line-height: 1; }
    .hp-price s { color: #94A3B8; font-size: 15px; }
    .hp-price .off { font-size: 12px; font-weight: 800; background: #DCFCE7; color: #15803D; padding: 2px 8px; border-radius: 999px; }
    .hp-feat { list-style: none; margin: 16px 0 20px; padding: 0; display: flex; flex-direction: column; gap: 9px; }
    .hp-feat li { display: flex; gap: 9px; font-size: 14px; color: #334155; line-height: 1.45; }
    .hp-feat li i { color: #16A34A; margin-top: 3px; font-size: 12px; }
    .hp-feat li.more { color: #028EA5; font-weight: 700; }
    .hp-btn { margin-top: auto; display: flex; align-items: center; justify-content: center; gap: 8px; height: 46px; border-radius: 12px; background: #EEF6FC; color: #08224A; font-weight: 700;
        text-decoration: none; transition: background .2s, color .2s; }
    .hp-btn:hover { background: #08224A; color: #fff; }
    .hp-card.ft .hp-btn { background: #02B8F5; color: #fff; } .hp-card.ft .hp-btn:hover { background: #0099D6; }
    .hp-empty { text-align: center; color: #64748B; background: #fff; border-radius: 18px; padding: 30px; border: 1px dashed #CBD5E1; }

    @media (max-width: 1024px) { .hf-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .hf-grid > :nth-child(3) { display: none; } }
    @media (max-width: 640px) {
        .hs-sec { padding: 46px 14px; }
        .hs-head { margin-bottom: 22px; }
        .hs-head .hs-more { display: none; }
        .hs-foot { display: block; }
        .hs-foot .hs-more { width: 100%; justify-content: center; }
        .hf-grid { grid-template-columns: 1fr; } .hf-grid > :nth-child(3) { display: flex; }
        .hf-empty { grid-template-columns: 1fr; text-align: center; justify-items: center; padding: 24px 18px; }
        .hp-tabs { display: flex; } .hp-tabs button { flex: 1; justify-content: center; padding: 10px 8px; font-size: 13.5px; }
        .hs-cats .cc-img { height: 140px; } .hs-cats .cc-ic { width: 100px; height: 100px; font-size: 40px; }
    }
</style>
