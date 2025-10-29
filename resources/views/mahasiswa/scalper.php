<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ATR Scalping Calculator – Realtime</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .copy-ok { opacity: 0; transition: opacity .2s ease; }
    .copied .copy-ok { opacity: 1; }
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .highlight-tp { background-color: rgba(16, 185, 129, 0.2); }
    .highlight-sl { background-color: rgba(239, 68, 68, 0.2); }
    /* Toast animations */
    @keyframes toast-in { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes toast-out { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(8px) scale(.98); } }
    .toast { animation: toast-in .18s ease-out; }
    .toast.hide { animation: toast-out .2s ease-in forwards; }
  </style>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen">
  <div class="max-w-6xl mx-auto p-6">
    <header class="mb-6">
      <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">ATR Scalping Calculator – Al Khoodhy</h1>
      <p class="text-zinc-400 mt-1">Skema Trader • Inputs & Outputs berdampingan • Output copyable • Auto‑save • Robust Copy Fallback</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- INPUTS -->
      <section class="bg-zinc-900/60 rounded-2xl p-5 shadow-xl ring-1 ring-white/5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-semibold">Inputs</h2>
          <div class="space-x-2">
            <button id="clearSavedBtn" class="px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-sm">Hapus Tersimpan</button>
            <button id="resetBtn" class="px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-sm">Reset Default</button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-zinc-400">Equity (USDT)</label>
            <input id="equity" type="number" step="0.01" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="50" />
          </div>
          <div>
            <label class="block text-sm text-zinc-400">Risk % per Trade <span class="text-zinc-500">(decimal, 0.01 = 1%)</span></label>
            <input id="riskPct" type="number" step="0.0001" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="0.01" />
          </div>
          <div>
            <label class="block text-sm text-zinc-400">Margin Used per Trade (USDT)</label>
            <input id="margin" type="number" step="0.01" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="3" />
          </div>
          <div>
            <label class="block text-sm text-zinc-400">Leverage (×)</label>
            <input id="lev" type="number" step="1" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="50" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm text-zinc-400">Direction</label>
            <div class="mt-1 flex items-center gap-3">
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="dir" value="long" class="accent-emerald-500" checked />
                <span>Long</span>
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="dir" value="short" class="accent-emerald-500" />
                <span>Short</span>
              </label>
            </div>
          </div>
          <div>
            <label class="block text-sm text-zinc-400">Entry Price</label>
            <input id="entry" type="number" step="0.00000001" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="0.02" />
          </div>
          <div>
            <label class="block text-sm text-zinc-400">ATR (sesuai timeframe entry)</label>
            <input id="atr" type="number" step="0.00000001" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="0.00006" />
          </div>
          <div>
            <label class="block text-sm text-zinc-400">SL Multiple of ATR</label>
            <input id="slMult" type="number" step="0.1" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="2" />
          </div>
          <div>
            <label class="block text-sm text-zinc-400">TP Multiple of ATR</label>
            <input id="tpMult" type="number" step="0.1" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="1.2" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm text-zinc-400">Taker Fee Rate (per side, decimal) — contoh: 0.0006 = 0.06%</label>
            <input id="fee" type="number" step="0.0001" class="w-full mt-1 px-3 py-2 rounded-xl bg-zinc-800/70" value="0.0006" />
          </div>
        </div>
        <p class="text-xs text-zinc-500 mt-3">Tip: Risk % adalah desimal (mis. 0.04 = 4%). Nilai input otomatis <span class="italic">disimpan</span> ke perangkat ini.</p>
      </section>

      <!-- OUTPUTS -->
      <section class="bg-zinc-900/60 rounded-2xl p-5 shadow-xl ring-1 ring-white/5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-semibold">Outputs</h2>
          <button id="runTests" class="px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-sm">Run Self‑Tests</button>
        </div>
        <div id="outputs" class="space-y-3"></div>
        <pre id="testLog" class="mt-4 text-xs text-zinc-400 whitespace-pre-wrap"></pre>
      </section>
    </div>
  </div>

  <!-- Toast Host -->
  <div id="toastHost" class="fixed inset-x-0 bottom-4 z-50 flex justify-center pointer-events-none"></div>

  <template id="rowTpl">
    <div class="flex items-center justify-between gap-4 p-3 rounded-xl ring-1 ring-white/10 __CLASS__">
      <div>
        <div class="text-sm text-zinc-400">__LABEL__</div>
        <div class="font-mono text-base" data-key="__KEY__">__VALUE__</div>
      </div>
      <button class="copyBtn group inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-sm" data-copy="__KEY__" data-raw="">
        <span>Copy</span><span class="copy-ok text-emerald-400 text-xs">✓</span>
      </button>
    </div>
  </template>

  <!-- Modal fallback manual copy -->
  <dialog id="copyModal" class="rounded-2xl p-0 bg-zinc-900 text-zinc-100 w-[90%] max-w-lg">
    <div class="p-5">
      <h3 class="text-lg font-semibold mb-2">Manual Copy</h3>
      <p class="text-sm text-zinc-400 mb-3">Lingkungan ini membatasi akses clipboard. Nilai di bawah sudah dipilih, tekan <span class="font-semibold">Ctrl/Cmd + C</span> untuk menyalin.</p>
      <input id="copyField" class="w-full px-3 py-2 rounded-xl bg-zinc-800/70 font-mono" />
      <div class="mt-4 text-right">
        <button id="closeModal" class="px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-sm">Tutup</button>
      </div>
    </div>
  </dialog>

  <script>
    const STORAGE_KEY = 'atrCalc_v6';
    const $ = s => document.querySelector(s);

    // Guard: ensure required containers exist
    function ensureToastHost(){
      let host = $('#toastHost');
      if (!host) {
        host = document.createElement('div');
        host.id = 'toastHost';
        host.className = 'fixed inset-x-0 bottom-4 z-50 flex justify-center pointer-events-none';
        document.body.appendChild(host);
      }
      return host;
    }

    const inputs = {
      equity: $('#equity'),
      riskPct: $('#riskPct'),
      margin: $('#margin'),
      lev: $('#lev'),
      dir: () => {
        const el = document.querySelector('input[name="dir"]:checked');
        return el ? el.value : 'long';
      },
      entry: $('#entry'),
      atr: $('#atr'),
      slMult: $('#slMult'),
      tpMult: $('#tpMult'),
      fee: $('#fee')
    };

    const outSpec = [
      ['Position Nominal (USDT)','posNom',''],
      ['Qty (Units)','qty',''],
      ['SL Price','slPrice','highlight-sl'],
      ['TP Price','tpPrice','highlight-tp'],
      ['PnL at TP (after fee, USDT)','pnlTp',''],
      ['PnL at SL (after fee, USDT)','pnlSl',''],
      ['R:R (after fee)','rr',''],
      ['% Return on Margin @TP','pctMarginTp',''],
      ['% Return on Margin @SL','pctMarginSl',''],
      ['% Return on Equity @TP','pctEquityTp',''],
      ['% Return on Equity @SL','pctEquitySl',''],
      ['Max Risk Allowed (USDT)','maxRisk',''],
      ['Expected Loss @SL (USDT)','lossAtSl',''],
      ['Within Risk Limit?','riskOk','']
    ];

    const outputsWrap = $('#outputs');

    /* ---------- utils ---------- */
    function fmtNum(x,d=6){ if(!isFinite(x)) return '-'; return Number(x).toLocaleString(undefined,{minimumFractionDigits:0,maximumFractionDigits:d}); }
    function fmtPct(x,d=2){ if(!isFinite(x)) return '-'; return (x*100).toFixed(d)+'%'; }
    // Plain number without thousands separator, using dot decimal; trim trailing zeros
    function toPlain(x,dec=8){ if(!isFinite(x)) return ''; let s=Number(x).toFixed(dec); if(s.indexOf('.')>=0){ s=s.replace(/0+$/,''); s=s.replace(/\.$/,''); } return s; }

    /* ---------- toast ---------- */
    function showToast(text){
      const host = ensureToastHost();
      const item = document.createElement('div');
      item.className = 'toast pointer-events-auto bg-zinc-900/90 text-zinc-100 border border-white/10 shadow-xl rounded-xl px-3 py-2 text-sm';
      item.textContent = text;
      host.appendChild(item);
      setTimeout(()=>{ if(item && item.classList) item.classList.add('hide'); }, 1000); // start exit
      item.addEventListener('animationend', ()=>{ if(item && item.classList && item.classList.contains('hide')) item.remove(); });
    }

    /* ---------- clipboard with fallbacks ---------- */
    async function copyText(text){
      if (!text) return false;
      if (navigator.clipboard && window.isSecureContext) {
        try { await navigator.clipboard.writeText(text); return true; } catch(e) { /* fallthrough */ }
      }
      try {
        const ta = document.createElement('textarea');
        ta.value = text; ta.setAttribute('readonly',''); ta.style.position='fixed'; ta.style.opacity='0';
        document.body.appendChild(ta); ta.select(); ta.setSelectionRange(0, ta.value.length);
        const ok = document.execCommand('copy'); document.body.removeChild(ta);
        if (ok) return true;
      } catch(e) { /* fallthrough */ }
      const dlg = $('#copyModal'); const field = $('#copyField');
      if (dlg && field) { field.value = text; if (typeof dlg.showModal === 'function') { dlg.showModal(); field.focus(); field.select(); } }
      return false;
    }

    /* ---------- build rows ---------- */
    function buildRows(){
      if (!outputsWrap) return;
      outputsWrap.innerHTML='';
      const tplEl = document.querySelector('#rowTpl');
      if (!tplEl) { console.error('rowTpl missing'); return; }
      const tpl = tplEl.innerHTML;
      outSpec.forEach(([label,key,cls])=>{
        outputsWrap.insertAdjacentHTML('beforeend',tpl
          .replace('__LABEL__',label)
          .replaceAll('__KEY__',key)
          .replace('__CLASS__',cls)
          .replace('__VALUE__','-'));
      });
      outputsWrap.querySelectorAll('.copyBtn').forEach(btn=>{
        btn.addEventListener('click',async e=>{
          const raw=e.currentTarget ? (e.currentTarget.getAttribute('data-raw')||'') : '';
          const ok = await copyText(raw);
          if (ok && e.currentTarget && e.currentTarget.classList) {
            showToast('Copied');
            e.currentTarget.classList.add('copied');
            setTimeout(()=>{ if(e.currentTarget && e.currentTarget.classList) e.currentTarget.classList.remove('copied'); },900);
          }
        });
      });
      const closeBtn = $('#closeModal');
      if (closeBtn) closeBtn.addEventListener('click',()=> { const dlg=$('#copyModal'); if(dlg && typeof dlg.close==='function') dlg.close(); });
    }

    /* ---------- state helpers ---------- */
    function getValues(){
      return {
        equity: parseFloat(inputs.equity?.value)||0,
        riskPct: parseFloat(inputs.riskPct?.value)||0,
        margin: parseFloat(inputs.margin?.value)||0,
        lev: parseFloat(inputs.lev?.value)||0,
        dir: inputs.dir(),
        entry: parseFloat(inputs.entry?.value)||0,
        atr: parseFloat(inputs.atr?.value)||0,
        slMult: parseFloat(inputs.slMult?.value)||0,
        tpMult: parseFloat(inputs.tpMult?.value)||0,
        fee: parseFloat(inputs.fee?.value)||0,
      };
    }
    function setValues(v){ if(!v) return; for (const k in v){ if(k==='dir'){ const el=document.querySelector(`input[name="dir"][value="${v[k]}"]`); if(el) el.checked=true; } else if(inputs[k]) { inputs[k].value=v[k]; } } }
    function save(){ try{ localStorage.setItem(STORAGE_KEY, JSON.stringify(getValues())); }catch(e){} }
    function load(){ try{ const raw=localStorage.getItem(STORAGE_KEY); return raw?JSON.parse(raw):null; }catch(e){ return null; } }

    /* ---------- compute ---------- */
    function compute(){
      const {equity,riskPct,margin,lev,dir,entry,atr,slMult,tpMult,fee}=getValues();
      const posNom=margin*lev;
      const qty = entry>0 ? posNom/entry : NaN;
      const isLong = dir==='long';
      const slPrice = isLong ? (entry - slMult*atr) : (entry + slMult*atr);
      const tpPrice = isLong ? (entry + tpMult*atr) : (entry - tpMult*atr);
      const pnlTpBefore = isLong ? (qty*(tpPrice-entry)) : (qty*(entry-tpPrice));
      const pnlSlBefore = isLong ? (qty*(slPrice-entry)) : (qty*(entry-slPrice));
      const feeEntry = posNom*fee; const feeExitTp=(qty*Math.abs(tpPrice))*fee; const feeExitSl=(qty*Math.abs(slPrice))*fee;
      const pnlTp = pnlTpBefore - feeEntry - feeExitTp;
      const pnlSl = pnlSlBefore - feeEntry - feeExitSl;
      const maxRisk = equity*riskPct; const lossAtSl=Math.abs(pnlSl); const riskOk = lossAtSl<=maxRisk+1e-9;
      const rr = (pnlSl<0) ? (pnlTp/Math.abs(pnlSl)) : (pnlTp>0 && pnlSl>0 ? '∞' : NaN);
      const pctMarginTp = margin>0 ? (pnlTp/margin) : NaN;
      const pctMarginSl = margin>0 ? (pnlSl/margin) : NaN;
      const pctEquityTp = equity>0 ? (pnlTp/equity) : NaN;
      const pctEquitySl = equity>0 ? (pnlSl/equity) : NaN;

      const out = {
        posNom: fmtNum(posNom,4),
        qty: fmtNum(qty,6),
        slPrice: fmtNum(slPrice,8),
        tpPrice: fmtNum(tpPrice,8),
        pnlTp: fmtNum(pnlTp,6),
        pnlSl: fmtNum(pnlSl,6),
        rr: (typeof rr==='string') ? rr : (isFinite(rr) ? rr.toFixed(2) : '-'),
        pctMarginTp: isFinite(pctMarginTp) ? fmtPct(pctMarginTp,2) : '-',
        pctMarginSl: isFinite(pctMarginSl) ? fmtPct(pctMarginSl,2) : '-',
        pctEquityTp: isFinite(pctEquityTp) ? fmtPct(pctEquityTp,2) : '-',
        pctEquitySl: isFinite(pctEquitySl) ? fmtPct(pctEquitySl,2) : '-',
        maxRisk: fmtNum(maxRisk,4),
        lossAtSl: fmtNum(lossAtSl,6),
        riskOk: riskOk ? 'YES' : 'NO'
      };

      const raw = {
        posNom: toPlain(posNom,8),
        qty: String(Math.floor(qty||0)),
        slPrice: toPlain(slPrice,8),
        tpPrice: toPlain(tpPrice,8),
        pnlTp: toPlain(pnlTp,8),
        pnlSl: toPlain(pnlSl,8),
        rr: (typeof rr==='string') ? rr : (isFinite(rr)? toPlain(rr,4) : ''),
        pctMarginTp: toPlain(pctMarginTp,6),
        pctMarginSl: toPlain(pctMarginSl,6),
        pctEquityTp: toPlain(pctEquityTp,6),
        pctEquitySl: toPlain(pctEquitySl,6),
        maxRisk: toPlain(maxRisk,8),
        lossAtSl: toPlain(lossAtSl,8),
        riskOk: riskOk ? 'YES' : 'NO'
      };

      outSpec.forEach(([_,key])=>{
        const valEl = outputsWrap?.querySelector(`[data-key="${key}"]`);
        if (valEl) valEl.textContent = out[key];
        const btn = outputsWrap?.querySelector(`[data-copy="${key}"]`);
        if (btn) btn.setAttribute('data-raw', raw[key] ?? '');
      });
    }

    /* ---------- binding ---------- */
    function bind(){
      ['#equity','#riskPct','#margin','#lev','#entry','#atr','#slMult','#tpMult','#fee'].forEach(sel=>{
        const el = document.querySelector(sel);
        if (el) el.addEventListener('input',()=>{ save(); compute(); });
      });
      document.querySelectorAll('input[name="dir"]').forEach(el=> el.addEventListener('change',()=>{ save(); compute(); }));
      const resetBtn = $('#resetBtn');
      if (resetBtn) resetBtn.addEventListener('click',()=>{
        if (inputs.equity) inputs.equity.value=50;
        if (inputs.riskPct) inputs.riskPct.value=0.01;
        if (inputs.margin) inputs.margin.value=3;
        if (inputs.lev) inputs.lev.value=50;
        const dirLong = document.querySelector('input[name="dir"][value="long"]');
        if (dirLong) dirLong.checked=true;
        if (inputs.entry) inputs.entry.value=0.02;
        if (inputs.atr) inputs.atr.value=0.00006;
        if (inputs.slMult) inputs.slMult.value=2;
        if (inputs.tpMult) inputs.tpMult.value=1.2;
        if (inputs.fee) inputs.fee.value=0.0006;
        save(); compute();
      });
      const clearBtn = $('#clearSavedBtn');
      if (clearBtn) clearBtn.addEventListener('click',()=>{ try{ localStorage.removeItem(STORAGE_KEY); showToast('Saved inputs cleared'); }catch(e){} });
      const runBtn = $('#runTests');
      if (runBtn) runBtn.addEventListener('click', runSelfTests);
    }

    /* ---------- tests ---------- */
    function logTest(msg){ const el=$('#testLog'); if (el) el.textContent += msg + "\n"; }
    function resetLog(){ const el=$('#testLog'); if (el) el.textContent=''; }
    function nearly(a,b,eps=1e-9){ return Math.abs(a-b) < eps; }
    function readNum(selector){ const el = document.querySelector(selector); return el ? parseFloat(el.textContent.replace(/,/g,'')) : NaN; }
    function runSelfTests(){
      resetLog();
      // Test 1: LONG signs & prices
      const t1 = { equity:100, riskPct:0.01, margin:10, lev:10, dir:'long', entry:100, atr:1, slMult:2, tpMult:1.5, fee:0 };
      setValues(t1); compute();
      logTest('T1 Long SL ok: ' + nearly(readNum('#outputs [data-key="slPrice"]'), 98));
      logTest('T1 Long TP ok: ' + nearly(readNum('#outputs [data-key="tpPrice"]'), 101.5));
      // Test 2: SHORT signs & prices
      const t2 = { equity:100, riskPct:0.01, margin:10, lev:10, dir:'short', entry:100, atr:1, slMult:2, tpMult:1.5, fee:0 };
      setValues(t2); compute();
      logTest('T2 Short SL ok: ' + nearly(readNum('#outputs [data-key="slPrice"]'), 102));
      logTest('T2 Short TP ok: ' + nearly(readNum('#outputs [data-key="tpPrice"]'), 98.5));
      // Test 3: Qty raw is integer (no comma)
      const btnQty = outputsWrap?.querySelector('[data-copy="qty"]');
      const rawQty = btnQty ? btnQty.getAttribute('data-raw') : '';
      logTest('T3 Qty raw is integer: ' + /^\d+$/.test(rawQty||''));
      // Test 4: RR logic (Long, TP 2x ATR vs SL 1x ATR => RR ~ 2)
      const t4 = { equity:100, riskPct:0.01, margin:10, lev:10, dir:'long', entry:100, atr:1, slMult:1, tpMult:2, fee:0 };
      setValues(t4); compute();
      const rrText = document.querySelector('#outputs [data-key="rr"]')?.textContent || '';
      const rrNum = parseFloat(rrText);
      logTest('T4 RR ≈ 2: ' + (Math.abs(rrNum - 2) < 1e-6));
      // Test 5: riskOk toggles with very small vs large riskPct
      const t5a = { equity:100, riskPct:0.0001, margin:10, lev:10, dir:'long', entry:100, atr:1, slMult:2, tpMult:1.5, fee:0 };
      setValues(t5a); compute();
      const riskOkSmall = document.querySelector('#outputs [data-key="riskOk"]')?.textContent;
      logTest('T5a riskOk small risk -> possibly NO: ' + (riskOkSmall === 'NO' || riskOkSmall === 'YES'));
      const t5b = { equity:100, riskPct:1, margin:10, lev:10, dir:'long', entry:100, atr:1, slMult:2, tpMult:1.5, fee:0 };
      setValues(t5b); compute();
      const riskOkLarge = document.querySelector('#outputs [data-key="riskOk"]')?.textContent;
      logTest('T5b riskOk large risk -> likely YES: ' + (riskOkLarge === 'YES' || riskOkLarge === 'NO'));
      // Test 6: data-raw for prices are plain (no comma)
      const rawSL = outputsWrap?.querySelector('[data-copy="slPrice"]')?.getAttribute('data-raw') || '';
      const rawTP = outputsWrap?.querySelector('[data-copy="tpPrice"]')?.getAttribute('data-raw') || '';
      logTest('T6 SL no comma: ' + (rawSL.indexOf(',') === -1));
      logTest('T6 TP no comma: ' + (rawTP.indexOf(',') === -1));
      // Restore saved values if any
      const saved = load(); if(saved) setValues(saved); compute();
    }

    /* ---------- init ---------- */
    function build(){
      if (!outputsWrap) return;
      outputsWrap.innerHTML='';
      const tplEl = document.querySelector('#rowTpl');
      if (!tplEl) { console.error('rowTpl missing'); return; }
      const tpl = tplEl.innerHTML;
      outSpec.forEach(([label,key,cls])=>{
        outputsWrap.insertAdjacentHTML('beforeend', tpl
          .replace('__LABEL__',label)
          .replaceAll('__KEY__',key)
          .replace('__CLASS__',cls)
          .replace('__VALUE__','-'));
      });
      outputsWrap.querySelectorAll('.copyBtn').forEach(btn=>{
        btn.addEventListener('click', async (e)=>{
          const raw = e.currentTarget ? (e.currentTarget.getAttribute('data-raw')||'') : '';
          const ok = await copyText(raw);
          if (ok && e.currentTarget && e.currentTarget.classList) {
            showToast('Copied');
            e.currentTarget.classList.add('copied');
            setTimeout(()=>{ if(e.currentTarget && e.currentTarget.classList) e.currentTarget.classList.remove('copied'); },900);
          }
        });
      });
      const closeBtn = $('#closeModal');
      if (closeBtn) closeBtn.addEventListener('click',()=> { const dlg=$('#copyModal'); if(dlg && typeof dlg.close==='function') dlg.close(); });
    }

    (function init(){
      build(); bind();
      const saved=load(); if(saved) setValues(saved);
      compute();
    })();
  </script>
</body>
</html>
