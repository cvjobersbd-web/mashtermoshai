<?php
/**
 * Admin → Packages: শিক্ষকের Subscription আর শিক্ষার্থীর Tuition Post প্যাকেজ যোগ/এডিট/মুছে ফেলা/সাজানো।
 * (পেমেন্ট সিস্টেম পরে যুক্ত হবে — এখন শুধু প্যাকেজ)
 */
$ViewData['Title'] = 'Packages';
$packages = is_array($Model) ? $Model : [];
$benefits = $ViewBag->Benefits ?? package_benefits();
$pkb = package_banner();
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .pa-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .pa-head h2 { color: #08224A; margin: 0; font-size: 24px; }
    .pa-head p { color: #64748B; margin: 4px 0 0; font-size: 14px; }
    .pa-view { display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: 10px; border: 1.5px solid #DCECF5; background: #fff; color: #08224A; font-weight: 600; font-size: 13.5px; text-decoration: none; }
    .pa-view:hover { border-color: #02B8F5; color: #08224A; }

    .pa-sec { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.06); margin-bottom: 22px; }
    .pa-sec-h { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
    .pa-sec-h h5 { margin: 0; color: #08224A; font-weight: 700; display: flex; gap: 8px; align-items: center; }
    .pa-sec-h small { color: #64748B; font-size: 13px; flex-basis: 100%; }
    .pa-add { margin-left: auto; border: none; background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; font-weight: 700; font-size: 13.5px; padding: 9px 16px; border-radius: 10px; cursor: pointer; display: inline-flex; gap: 7px; align-items: center; }

    .pa-list { display: flex; flex-direction: column; gap: 10px; }
    .pa-row { display: grid; grid-template-columns: 34px minmax(0, 1.6fr) minmax(0, 1fr) auto auto; gap: 14px; align-items: center; padding: 14px; border: 1.5px solid #DCECF5; border-radius: 14px; background: #fff; }
    .pa-row.off { background: #F8FAFC; opacity: .75; }
    .pa-row.feat { border-color: #9EC2FA; background: #F5F9FF; }
    .pa-move { display: flex; flex-direction: column; gap: 2px; }
    .pa-move button { border: 1px solid #DCECF5; background: #fff; border-radius: 6px; width: 30px; height: 22px; font-size: 11px; color: #475569; cursor: pointer; padding: 0; }
    .pa-move button:disabled { opacity: .35; cursor: default; }
    .pa-name { font-weight: 800; color: #08224A; font-size: 15.5px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .pa-badge { font-size: 11px; font-weight: 800; padding: 2px 9px; border-radius: 12px; background: #FFF4D6; color: #9A5B00; }
    .pa-meta { font-size: 12.5px; color: #64748B; margin-top: 3px; }
    .pa-price b { font-size: 20px; color: #08224A; }
    .pa-price s { color: #94A3B8; font-size: 13px; margin-left: 6px; }
    .pa-price .off { display: inline-block; margin-left: 6px; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 10px; background: #E8F8EF; color: #157347; }
    .pa-tg { display: flex; flex-direction: column; gap: 6px; font-size: 12.5px; color: #475569; }
    .pa-tg label { display: inline-flex; gap: 7px; align-items: center; cursor: pointer; white-space: nowrap; margin: 0; }
    .pa-sw { appearance: none; -webkit-appearance: none; width: 34px; height: 19px; border-radius: 20px; background: #CBD5E1; position: relative; cursor: pointer; transition: background .2s; margin: 0; flex-shrink: 0; }
    .pa-sw::after { content: ''; position: absolute; top: 2px; left: 2px; width: 15px; height: 15px; border-radius: 50%; background: #fff; transition: transform .2s; }
    .pa-sw:checked { background: #22C55E; }
    .pa-sw.ft:checked { background: #1D7FE5; }
    .pa-sw:checked::after { transform: translateX(15px); }
    .pa-acts { display: flex; gap: 6px; }
    .pa-acts button { border: 1px solid #DCECF5; background: #fff; border-radius: 9px; padding: 7px 12px; font-size: 13px; font-weight: 600; color: #1D7FE5; cursor: pointer; display: inline-flex; gap: 6px; align-items: center; }
    .pa-acts button.del { color: #DC2626; border-color: #F5C6CB; }
    .pa-acts button.del:hover { background: #DC2626; color: #fff; }
    .pa-empty { padding: 26px; text-align: center; border: 2px dashed #DCECF5; border-radius: 14px; color: #64748B; }

    .pa-modal { position: fixed; inset: 0; z-index: 3000; background: rgba(8,34,74,.45); display: flex; align-items: flex-start; justify-content: center; padding: 30px 16px; overflow-y: auto; }
    .pa-modal[hidden] { display: none; }
    .pa-box { background: #fff; border-radius: 18px; width: 100%; max-width: 560px; padding: 22px; box-shadow: 0 24px 60px rgba(8,34,74,.3); }
    .pa-box h4 { margin: 0 0 16px; color: #08224A; font-size: 18px; font-weight: 800; display: flex; gap: 8px; align-items: center; }
    .pa-box h4 button { margin-left: auto; border: none; background: none; font-size: 20px; color: #64748B; cursor: pointer; }
    .pa-f { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .pa-f .full { grid-column: 1 / -1; }
    .pa-f label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .pa-f label small { font-weight: 500; color: #94A3B8; }
    .pa-f input[type=text], .pa-f input[type=number], .pa-f select, .pa-f textarea { width: 100%; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 9px 12px; font-size: 14px; outline: none; background: #F8FBFE; font-family: inherit; }
    .pa-f input:focus, .pa-f select:focus, .pa-f textarea:focus { border-color: #1D7FE5; background: #fff; }
    .pa-f textarea { min-height: 120px; resize: vertical; line-height: 1.55; }
    .pa-f .chk { display: flex; gap: 18px; flex-wrap: wrap; }
    .pa-f .chk label { display: inline-flex; gap: 8px; align-items: center; font-weight: 600; margin: 0; cursor: pointer; }
    .pa-err { grid-column: 1 / -1; color: #B91C1C; background: #FEF2F2; border-radius: 10px; padding: 8px 12px; font-size: 13px; }
    .pa-err[hidden] { display: none; }
    .pa-foot { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
    .pa-foot button { border: 1.5px solid #DCECF5; background: #fff; border-radius: 10px; padding: 10px 18px; font-weight: 700; font-size: 14px; color: #475569; cursor: pointer; }
    .pa-foot button.sv { background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; border-color: transparent; }
    .pa-foot button:disabled { opacity: .6; }
    .bn-tabs { display: inline-flex; gap: 4px; padding: 4px; background: #F1F5F9; border-radius: 12px; margin-bottom: 12px; }
    .bn-tabs button { border: none; background: none; padding: 8px 16px; border-radius: 9px; font-weight: 700; font-size: 13.5px; color: #475569; cursor: pointer; }
    .bn-tabs button.on { background: #fff; color: #1D7FE5; box-shadow: 0 2px 6px rgba(0,0,0,.08); }
    .bn-list { display: flex; flex-direction: column; gap: 10px; }
    .bn-row { display: grid; grid-template-columns: 30px 120px minmax(0, 1fr) minmax(0, 1.6fr) 36px; gap: 10px; align-items: center; padding: 10px; border: 1.5px solid #DCECF5; border-radius: 12px; }
    .bn-row .mv { display: flex; flex-direction: column; gap: 2px; }
    .bn-row .mv button { border: 1px solid #DCECF5; background: #fff; border-radius: 6px; width: 28px; height: 20px; font-size: 10px; color: #475569; cursor: pointer; padding: 0; }
    .bn-row .mv button:disabled { opacity: .35; }
    .bn-row select, .bn-row input { width: 100%; border: 1.5px solid #DCECF5; border-radius: 9px; padding: 8px 10px; font-size: 13.5px; background: #F8FBFE; outline: none; font-family: inherit; }
    .bn-row select:focus, .bn-row input:focus { border-color: #1D7FE5; background: #fff; }
    .bn-ic { display: flex; gap: 6px; align-items: center; }
    .bn-ic i { width: 30px; height: 30px; border-radius: 9px; background: #E3F2FF; color: #1D7FE5; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .bn-row .rm { border: 1px solid #FCA5A5; background: #fff; color: #DC2626; border-radius: 9px; height: 34px; cursor: pointer; }
    .bn-foot { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 14px; }
    .bn-add, .bn-reset { border: 1.5px solid #DCECF5; background: #fff; color: #1D7FE5; font-weight: 700; font-size: 13.5px; padding: 9px 14px; border-radius: 10px; cursor: pointer; display: inline-flex; gap: 7px; align-items: center; }
    .bn-reset { margin-left: auto; color: #64748B; }
    .bn-msg { font-size: 13px; font-weight: 700; color: #15803D; }
    @media (max-width: 720px) { .bn-row { grid-template-columns: 30px minmax(0, 1fr) 36px; } .bn-row .bn-t, .bn-row .bn-d { grid-column: 2 / 3; } .bn-row .bn-ic { grid-column: 2 / 3; } .bn-row .rm { grid-column: 3; grid-row: 1; } }
    .bnr-prev { position: relative; height: 170px; border-radius: 14px; overflow: hidden; display: flex; align-items: center; padding: 0 22px; color: #fff;
        background: linear-gradient(90deg, #0E4A5E 0%, #0E4A5E 34%, rgba(14,74,94,.82) 46%, rgba(14,74,94,0) 64%), var(--img) right center / cover no-repeat, linear-gradient(120deg, #0E4A5E, #1B6A80); }
    .bnr-prev .bnr-img { display: none; }
    .bnr-prev.imgonly:not(.off) { height: auto; padding: 0; background: #DCEBF5; }
    .bnr-prev.imgonly:not(.off) .bnr-txt { display: none; }
    .bnr-prev.imgonly:not(.off) .bnr-img { display: block; width: 100%; height: auto; }
    .bnr-prev.off { background: radial-gradient(circle at 85% 30%, rgba(2,184,245,.35), transparent 45%), linear-gradient(120deg, #0E4A5E, #1B6A80); }
    .bnr-prev small { display: inline-flex; gap: 6px; align-items: center; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 12px; background: rgba(255,255,255,.16); }
    .bnr-prev b { display: block; font-size: 24px; font-weight: 800; margin: 8px 0 4px; }
    .bnr-prev span { font-size: 12.5px; opacity: .85; }
    .bnr-row { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
    .bnr-row input[type=text] { flex: 1; min-width: 220px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 9px 12px; font-size: 13.5px; background: #F8FBFE; outline: none; }
    .bnr-row input[type=text]:focus { border-color: #1D7FE5; background: #fff; }
    .pa-free { flex-basis: 100%; order: 3; display: flex; gap: 12px; align-items: center; margin: 4px 0 2px; padding: 12px 14px; border-radius: 12px; border: 1.5px dashed #CBD5E1; background: #F8FAFC; cursor: pointer; }
    .pa-free.on { border-color: #22C55E; background: #F0FDF4; }
    .pa-free b { display: block; color: #08224A; font-size: 14.5px; }
    .pa-free em { font-style: normal; font-size: 12.5px; color: #64748B; }
    .pa-free.on em { color: #15803D; font-weight: 700; }
    .pa-sec-h small { order: 2; }
    [data-only-student][hidden], [data-only-teacher][hidden] { display: none; }

    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 20px 15px; } }
    @media (max-width: 720px) {
        .pa-row { grid-template-columns: 34px minmax(0, 1fr) auto; }
        .pa-tg { grid-column: 2 / -1; flex-direction: row; gap: 16px; }
        .pa-acts { grid-column: 2 / -1; }
        .pa-acts button { flex: 1; justify-content: center; }
        .pa-f { grid-template-columns: 1fr; }
    }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="pa-head">
        <div>
            <h2><i class="fas fa-box-open me-2" style="color:#1D7FE5;"></i>Packages</h2>
            <p>শিক্ষকের Subscription আর শিক্ষার্থীর Tuition Post প্যাকেজ — ইচ্ছামতো যোগ, এডিট, চালু/বন্ধ আর উপরে-নিচে সাজান। (পেমেন্ট সিস্টেম পরে যুক্ত হবে)</p>
        </div>
        <a class="pa-view" href="/Package" target="_blank"><i class="fas fa-external-link-alt"></i> প্যাকেজ পেজ দেখুন</a>
    </div>
    <?= csrf_field() ?>

    <div class="pa-sec" id="sec-Banner">
        <div class="pa-sec-h">
            <h5><i class="fas fa-image" style="color:#0EA5E9;"></i> Package পেজের ব্যানার ছবি</h5>
            <small>Package পেজের একদম উপরের ছবি। ছবিটা পুরোটা দেখায়, নিচে “শিক্ষকদের জন্য / শিক্ষার্থীদের জন্য” ট্যাব বসে। ছবির ভিতরে লেখা না থাকলে “ছবির উপর লেখা বসাও” চালু করুন — তখন বাম দিকে লেখা বসবে। (চওড়া ছবি ভালো, সর্বোচ্চ 5 MB)</small>
        </div>
        <div class="bnr-prev<?= $pkb['pk_banner_enabled'] === '0' ? ' off' : '' ?><?= ($pkb['pk_banner_text'] ?? '0') !== '1' ? ' imgonly' : '' ?>" id="pkbBox" style="--img:url('<?= e($pkb['pk_banner']) ?>')">
            <div class="bnr-txt"><small><i class="fas fa-graduation-cap"></i> Tutor &amp; Student Service</small><b>মাষ্টার মশাই প্যাকেজ</b><span>এভাবে পেজে দেখাবে</span></div><img class="bnr-img" src="<?= e($pkb['pk_banner']) ?>" alt="" />
        </div>
        <div class="bnr-row">
            <label class="bn-add" for="pkbFile2" id="pkbUp"><i class="fas fa-upload"></i> নতুন ছবি আপলোড</label>
            <input type="file" id="pkbFile2" accept="image/jpeg,image/png,image/gif,image/webp" hidden />
            <input type="text" id="pkbUrl" value="<?= e($pkb['pk_banner']) ?>" placeholder="অথবা ছবির লিংক দিন — https://..." />
        </div>
        <div class="bn-foot">
            <label style="display:inline-flex;gap:8px;align-items:center;font-weight:600;font-size:13.5px;margin:0;cursor:pointer;"><input type="checkbox" class="pa-sw" id="pkbOn"<?= $pkb['pk_banner_enabled'] !== '0' ? ' checked' : '' ?> /> ব্যানার ছবি দেখাও</label>
            <label style="display:inline-flex;gap:8px;align-items:center;font-weight:600;font-size:13.5px;margin:0;cursor:pointer;" title="ছবির ভিতরে লেখা না থাকলে চালু করুন"><input type="checkbox" class="pa-sw" id="pkbText"<?= ($pkb['pk_banner_text'] ?? '0') === '1' ? ' checked' : '' ?> /> ছবির উপর লেখা বসাও</label>
            <span class="bn-msg" id="pkbMsg"></span>
            <button type="button" class="bn-reset" id="pkbReset"><i class="fas fa-rotate-left"></i> Default ছবি</button>
            <button type="button" class="pa-add" id="pkbSave" style="margin-left:0;"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>

    <div class="pa-sec" id="sec-Teacher">
        <div class="pa-sec-h">
            <h5><i class="fas fa-chalkboard-teacher" style="color:#1D7FE5;"></i> শিক্ষকদের Subscription</h5>
            <button type="button" class="pa-add" data-pa-add="Teacher"><i class="fas fa-plus"></i> নতুন প্যাকেজ</button>
            <small>মেয়াদ (মাস) অনুযায়ী প্ল্যান — যেমন ১ মাস ৳100, ৩ মাস ৳300 …</small>
        </div>
        <div class="pa-list" id="list-Teacher"></div>
    </div>

    <div class="pa-sec" id="sec-Student">
        <div class="pa-sec-h">
            <h5><i class="fas fa-user-graduate" style="color:#157347;"></i> শিক্ষার্থীদের Tuition Post প্যাকেজ</h5>
            <button type="button" class="pa-add" data-pa-add="Student"><i class="fas fa-plus"></i> নতুন প্যাকেজ</button>
            <label class="pa-free<?= $ViewBag->StudentFree ? ' on' : '' ?>" id="paFreeLbl"><input type="checkbox" class="pa-sw" id="paFree"<?= $ViewBag->StudentFree ? ' checked' : '' ?> />
                <span><b>শিক্ষার্থীদের পোস্ট এখন ফ্রি</b><em id="paFreeTxt"><?= $ViewBag->StudentFree ? 'চালু — কেউ টাকা ছাড়াই পোস্ট করতে পারবে' : 'বন্ধ — প্যাকেজের দাম লাগবে' ?></em></span></label>
            <small>“Teacher Needed / Tutor Required” পোস্ট করার প্যাকেজ — যেমন ১টি পোস্ট ৳100। Account খোলা সবসময় ফ্রি।</small>
        </div>
        <div class="pa-list" id="list-Student"></div>
    </div>
    <div class="pa-sec" id="sec-Benefits">
        <div class="pa-sec-h">
            <h5><i class="fas fa-list-check" style="color:#7C3AED;"></i> প্যাকেজের বিস্তারিত — কিনলে যা যা পাবে</h5>
            <small>Package পেজে প্যাকেজের নিচে এই ফিচারগুলো আইকন, শিরোনাম আর বর্ণনাসহ দেখায়। ইচ্ছামতো যোগ, এডিট, সাজানো বা মুছে ফেলুন। (প্রতিটা প্যাকেজের নিজের সুবিধার তালিকা ওই প্যাকেজের Edit থেকে বদলানো যায়।)</small>
        </div>
        <div class="bn-tabs">
            <button type="button" class="on" data-bn-tab="Teacher"><i class="fas fa-chalkboard-teacher"></i> শিক্ষক</button>
            <button type="button" data-bn-tab="Student"><i class="fas fa-user-graduate"></i> শিক্ষার্থী</button>
        </div>
        <div class="bn-list" id="bnList"></div>
        <div class="bn-foot">
            <button type="button" class="bn-add" id="bnAdd"><i class="fas fa-plus"></i> নতুন ফিচার</button>
            <span class="bn-msg" id="bnMsg"></span>
            <button type="button" class="bn-reset" id="bnReset"><i class="fas fa-rotate-left"></i> Default</button>
            <button type="button" class="pa-add" id="bnSave" style="margin-left:0;"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>


<div class="pa-modal" id="paModal" hidden role="dialog" aria-modal="true" aria-labelledby="paTitle">
    <form class="pa-box" id="paForm" novalidate>
        <h4 id="paTitle"><span>নতুন প্যাকেজ</span><button type="button" data-pa-close aria-label="Close">&times;</button></h4>
        <input type="hidden" name="id" value="0" />
        <div class="pa-f">
            <div>
                <label for="paAud">কার জন্য</label>
                <select id="paAud" name="audience"><option value="Teacher">শিক্ষক (Subscription)</option><option value="Student">শিক্ষার্থী (Tuition Post)</option></select>
            </div>
            <div>
                <label for="paName">প্যাকেজের নাম</label>
                <input type="text" id="paName" name="name" maxlength="80" placeholder="যেমন: ১ মাস" />
            </div>
            <div>
                <label for="paPrice">দাম (৳)</label>
                <input type="number" id="paPrice" name="price" min="0" step="1" placeholder="100" />
            </div>
            <div>
                <label for="paReg">নিয়মিত মূল্য (৳) <small>— ছাড় দেখাতে, না হলে খালি</small></label>
                <input type="number" id="paReg" name="regularPrice" min="0" step="1" placeholder="1000" />
            </div>
            <div>
                <label for="paMonths">মেয়াদ (মাস) <small data-only-student>— 0 = সীমা নেই</small></label>
                <input type="number" id="paMonths" name="durationMonths" min="0" max="120" step="1" placeholder="1" />
            </div>
            <div data-only-student>
                <label for="paLimit">কয়টি পোস্ট <small>— 0 = আনলিমিটেড</small></label>
                <input type="number" id="paLimit" name="postLimit" min="0" max="1000" step="1" placeholder="1" />
            </div>
            <div>
                <label for="paBadge">ব্যাজ <small>— যেমন: জনপ্রিয়, 90% ছাড়</small></label>
                <input type="text" id="paBadge" name="badge" maxlength="40" />
            </div>
            <div class="full">
                <label for="paDesc">ছোট বর্ণনা</label>
                <input type="text" id="paDesc" name="description" maxlength="300" />
            </div>
            <div class="full">
                <label for="paFeat">সুবিধাগুলো <small>— প্রতি লাইনে একটি (সর্বোচ্চ ১৫টি)</small></label>
                <textarea id="paFeat" name="features"></textarea>
            </div>
            <div class="full chk">
                <label><input type="checkbox" class="pa-sw" name="isActive" value="1" checked /> চালু (পেজে দেখাবে)</label>
                <label><input type="checkbox" class="pa-sw ft" name="isFeatured" value="1" /> হাইলাইট করুন</label>
            </div>
            <div class="pa-err" id="paErr" hidden></div>
        </div>
        <div class="pa-foot">
            <button type="button" data-pa-close>বাতিল</button>
            <button type="submit" class="sv"><i class="fas fa-save"></i> সেভ করুন</button>
        </div>
    </form>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var PK = <?= js(array_map(fn($p) => (array)$p, $packages)) ?>;
    function esc(t) { return String(t == null ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
    function tk(n) { return '৳' + Number(n).toLocaleString('en-US'); }
    function token() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    async function post(url, data) {
        var fd = data instanceof FormData ? data : new FormData();
        if (!(data instanceof FormData)) Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', token());
        var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        return r.json();
    }
    function byAud(a) { return PK.filter(function (p) { return p.Audience === a; }); }

    function row(p, i, n) {
        var feats = (p.FeatureList || []).length;
        return '<div class="pa-row' + (p.IsActive ? '' : ' off') + (p.IsFeatured ? ' feat' : '') + '" data-id="' + p.Id + '">'
            + '<div class="pa-move"><button type="button" data-mv="-1" title="উপরে"' + (i === 0 ? ' disabled' : '') + '><i class="fas fa-chevron-up"></i></button>'
            + '<button type="button" data-mv="1" title="নিচে"' + (i === n - 1 ? ' disabled' : '') + '><i class="fas fa-chevron-down"></i></button></div>'
            + '<div><div class="pa-name">' + esc(p.Name) + (p.Badge ? '<span class="pa-badge">' + esc(p.Badge) + '</span>' : '') + '</div>'
            + '<div class="pa-meta"><i class="far fa-clock"></i> ' + esc(p.DurationLabel) + ' · ' + feats + 'টি সুবিধা' + (p.IsActive ? '' : ' · <b style="color:#B91C1C;">বন্ধ</b>') + '</div></div>'
            + '<div class="pa-price"><b>' + tk(p.Price) + '</b>' + (p.RegularPrice ? '<s>' + tk(p.RegularPrice) + '</s><span class="off">' + p.Discount + '% ছাড়</span>' : '') + '</div>'
            + '<div class="pa-tg"><label><input type="checkbox" class="pa-sw" data-tg="active"' + (p.IsActive ? ' checked' : '') + '> চালু</label>'
            + '<label><input type="checkbox" class="pa-sw ft" data-tg="featured"' + (p.IsFeatured ? ' checked' : '') + '> হাইলাইট</label></div>'
            + '<div class="pa-acts"><button type="button" data-ed><i class="fas fa-pen"></i> Edit</button><button type="button" class="del" data-del><i class="fas fa-trash"></i></button></div>'
            + '</div>';
    }
    function render() {
        ['Teacher', 'Student'].forEach(function (a) {
            var list = byAud(a), el = document.getElementById('list-' + a);
            el.innerHTML = list.length ? list.map(function (p, i) { return row(p, i, list.length); }).join('')
                : '<div class="pa-empty"><i class="fas fa-box-open"></i> কোনো প্যাকেজ নেই — “নতুন প্যাকেজ” চেপে যোগ করুন।</div>';
        });
    }
    function find(id) { return PK.find(function (p) { return p.Id === id; }); }
    function replace(p) { var i = PK.findIndex(function (x) { return x.Id === p.Id; }); if (i > -1) PK[i] = p; else PK.push(p); }

    // ===== ফর্ম =====
    var modal = document.getElementById('paModal'), form = document.getElementById('paForm'), err = document.getElementById('paErr');
    function syncAud() {
        var st = form.audience.value === 'Student';
        document.querySelectorAll('[data-only-student]').forEach(function (x) { x.hidden = !st; });
        form.name.placeholder = st ? 'যেমন: Tuition Post' : 'যেমন: ১ মাস';
    }
    form.audience.addEventListener('change', syncAud);
    function open(p, aud) {
        p = p || { Id: 0, Audience: aud, Name: '', Price: '', RegularPrice: '', DurationMonths: aud === 'Teacher' ? 1 : 0, PostLimit: 1, Badge: '', Description: '', Features: '', IsActive: true, IsFeatured: false };
        form.id.value = p.Id; form.audience.value = p.Audience; form.name.value = p.Name || '';
        form.price.value = p.Price === '' ? '' : p.Price; form.regularPrice.value = p.RegularPrice || '';
        form.durationMonths.value = p.DurationMonths; form.postLimit.value = p.PostLimit;
        form.badge.value = p.Badge || ''; form.description.value = p.Description || ''; form.features.value = p.Features || '';
        form.isActive.checked = !!p.IsActive; form.isFeatured.checked = !!p.IsFeatured;
        document.querySelector('#paTitle span').textContent = p.Id ? 'প্যাকেজ এডিট' : 'নতুন প্যাকেজ';
        err.hidden = true; syncAud(); modal.hidden = false; form.name.focus();
    }
    function close() { modal.hidden = true; }
    document.querySelectorAll('[data-pa-add]').forEach(function (b) { b.addEventListener('click', function () { open(null, b.getAttribute('data-pa-add')); }); });
    modal.addEventListener('click', function (e) { if (e.target === modal || e.target.closest('[data-pa-close]')) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) close(); });
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var btn = form.querySelector('.sv'); btn.disabled = true; err.hidden = true;
        var fd = new FormData(form);
        fd.set('isActive', form.isActive.checked ? 'true' : 'false'); fd.set('isFeatured', form.isFeatured.checked ? 'true' : 'false');
        try {
            var d = await post('/Admin/SavePackage', fd);
            if (!d.success) { err.textContent = d.message || 'সেভ হয়নি।'; err.hidden = false; return; }
            replace(d.package); render(); close();
        } catch (x) { err.textContent = 'ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'; err.hidden = false; }
        finally { btn.disabled = false; }
    });

    // ===== লিস্টের বাটন =====
    document.addEventListener('click', async function (e) {
        var r = e.target.closest('.pa-row'); if (!r) return;
        var id = parseInt(r.getAttribute('data-id'), 10), p = find(id);
        if (e.target.closest('[data-ed]')) { open(p); return; }
        if (e.target.closest('[data-del]')) {
            if (!confirm('“' + p.Name + '” প্যাকেজটি মুছে ফেলবেন?')) return;
            var d = await post('/Admin/DeletePackage', { id: id });
            if (d.success) { PK = PK.filter(function (x) { return x.Id !== id; }); render(); } else alert(d.message || 'Failed');
            return;
        }
        var mv = e.target.closest('[data-mv]');
        if (mv && !mv.disabled) {
            var list = byAud(p.Audience), i = list.indexOf(p), j = i + parseInt(mv.getAttribute('data-mv'), 10);
            if (j < 0 || j >= list.length) return;
            var tmp = list[i]; list[i] = list[j]; list[j] = tmp;
            PK = PK.filter(function (x) { return x.Audience !== p.Audience; }).concat(list);
            render();
            var d2 = await post('/Admin/ReorderPackages', { audience: p.Audience, ids: list.map(function (x) { return x.Id; }).join(',') });
            if (!d2.success) alert(d2.message || 'Failed');
        }
    });
    document.addEventListener('change', async function (e) {
        var t = e.target.closest('[data-tg]'); if (!t) return;
        var id = parseInt(t.closest('.pa-row').getAttribute('data-id'), 10);
        t.disabled = true;
        var d = await post('/Admin/TogglePackage', { id: id, flag: t.getAttribute('data-tg'), on: t.checked ? 'true' : 'false' });
        if (d.success) { replace(d.package); render(); } else { t.checked = !t.checked; t.disabled = false; alert(d.message || 'Failed'); }
    });
    render();

    // ===== প্যাকেজের বিস্তারিত (ফিচার) এডিটর =====
    var BN = <?= js($benefits) ?>, ICONS = <?= js(package_benefit_icons()) ?>, bnAud = 'Teacher';
    var ICON_LBL = { 'fa-certificate': 'নীল টিক', 'fa-arrow-trend-up': 'উপরে', 'fa-bullhorn': 'পোস্ট', 'fa-clipboard-list': 'Apply', 'fa-users': 'স্টুডেন্ট', 'fa-headset': 'সাপোর্ট', 'fa-star': 'স্টার',
        'fa-bolt': 'দ্রুত', 'fa-shield-halved': 'নিরাপদ', 'fa-chalkboard-user': 'শিক্ষক', 'fa-user-graduate': 'শিক্ষার্থী', 'fa-pen-to-square': 'লেখা', 'fa-location-dot': 'লোকেশন',
        'fa-coins': 'টাকা', 'fa-comments': 'যোগাযোগ', 'fa-bell': 'নোটিফিকেশন', 'fa-eye': 'দেখা', 'fa-gift': 'ফ্রি', 'fa-crown': 'প্রিমিয়াম', 'fa-handshake': 'চুক্তি' };
    var bnList = document.getElementById('bnList'), bnMsg = document.getElementById('bnMsg');
    function bnRender() {
        var list = BN[bnAud] || [];
        bnList.innerHTML = list.length ? list.map(function (b, i) {
            return '<div class="bn-row" data-i="' + i + '">'
                + '<div class="mv"><button type="button" data-bmv="-1"' + (i === 0 ? ' disabled' : '') + '><i class="fas fa-chevron-up"></i></button><button type="button" data-bmv="1"' + (i === list.length - 1 ? ' disabled' : '') + '><i class="fas fa-chevron-down"></i></button></div>'
                + '<div class="bn-ic"><i class="fas ' + esc(b.i) + '"></i><select data-k="i" aria-label="Icon">' + ICONS.map(function (ic) { return '<option value="' + ic + '"' + (ic === b.i ? ' selected' : '') + '>' + (ICON_LBL[ic] || ic) + '</option>'; }).join('') + '</select></div>'
                + '<input class="bn-t" data-k="t" maxlength="60" placeholder="শিরোনাম (যেমন: প্রোফাইলে নীল টিক)" value="' + esc(b.t) + '" />'
                + '<input class="bn-d" data-k="d" maxlength="250" placeholder="ছোট বর্ণনা" value="' + esc(b.d || '') + '" />'
                + '<button type="button" class="rm" data-brm title="মুছুন"><i class="fas fa-trash"></i></button></div>';
        }).join('') : '<div class="pa-empty">কোনো ফিচার নেই — “নতুন ফিচার” চাপুন।</div>';
    }
    document.querySelectorAll('[data-bn-tab]').forEach(function (b) { b.addEventListener('click', function () {
        bnAud = b.getAttribute('data-bn-tab'); document.querySelectorAll('[data-bn-tab]').forEach(function (x) { x.classList.toggle('on', x === b); }); bnMsg.textContent = ''; bnRender();
    }); });
    bnList.addEventListener('input', function (e) { var r = e.target.closest('.bn-row'), k = e.target.getAttribute('data-k'); if (!r || !k) return;
        BN[bnAud][+r.getAttribute('data-i')][k] = e.target.value; if (k === 'i') r.querySelector('.bn-ic i').className = 'fas ' + e.target.value; bnMsg.textContent = ''; });
    bnList.addEventListener('change', function (e) { if (e.target.getAttribute('data-k') === 'i') e.target.dispatchEvent(new Event('input', { bubbles: true })); });
    bnList.addEventListener('click', function (e) {
        var r = e.target.closest('.bn-row'); if (!r) return; var i = +r.getAttribute('data-i'), list = BN[bnAud];
        if (e.target.closest('[data-brm]')) { list.splice(i, 1); bnRender(); bnMsg.textContent = ''; return; }
        var mv = e.target.closest('[data-bmv]'); if (mv && !mv.disabled) { var j = i + +mv.getAttribute('data-bmv'); var t = list[i]; list[i] = list[j]; list[j] = t; bnRender(); bnMsg.textContent = ''; }
    });
    document.getElementById('bnAdd').addEventListener('click', function () { (BN[bnAud] = BN[bnAud] || []).push({ i: 'fa-star', t: '', d: '' }); bnRender(); var ins = bnList.querySelectorAll('.bn-t'); ins[ins.length - 1].focus(); });
    async function bnSend(reset) {
        var d = await post('/Admin/SavePackageBenefits', reset ? { audience: bnAud, reset: 'true' } : { audience: bnAud, data: JSON.stringify(BN[bnAud]) });
        if (!d.success) { alert(d.message || 'Failed'); return; }
        BN[bnAud] = d.list; bnRender(); bnMsg.textContent = reset ? '✓ Default এ ফেরানো হয়েছে' : '✓ সেভ হয়েছে';
    }
    document.getElementById('bnSave').addEventListener('click', function () { bnSend(false); });
    document.getElementById('bnReset').addEventListener('click', function () { if (confirm('এই তালিকা Default এ ফেরাবেন?')) bnSend(true); });
    bnRender();

    // ===== শিক্ষার্থীদের পোস্ট ফ্রি =====
    document.getElementById('paFree').addEventListener('change', async function () {
        var c = this, on = c.checked;
        if (!confirm(on ? 'শিক্ষার্থীদের Tuition পোস্ট ফ্রি করবেন? তখন কেউ টাকা ছাড়াই পোস্ট করতে পারবে।' : 'ফ্রি বন্ধ করবেন? তখন আবার প্যাকেজের দাম লাগবে (যদি Payment Methods-এ “বাধ্যতামূলক” চালু থাকে)।')) { c.checked = !on; return; }
        c.disabled = true;
        try {
            var d = await post('/Admin/SetStudentPostFree', { on: on ? 'true' : 'false' });
            if (!d.success) { c.checked = !on; alert(d.message || 'Failed'); return; }
            document.getElementById('paFreeLbl').classList.toggle('on', d.free);
            document.getElementById('paFreeTxt').textContent = d.free ? 'চালু — কেউ টাকা ছাড়াই পোস্ট করতে পারবে' : 'বন্ধ — প্যাকেজের দাম লাগবে';
        } finally { c.disabled = false; }
    });

    // ===== Package পেজের ব্যানার ছবি =====
    var pkBox = document.getElementById('pkbBox'), pkUrl = document.getElementById('pkbUrl'), pkOn = document.getElementById('pkbOn'), pkMsg = document.getElementById('pkbMsg');
    function pkPaint() { pkBox.style.setProperty('--img', "url('" + pkUrl.value.replace(/['"()\\]/g, '') + "')"); pkBox.querySelector('.bnr-img').src = pkUrl.value; pkBox.classList.toggle('off', !pkOn.checked); pkBox.classList.toggle('imgonly', !document.getElementById('pkbText').checked); }
    document.getElementById('pkbText').addEventListener('change', function () { pkPaint(); pkMsg.textContent = ''; });
    pkUrl.addEventListener('input', function () { pkPaint(); pkMsg.textContent = ''; });
    pkOn.addEventListener('change', function () { pkPaint(); pkMsg.textContent = ''; });
    async function pkSave(reset) {
        var data = { which: 'package', pk_banner: pkUrl.value.trim(), pk_banner_enabled: pkOn.checked ? 'true' : 'false', pk_banner_text: document.getElementById('pkbText').checked ? 'true' : 'false' };
        if (reset) data.reset = 'true';
        var d = await post('/Admin/SaveCircularBanner', data);
        if (!d.success) { pkMsg.textContent = ''; alert(d.message || 'Failed'); return; }
        if (d.pk_banner) { pkUrl.value = d.pk_banner; pkOn.checked = d.pk_banner_enabled !== '0'; if (d.pk_banner_text !== undefined) document.getElementById('pkbText').checked = d.pk_banner_text === '1'; pkPaint(); }
        pkMsg.textContent = reset ? '✓ Default ছবিতে ফেরানো হয়েছে' : '✓ সেভ হয়েছে — Package পেজে বদলে গেছে';
    }
    document.getElementById('pkbFile2').addEventListener('change', async function () {
        var f = this.files[0]; if (!f) return;
        if (f.size > 5 * 1024 * 1024) { alert('ছবি ৫ MB এর কম হতে হবে।'); this.value = ''; return; }
        var lbl = document.getElementById('pkbUp'), html = lbl.innerHTML; lbl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> আপলোড হচ্ছে...';
        var fd = new FormData(); fd.append('promoImage', f);
        try {
            var d = await post('/Admin/UploadProfilePromo', fd);
            if (!d.success) { alert(d.message || 'Upload failed'); return; }
            pkUrl.value = d.imageUrl; pkOn.checked = true; pkPaint();
            await pkSave(false);
        } finally { lbl.innerHTML = html; this.value = ''; }
    });
    document.getElementById('pkbSave').addEventListener('click', function () { pkSave(false); });
    document.getElementById('pkbReset').addEventListener('click', function () { if (confirm('আগের (Default) ছবিতে ফেরত যাবেন?')) pkSave(true); });
})();
</script>
<?php section_end(); ?>
