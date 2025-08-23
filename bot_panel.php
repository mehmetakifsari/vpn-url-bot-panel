<?php
declare(strict_types=1);
require __DIR__.'/auth.php'; require_login();

$BASE = __DIR__;
$URLS = $BASE . '/url.txt';
$MAP  = $BASE . '/vpn_map.json';
$SEL  = $BASE . '/selected_countries.json';
$MINF = $BASE . '/minutes.txt';
$COMPLETED = $BASE . '/completed_urls.txt';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act = $_POST['act'] ?? '';
  if ($act==='save') {
    file_put_contents($URLS, trim((string)($_POST['urls'] ?? '')), LOCK_EX);
    $mins = (float)($_POST['minutes'] ?? '3');
    file_put_contents($MINF, (string)$mins, LOCK_EX);
    $countries = (array)($_POST['countries'] ?? []);
    file_put_contents($SEL, json_encode(array_values($countries), JSON_UNESCAPED_UNICODE), LOCK_EX);
    header('Location: bot_panel.php?saved=1'); exit;
  }
}

$urls_txt = file_exists($URLS) ? (string)file_get_contents($URLS) : "";
$mins_txt = file_exists($MINF) ? trim((string)file_get_contents($MINF)) : "3";
$map = file_exists($MAP) ? (array)json_decode((string)file_get_contents($MAP), true) : [];
$sel = file_exists($SEL) ? json_decode((string)file_get_contents($SEL), true) : [];
if (!is_array($sel)) $sel = [];
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Tema: SADECE localStorage; kayıt yoksa 'light' ile başla -->
<script>
(function(){
  const KEY='proje8_theme';
  let saved='light';
  try { saved = localStorage.getItem(KEY) || 'light'; } catch(e){}
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>

<title>VPN Bot Paneli — proje8</title>
<link rel="stylesheet" href="/proje8/assets/ui.css">
<style>
  /* === Senin verdiğin panel-container düzeni (yapıyı koruyarak eklendi) === */
  :root {
    --panel-vline: var(--border);
  }
  .panel-container {
    display: grid;
    grid-template-columns: 1fr 2px 1fr;
    background: var(--card);
    border-radius: 15px;
    box-shadow: 0 1px 8px var(--shadow);
    padding: 24px;
    align-items: start;
    border:1px solid var(--border);
  }
  .panel-section {
    padding: 0 24px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
  }
  .vertical-line {
    width: 2px;
    background: var(--panel-vline);
    height: 100%;
    justify-self: center;
    align-self: stretch;
  }
  .panel-section h3{ margin:0 0 6px 0; }
  .panel-section p{ margin:0 0 10px 0; color:var(--muted); }

  /* Form elemanları (ui.css ile uyumlu) */
  .panel-section input[type="text"],
  .panel-section input[type="number"],
  .panel-section textarea{
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px;
    background: color-mix(in srgb, var(--card) 92%, var(--bg));
    color: var(--txt);
    font-size: 16px;
    box-sizing: border-box;
    resize: vertical;
  }

  .checkbox-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: 16px;
    align-items: flex-start;
  }
  .checkbox-list label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 400;
    cursor: pointer;
    white-space: nowrap;
  }
  .checkbox-list input[type="checkbox"]{ margin-left:0; accent-color:var(--secondary); }

  /* Süre kutusu ve kaydet */
  .süre-kutusu {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 24px;
    width: 100%;
  }
  .süre-kutusu label { margin-bottom:0; white-space:nowrap; color:var(--muted); }
  .süre-kutusu input { flex:1; max-width:180px; text-align:center; }

  .kaydet-btn {
    background: var(--secondary);
    color: var(--secondary-ink);
    border: none;
    border-radius: 8px;
    padding: 6px 22px;
    font-weight: bold;
    cursor: pointer;
    white-space: nowrap;
  }
  .kaydet-btn:hover{ filter:brightness(1.05) }

  .panel-info {
    margin-top: 8px;
    color: #888;
    font-size: 14px;
    text-align: left;
    width: 100%;
    margin-left: 2cm;
  }

  /* Log alanı isteklerine uygun: pencere sabit + içerik scroll */
  .card--vpnlog .log-window{
    height:360px;overflow-y:auto;border:1px solid var(--border);
    border-radius:12px;background:color-mix(in srgb,var(--card) 92%, var(--bg));
    padding:12px;
  }

  @media (max-width:920px){
    .panel-container{ grid-template-columns:1fr; gap:16px }
    .vertical-line{ display:none }
    .panel-info{ margin-left:0 }
  }
