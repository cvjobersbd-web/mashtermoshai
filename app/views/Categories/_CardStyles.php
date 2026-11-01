<?php /** ক্যাটাগরি কার্ডের স্টাইল (Categories পেজ, ক্যাটাগরি পেজের Related অংশ) */ ?>
<style>
    .cc-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 30px; }
    .cc-card { display: flex; flex-direction: column; align-items: center; background: #fff; border: 1px solid #DCE5EF; border-radius: 8px;
        padding: 24px 16px 22px; text-decoration: none; color: #1E293B; box-shadow: 0 2px 6px rgba(15,27,45,.06); transition: transform .25s, box-shadow .25s, border-color .25s; min-width: 0; }
    .cc-card:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(15,27,45,.10); border-color: #BFD9EC; color: #08224A; }
    .cc-img { width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; }
    .cc-img img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; }
    .cc-ic { width: 150px; height: 150px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 58px; position: relative; }
    .cc-ic::after { content: ''; position: absolute; left: 50%; bottom: -14px; transform: translateX(-50%); width: 120px; height: 16px; border-radius: 50%; background: rgba(15,27,45,.07); }
    .cc-name { margin-top: 18px; font-size: 21px; font-weight: 500; text-align: center; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    @media (max-width: 1100px) { .cc-grid { gap: 20px; } .cc-img { height: 170px; } .cc-ic { width: 130px; height: 130px; font-size: 50px; } .cc-name { font-size: 19px; } }
    @media (max-width: 900px) { .cc-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 640px) {
        .cc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .cc-card { padding: 16px 10px 14px; }
        .cc-img { height: 120px; }
        .cc-ic { width: 96px; height: 96px; font-size: 38px; }
        .cc-ic::after { width: 80px; height: 10px; bottom: -10px; }
        .cc-name { font-size: 15px; margin-top: 12px; }
    }
</style>
