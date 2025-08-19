<?php
declare(strict_types=1);
require __DIR__.'/auth.php'; require_login();
$BASE=__DIR__;
$URLS=$BASE.'/url.txt'; $MAP=$BASE.'/vpn_map.json';
$SEL=$BASE.'/selected_countries.json'; $MINF=$BASE.'/minutes.txt';
$COMPLETED=$BASE.'/completed_urls.txt';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(($_POST['act']??'')==='save'){
    file_put_contents($URLS,trim($_POST['urls']??''),LOCK_EX);
    file_put_contents($MINF,(string)((float)($_POST['minutes']??'3')),LOCK_EX);
    $countries=$_POST['countries']??[];
    file_put_contents($SEL,json_encode(array_values($countries),JSON_UNESCAPED_UNICODE),LOCK_EX);
    header('Location: bot_panel.php'); exit;
  }
}
$urls_txt=file_exists($URLS)?file_get_contents($URLS):"";
$mins_txt=file_exists($MINF)?trim(file_get_contents($MINF)):"3";
$map=file_exists($MAP)?json_decode(file_get_contents($MAP),true):[];
$sel=file_exists($SEL)?json_decode(file_get_contents($SEL),true):[];
if(!is_array($sel))$sel=[];
?>
<!doctype html>
<html lang="tr" data-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>VPN Bot Paneli — proje8</title>
<link rel="stylesheet" href="/proje8/assets/ui.css">
</head>
<body>
<div class="container">
  <div class="header">
    <div class="left">
      <h1>VPN Botu — proje8</h1>
      <span class="badge">Kullanıcı: <?=htmlspecialchars($_SESSION['name']??'')?></span>
    </div>
    <div class="right">
      <button class="theme-toggle" onclick="__toggleTheme();__themeApply();">
        <span class="dot"></span><span data-theme-label>Koyu Mod</span>
      </button>
      <a class="btn" href="logout.php">Çıkış</a>
    </div>
  </div>

  <form method="post" class="card">
    <input type="hidden" name="act" value="save">
    <div class="grid-2">
      <div>
        <h3>URL Listesi</h3>
        <textarea name="urls" rows="12"><?=htmlspecialchars($urls_txt)?></textarea>
      </div>
      <div>
        <h3>VPN Ülkeleri</h3>
        <label><input type="checkbox" id="selAll"> Tümünü Seç</label>
        <div class="grid-4">
          <?php foreach($map as $c=>$o): $checked=in_array($c,$sel)?'checked':''; ?>
            <label><input type="checkbox" class="cbox" name="countries[]" value="<?=htmlspecialchars($c)?>" <?=$checked?>> <?=htmlspecialchars($c)?></label>
          <?php endforeach;?>
        </div>
        <div class="form-row" style="margin-top:12px">
          <label for="mins">Süre (dk):</label>
          <input id="mins" type="text" name="minutes" value="<?=htmlspecialchars($mins_txt)?>" style="max-width:120px">
          <button type="submit" class="btn secondary">Kaydet</button>
        </div>
      </div>
    </div>
  </form>

  <div class="card" style="display:flex;gap:10px;align-items:center">
    <button id="btnStart" class="btn">Başlat</button>
    <button id="btnStop" class="btn danger">Durdur</button>
    <span id="stat" class="badge">Durum: bilinmiyor</span>
  </div>

  <div class="grid-2">
    <div class="card"><h3>Genel Log</h3><pre id="logBot" class="log">(yükleniyor)</pre></div>
    <div class="card"><h3>YouTube Log</h3><pre id="logYt" class="log">(yükleniyor)</pre></div>
  </div>

  <div class="card">
    <h3>Tamamlanan URL’ler</h3>
    <div class="scroll-x">
      <table class="table"><thead><tr><th>Ülke</th><th>URL</th></tr></thead><tbody id="doneBody"></tbody></table>
    </div>
  </div>

  <div class="card"><h3>VPN Log</h3><pre id="logVpn" class="log">(yükleniyor)</pre></div>
</div>

<script src="/proje8/assets/theme.js"></script>
<script>window.__themeApply();</script>
<script>
const $=s=>document.querySelector(s);
$('#selAll')?.addEventListener('change',e=>{
  document.querySelectorAll('.cbox').forEach(cb=>cb.checked=e.target.checked);
});
async function call(a){const f=new FormData();f.append('action',a);return fetch('run_bot.php',{method:'POST',body:f}).then(r=>r.json());}
async function getTail(w){return fetch('run_bot.php?action=tail&which='+w).then(r=>r.json());}
async function refresh(){
  const st=await fetch('run_bot.php?action=status').then(r=>r.json());
  if(st.ok) $('#stat').textContent='Durum: '+(st.running?'ÇALIŞIYOR':'DURDU')+' | VPN: '+st.vpn;
  $('#logBot').textContent=(await getTail('bot')).log||''; $('#logYt').textContent=(await getTail('yt')).log||''; $('#logVpn').textContent=(await getTail('vpn')).log||'';
  fetch('completed_urls.txt?ts='+Date.now()).then(r=>r.text()).then(t=>{
    const body=$('#doneBody');body.innerHTML='';t.trim().split('\n').filter(Boolean).forEach(l=>{
      const idx=l.indexOf('\t');if(idx<0)return;const c=l.slice(0,idx),u=l.slice(idx+1);
      const tr=document.createElement('tr');tr.innerHTML=`<td>${c}</td><td>${u}</td>`;body.appendChild(tr);
    });
  });
}
$('#btnStart').onclick=async()=>{const r=await call('start');alert(r.msg);refresh();};
$('#btnStop').onclick=async()=>{const r=await call('stop');alert(r.msg);refresh();};
setInterval(refresh,5000);refresh();
</script>
</body></html>
