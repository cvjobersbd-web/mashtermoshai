<style>
    .auth-wrap { min-height: 78vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px;
        background: linear-gradient(160deg, #CFF2FC 0%, #F5FBFF 55%, #ffffff 100%); }
    .auth-card { width: 100%; max-width: 440px; background: #fff; border-radius: 22px; border: 1px solid #DCECF5; box-shadow: 0 18px 50px rgba(8,34,74,.10); overflow: hidden; }
    .auth-head { text-align: center; padding: 30px 26px 8px; }
    .auth-ico { width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 50%; background: #CFF2FC; color: #028EA5; display: flex; align-items: center; justify-content: center; font-size: 26px; }
    .auth-ico.bad { background: #FDECEA; color: #c0392b; }
    .auth-head h4 { margin: 0 0 8px; color: #08224A; font-weight: 800; font-size: 21px; }
    .auth-head p { margin: 0; color: #64748B; font-size: 14px; line-height: 1.6; }
    .auth-body { padding: 20px 26px 28px; }
    .auth-label { display: block; font-weight: 700; font-size: 13.5px; color: #08224A; margin: 10px 0 6px; }
    .auth-input { position: relative; display: flex; align-items: center; border: 1.5px solid #DCECF5; border-radius: 12px; background: #F5FBFF; transition: border-color .2s, background .2s; }
    .auth-input:focus-within { border-color: #02B8F5; background: #fff; }
    .auth-input.bad { border-color: #e74c3c; }
    .auth-input > i { color: #7A8CA0; padding-left: 14px; width: 36px; flex-shrink: 0; font-size: 15px; }
    .auth-input input { flex: 1; min-width: 0; border: none; background: transparent; outline: none; padding: 12px 12px 12px 8px; font-size: 15px; color: #08224A; }
    .auth-eye { border: none; background: transparent; color: #7A8CA0; padding: 0 14px; cursor: pointer; }
    .auth-btn { margin-top: 20px; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; border-radius: 12px; padding: 13px; font-weight: 700; font-size: 15.5px; color: #fff; background: #02B8F5; box-shadow: 0 10px 22px rgba(2,184,245,.28); cursor: pointer; transition: background .2s, transform .15s; }
    .auth-btn:hover { background: #0099D6; color: #fff; }
    .auth-btn:active { transform: scale(.98); }
    .auth-btn:disabled { opacity: .7; cursor: default; }
    .auth-alert { display: flex; gap: 10px; align-items: flex-start; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; line-height: 1.6; margin-bottom: 10px; }
    .auth-alert i { margin-top: 3px; }
    .auth-alert.ok { background: #E8F8EF; color: #155724; border: 1px solid #A8E2C0; }
    .auth-alert.err { background: #FDECEA; color: #8B1E15; border: 1px solid #F5B7B1; }
    .auth-meter { height: 5px; border-radius: 4px; background: #EEF5FA; margin-top: 8px; overflow: hidden; }
    .auth-meter span { display: block; height: 100%; width: 0; transition: width .25s, background .25s; }
    .auth-hint { font-size: 12.5px; margin-top: 6px; min-height: 18px; }
    .auth-hint.bad { color: #c0392b; } .auth-hint.ok { color: #16a34a; }
    .auth-foot { text-align: center; margin-top: 18px; font-size: 14px; }
    .auth-foot a { color: #028EA5; text-decoration: none; font-weight: 600; }
    .auth-foot a:hover { text-decoration: underline; }
</style>
