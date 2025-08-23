(function(){
  const KEY = 'proje8_theme';

  // Sadece localStorage; kayıt yoksa 'light'
  function getTheme(){ try{ return localStorage.getItem(KEY) || 'light'; }catch(e){ return 'light'; } }
  function setTheme(v){ try{ localStorage.setItem(KEY, v); }catch(e){} }

  // Tema değiştir (buton)
  window.__toggleTheme = function(){
    const cur  = document.documentElement.getAttribute('data-theme') || getTheme();
    const next = (cur === 'dark') ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    setTheme(next);
    window.__themeApply && window.__themeApply();
  };

  // Butonun HEDEF modu yazması (mevcut dark ise "Gündüz Mod", aksi "Karanlık Mod")
  window.__themeApply = function(){
    const cur = document.documentElement.getAttribute('data-theme') || getTheme();
    const el  = document.querySelector('[data-theme-label]');
    if(!el) return;
    const txt = (cur === 'dark') ? 'Gündüz Mod' : 'Karanlık Mod';
    el.textContent = txt;
    el.setAttribute('aria-label', txt);
    el.setAttribute('title', txt);
  };

  // İlk yüklemede buton etiketini düzelt
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', window.__themeApply);
  }else{
    window.__themeApply();
  }
})();
