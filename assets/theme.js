<script>
(function(){
  const KEY = 'proje8_theme';

  // Sadece localStorage; yoksa 'light'
  function getTheme(){ try{ return localStorage.getItem(KEY) || 'light'; }catch(e){ return 'light'; } }
  function setTheme(v){ try{ localStorage.setItem(KEY, v); }catch(e){} }

  window.__toggleTheme = function(){
    const cur  = document.documentElement.getAttribute('data-theme') || getTheme();
    const next = (cur === 'dark') ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    setTheme(next);
    window.__themeApply && window.__themeApply();
  };

  // Butonun HEDEF modu yazması
  window.__themeApply = function(){
    const cur = document.documentElement.getAttribute('data-theme') || getTheme();
    const el  = document.querySelector('[data-theme-label]');
    if(!el) return;
    el.textContent = (cur === 'dark') ? 'Gündüz Mod' : 'Karanlık Mod';
    el.setAttribute('aria-label', el.textContent);
    el.setAttribute('title', el.textContent);
  };

  // İlk yüklemede sadece buton etiketini düzelt
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', window.__themeApply);
  }else{
    window.__themeApply();
  }
})();
</script>
