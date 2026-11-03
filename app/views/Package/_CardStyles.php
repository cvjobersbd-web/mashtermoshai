<style>
    /* প্যাকেজ কার্ড (Package/_Card) — Package পেজের মতো একই স্টাইল */
    .pk-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; align-items: stretch; }
    .pk-grid.few { grid-template-columns: repeat(auto-fit, minmax(260px, 320px)); justify-content: center; }
    .pk-card { position: relative; display: flex; flex-direction: column; align-items: flex-start; background: #fff; border: 1.5px solid #E3ECF5; border-radius: 18px; padding: 22px 20px 20px;
        box-shadow: 0 6px 22px rgba(8,34,74,.06); transition: transform .2s, box-shadow .2s; }
    .pk-card:hover { transform: translateY(-3px); box-shadow: 0 16px 36px rgba(8,34,74,.10); }
    .pk-card.featured { border: 2px solid #1D7FE5; box-shadow: 0 16px 38px rgba(29,127,229,.20); }
    .pk-top { width: 100%; display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; min-height: 30px; }
    .pk-name { font-size: 18px; font-weight: 800; color: #08224A; padding-top: 3px; }
    .pk-badge { font-size: 12.5px; font-weight: 800; padding: 5px 13px; border-radius: 20px; background: #FFD84D; color: #4A3200; white-space: nowrap; }
    .pk-card.featured .pk-badge { background: #1D7FE5; color: #fff; }
    .pk-dur { margin-top: 2px; font-size: 13px; color: #64748B; }
    .pk-price { margin-top: 14px; display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
    .pk-price b { font-size: 40px; font-weight: 800; color: #08224A; line-height: 1; font-family: var(--font-heading); letter-spacing: -.5px; }
    .pk-price s { font-size: 17px; color: #94A3B8; font-weight: 600; }
    .pk-pill { display: inline-flex; align-items: center; margin-top: 12px; font-size: 13px; font-weight: 700; padding: 5px 12px; border-radius: 8px; background: #EEF2F6; color: #334155; }
    .pk-pill.green { background: #DDF6E6; color: #12733B; }
    .pk-feat { list-style: none; margin: 18px 0 0; padding: 0; display: flex; flex-direction: column; gap: 11px; flex: 1; width: 100%; }
    .pk-feat li { display: flex; gap: 10px; font-size: 14px; color: #1E293B; line-height: 1.5; }
    .pk-feat i { flex-shrink: 0; width: 19px; height: 19px; border-radius: 50%; background: #22C55E; color: #fff; font-size: 10px; display: inline-flex; align-items: center; justify-content: center; margin-top: 2px; }
    .pk-btn { margin-top: 22px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; border: 1.5px solid #1D7FE5; background: #fff; color: #1D7FE5 !important; font-weight: 700; font-size: 15px;
        padding: 12px; border-radius: 12px; cursor: pointer; text-decoration: none; transition: background .2s, color .2s, box-shadow .2s; }
    .pk-btn i { font-size: 13px; }
    .pk-btn:hover, .pk-card.featured .pk-btn { background: linear-gradient(135deg, #1D7FE5, #2B8CF0); color: #fff !important; border-color: transparent; box-shadow: 0 8px 20px rgba(29,127,229,.3); }

    .pk-free { display: flex; flex-direction: column; background: linear-gradient(160deg, #F0FAFF 0%, #fff 60%); }
    .pk-free .pk-price b { color: #157347; }
    .pk-free .pk-btn { border-color: #157347; color: #157347 !important; }
    .pk-free .pk-btn:hover { background: #157347; color: #fff !important; box-shadow: none; }

    .pk-grid[hidden] { display: none; }
</style>
