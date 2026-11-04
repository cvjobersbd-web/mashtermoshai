<style>
    /* ===== Tutor List — কার্ড আর পেজ ===== */
    .tl { background: #F3F6FA; padding: 30px 0 60px; min-height: 70vh; }
    .tl-wrap { max-width: 1200px; margin: 0 auto; padding: 0 16px; }
    .tl-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(15,27,45,.06); display: flex; flex-direction: column; align-items: center; text-align: center; padding: 22px 14px 0; overflow: hidden; min-width: 0; }
    .tl-pic { width: 66px; height: 66px; position: relative; display: flex; align-items: center; justify-content: center; text-decoration: none;
        border-radius: 58% 42% 55% 45% / 45% 55% 45% 55%; border: 2.5px solid #00CFFF; overflow: hidden; background: linear-gradient(135deg, #08224A, #0A5A86); color: #fff; font-weight: 800; font-size: 20px; }
    .tl-pic img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .tl-badges { display: flex; gap: 5px; justify-content: center; align-items: center; min-height: 20px; margin-top: 8px; font-size: 14px; }
    .tl-badges .pr { color: #F59E0B; } .tl-badges .tp { color: #A855F7; } .tl-badges .vf { color: #00B5E0; }
    .tl-name { margin: 6px 0 0; font-size: 16px; font-weight: 700; color: #1F2937; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tl-inst { margin-top: 8px; font-size: 13px; color: #6B7280; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tl-loc { margin-top: 6px; font-size: 13.5px; color: #374151; }
    .tl-loc i { color: #00CFFF; margin-right: 3px; }
    .tl-see { margin-top: 16px; align-self: stretch; margin-left: -14px; margin-right: -14px; display: block; background: #1F2328; color: #fff !important; font-weight: 700; font-size: 14px; padding: 10px 0; text-decoration: none; transition: background .2s; }
    .tl-see:hover { background: #00B5E0; }
    .tl-h { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    .tl-h h2 { margin: 0; font-size: 26px; font-weight: 600; color: #1F2937; font-family: var(--font-heading); }
    .tl-btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 6px; background: #00CFFF; color: #fff !important; font-weight: 700; font-size: 13px; text-decoration: none; border: none; cursor: pointer; font-family: inherit; }
    .tl-btn:hover { background: #00B5E0; }
    .tl-btn.light { background: #fff; color: #1F2937 !important; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .tl-btn.light:hover { background: #F1F5F9; }
    .tl-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px; }
    @media (max-width: 991px) { .tl-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; } }
    @media (max-width: 700px) { .tl-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; } .tl-h h2 { font-size: 21px; } .tl-name { font-size: 14.5px; } .tl-inst { max-width: 120px; } }
</style>