</style>
</head>
<body>
<div class="container">

  <!-- ÜST BAR -->
  <div class="header">
    <div class="left">
      <h1>VPN Botu — proje8</h1>
      <span class="badge">Kullanıcı: <?=htmlspecialchars($_SESSION['name'] ?? $_SESSION['role'] ?? 'admin')?></span>
      <?php if(isset($_GET['saved'])): ?><span class="badge">Ayarlar kaydedildi</span><?php endif; ?>
    </div>
    <div class="right">
      <!-- Buton METNİ: hedef mod; koyudayken 'Gündüz Mod', gündüzdeyken 'Karanlık Mod' -->
      <button class="theme-toggle" type="button" onclick="__toggleTheme(); __applyTargetLabel();">
        <span class="dot" aria-hidden="true"></span>
        <span data-theme-label>Karanlık Mod</span>
      </button>
      <a class="btn" href="logout.php">Çıkış</a>
    </div>
  </div>

  <!-- AYARLAR KARTI (senin verdiğin panel-container yapısı ile) -->
  <form method="post" class="card" style="padding:0;border:none;background:transparent;box-shadow:none;">
    <input type="hidden" name="act" value="save">

    <div class="panel-container">
      <div class="panel-section url-listesi">
        <h3>URL Listesi</h3>
        <p>Her satıra bir URL</p>
        <textarea name="urls" rows="12" placeholder="https://..."><?=htmlspecialchars($urls_txt)?></textarea>
      </div>

      <div class="vertical-line"></div>

      <div class="panel-section vpn-ulkeleri">
        <h3>VPN Ülkeleri</h3>
        <div class="checkbox-list">
          <label><input type="checkbox" id="selAll"> Tümünü Seç</label>
          <?php foreach ($map as $country=>$ovpn): ?>
            <?php $checked = in_array($country,$sel,true) ? 'checked' : ''; ?>
            <label>
              <!-- sınıf adlarını senin JS’inle uyumlu tuttum: .cbox -->
              <input type="checkbox" class="cbox" name="countries[]" value="<?=htmlspecialchars($country)?>" <?=$checked?>>
              <?=htmlspecialchars($country)?>
            </label>
          <?php endforeach; ?>
          <?php if(empty($map)): ?>
            <em style="color:var(--muted)">vpn_map.json boş görünüyor.</em>
          <?php endif; ?>
        </div>

        <div class="süre-kutusu">
          <label for="sure">Süre (dk):</label>
          <input id="sure" type="number" name="minutes" value="<?=htmlspecialchars($mins_txt ?: '3')?>" min="1">
          <button class="kaydet-btn" type="submit">Kaydet</button>
        </div>

        <div class="panel-info">Seçimleri kaydedin, sonra Başlat’a basın.</div>
      </div>
    </div>
  </form>

  <!-- KONTROL KARTI -->
  <div class="card" style="display:flex;gap:10px;align-items:center">
    <h3 style="margin:0;flex:1">Kontrol</h3>
    <button id="btnStart" class="btn" type="button">Başlat</button>
    <button id="btnStop" class="btn danger" type="button">Durdur</button>
    <span id="stat" class="badge">Durum: bilinmiyor</span>
  </div>

  <!-- VPN LOG (sabit pencere + içeriği scroll) -->
  <div class="card card--vpnlog">
    <h3 style="margin-top:0">VPN Log</h3>
    <div class="log-window">
      <pre id="logVpn" class="log">(yükleniyor)</pre>
    </div>
  </div>

  <!-- DİĞER LOG KARTLARI -->
  <div class="grid-2">
    <div class="card">
      <h3>Genel Log (bot.log)</h3>
      <pre id="logBot" class="log">(yükleniyor)</pre>
    </div>
    <div class="card">
      <h3>YouTube Log (yt_clicker.log)</h3>
      <pre id="logYt" class="log">(yükleniyor)</pre>
    </div>
  </div>

  <!-- TAMAMLANANLAR TABLOSU -->
  <div class="card">
    <h3>Şu Anki Ülke İçin Tamamlanan URL’ler</h3>
    <div class="scroll-x">
      <table class="table">
        <thead><tr><th>Ülke</th><th>URL</th></tr></thead>
        <tbody id="doneBody">
          <?php
          if (file_exists($COMPLETED)) {
            $lines = file($COMPLETED, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $ln) {
              $parts = explode("\t", $ln, 2);
              $cc = $parts[0] ?? ''; $uu = $parts[1] ?? '';
              echo '<tr><td>'.htmlspecialchars($cc).'</td><td>'.htmlspecialchars($uu).'</td></tr>';
            }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div> <!-- /.container -->

<!-- Tema JS (assets) -->
<script src="/proje8/assets/theme.js"></script>

<!-- Buton etiketi + TEMA Fallback (theme.js gelmezse dahi çalışsın) -->
<script>
(function(){
  const KEY='proje8_theme';
  function get(){ try{ return localStorage.getItem(KEY) || 'light'; }catch(e){ return 'light'; } }
  function set(v){ try{ localStorage.setItem(KEY, v); }catch(e){} }

  // Etiketin HEDEF modu göstermesi
  window.__applyTargetLabel = function(){
    var cur = document.documentElement.getAttribute('data-theme') || get();
    var el  = document.querySelector('[data-theme-label]');
    if(!el) return;
    el.textContent = (cur === 'dark') ? 'Gündüz Mod' : 'Karanlık Mod';
    el.setAttribute('aria-label', el.textContent);
    el.setAttribute('title', el.textContent);
  };

  // Fallback toggle: theme.js yoksa/bozuksa devreye girer
  if (typeof window.__toggleTheme !== 'function') {
    window.__toggleTheme = function(){
      var cur  = document.documentElement.getAttribute('data-theme') || get();
      var next = (cur === 'dark') ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      set(next);
      window.__applyTargetLabel();
    };
  }

  // İlk yüklemede etiketi ayarla
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.__applyTargetLabel);
  } else {
    window.__applyTargetLabel();
  }
})();
</script>

<!-- Panel JS -->
<script>
const $ = s => document.querySelector(s);

// Tümünü Seç
const chkAll = document.getElementById('selAll');
const boxes  = Array.from(document.querySelectorAll('.cbox'));

function syncMaster(){
  if (!chkAll) return;
  chkAll.checked = boxes.length>0 && boxes.every(cb => cb.checked);
}
chkAll?.addEventListener('change', e=>{
  boxes.forEach(cb => cb.checked = e.target.checked);
});
boxes.forEach(cb => cb.addEventListener('change', syncMaster));
syncMaster();

// (İstersen) Başlat/Durdur butonlarına AJAX bağlayacağımız alan
// document.getElementById('btnStart').onclick = ()=>{ /* fetch('run_bot.php?action=start') ... */ };
// document.getElementById('btnStop').onclick  = ()=>{ /* fetch('run_bot.php?action=stop') ... */ };
</script>
</body>
</html>
