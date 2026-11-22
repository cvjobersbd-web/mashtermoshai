<style>
    .gm-page { max-width: 1180px; margin: 0 auto; padding: 28px 16px 70px; color: #0F172A; }
    .gm-hero { position: relative; overflow: hidden; border-radius: 24px; padding: 28px 30px; color: #fff;
        background: radial-gradient(circle at 85% 20%, rgba(255,255,255,.16), transparent 40%), linear-gradient(135deg, #08224A 0%, #0B3A6E 55%, #0E7490 100%); }
    .gm-hero h1 { margin: 0; font-size: 32px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 12px; }
    .gm-hero p { margin: 8px 0 0; color: #D6F2FC; font-size: 15px; max-width: 620px; line-height: 1.6; }
    .gm-hero .gm-deco { position: absolute; right: 24px; top: 24px; display: flex; gap: 10px; opacity: .9; }
    .gm-hero .gm-deco span { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); }
    .gm-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: 20px; }
    .gm-stat { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); border-radius: 16px; padding: 12px 14px; }
    .gm-stat b { display: block; font-size: 22px; font-weight: 800; color: #fff; line-height: 1.2; }
    .gm-stat small { color: #BAE6FD; font-size: 12.5px; }
    .gm-guest { margin-top: 18px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: rgba(255,255,255,.1); border-radius: 14px; padding: 12px 14px; font-size: 14px; color: #E0F2FE; }
    .gm-guest a { background: #FACC15; color: #422006; font-weight: 800; padding: 8px 16px; border-radius: 10px; text-decoration: none; }

    .gm-sec-h { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin: 30px 0 14px; }
    .gm-sec-h h2 { margin: 0; font-size: 21px; font-weight: 800; color: #08224A; display: flex; align-items: center; gap: 10px; }
    .gm-sec-h a { color: #0369A1; font-weight: 700; font-size: 14px; text-decoration: none; }

    .gm-cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .gm-card { background: #fff; border-radius: 20px; padding: 20px; box-shadow: 0 8px 28px rgba(8, 34, 74, .07); border: 1px solid #EEF2F7; display: flex; flex-direction: column; }
    .gm-card .gm-art { height: 132px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
    .gm-card h3 { margin: 0; font-size: 20px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px; }
    .gm-card p { margin: 6px 0 14px; color: #64748B; font-size: 14px; line-height: 1.55; flex: 1; }
    .gm-lv { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .gm-lv a { text-align: center; padding: 10px 6px; border-radius: 12px; font-weight: 800; font-size: 14px; text-decoration: none; border: 1.5px solid; transition: .15s; }
    .gm-lv a small { display: block; font-weight: 600; font-size: 11px; opacity: .8; }
    .gm-lv a:hover { transform: translateY(-2px); }
    .gm-best { margin-top: 10px; font-size: 12.5px; color: #64748B; display: flex; gap: 10px; flex-wrap: wrap; }
    .gm-best span { background: #F1F5F9; border-radius: 8px; padding: 3px 8px; }

    .gm-row2 { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 18px; }
    .gm-box { background: #fff; border-radius: 20px; padding: 18px 20px; box-shadow: 0 8px 28px rgba(8, 34, 74, .06); border: 1px solid #EEF2F7; }
    .gm-box h3 { margin: 0 0 12px; font-size: 17px; font-weight: 800; color: #08224A; display: flex; align-items: center; gap: 8px; }
    .gm-lb-row { display: flex; align-items: center; gap: 12px; padding: 9px 4px; border-bottom: 1px solid #F1F5F9; }
    .gm-lb-row:last-child { border-bottom: none; }
    .gm-rank { width: 32px; text-align: center; font-weight: 800; color: #64748B; font-size: 15px; flex-shrink: 0; }
    .gm-rank.r1, .gm-rank.r2, .gm-rank.r3 { font-size: 20px; }
    .gm-av { width: 38px; height: 38px; border-radius: 50%; background: #E0F2FE; color: #0369A1; font-weight: 800; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative; font-size: 14px; }
    .gm-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .gm-nm { flex: 1; min-width: 0; }
    .gm-nm b { display: block; font-size: 14.5px; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .gm-nm small { color: #64748B; font-size: 12px; }
    .gm-role { font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 6px; margin-left: 4px; vertical-align: 1px; }
    .gm-role.t { background: #DCFCE7; color: #166534; } .gm-role.s { background: #EDE9FE; color: #5B21B6; }
    .gm-pts { font-weight: 800; color: #0F172A; font-size: 15px; white-space: nowrap; }
    .gm-pts small { color: #64748B; font-weight: 600; font-size: 11.5px; }
    .gm-me-row { background: #FEF9C3; border-radius: 12px; }
    .gm-empty { text-align: center; color: #64748B; padding: 18px 10px; font-size: 14px; }

    .gm-vs { display: flex; flex-direction: column; gap: 12px; }
    .gm-vs-bar { display: flex; height: 40px; border-radius: 12px; overflow: hidden; font-weight: 800; font-size: 13px; color: #fff; }
    .gm-vs-bar div { display: flex; align-items: center; padding: 0 12px; white-space: nowrap; min-width: 0; }
    .gm-vs-bar .s { background: linear-gradient(90deg, #7C3AED, #8B5CF6); }
    .gm-vs-bar .t { background: linear-gradient(90deg, #16A34A, #22C55E); justify-content: flex-end; }
    .gm-vs-legend { display: flex; justify-content: space-between; font-size: 13px; color: #475569; }
    .gm-vs-msg { background: #F8FAFC; border-radius: 12px; padding: 10px 12px; font-size: 14px; color: #334155; text-align: center; font-weight: 600; }
    .gm-reset { font-size: 12.5px; color: #64748B; display: flex; align-items: center; gap: 6px; }

    .gm-brain { display: flex; align-items: center; gap: 14px; }
    .gm-ring { --p: 0; width: 76px; height: 76px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        background: conic-gradient(#F59E0B calc(var(--p) * 1%), #E2E8F0 0); }
    .gm-ring span { width: 60px; height: 60px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; color: #0F172A; }

    @media (max-width: 960px) {
        .gm-cards { grid-template-columns: 1fr; }
        .gm-row2 { grid-template-columns: 1fr; }
        .gm-hero .gm-deco { display: none; }
        .gm-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 520px) {
        .gm-page { padding: 16px 12px 50px; }
        .gm-hero { padding: 22px 18px; border-radius: 20px; }
        .gm-hero h1 { font-size: 25px; }
    }
</style>
