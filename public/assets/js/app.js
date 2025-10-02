'use strict';
(function(){
  const qs = (s,el=document)=>el.querySelector(s);
  const qsa = (s,el=document)=>Array.from(el.querySelectorAll(s));

  // Quick view microinteraction
  qsa('.product-card .quick-view').forEach(btn=>{
    btn.addEventListener('click', e => {
      const title = e.currentTarget.getAttribute('data-product');
      openModal(`<h3>${title}</h3><p>Premium Ethiopian product. More details coming soon.</p><button class="btn btn-primary" data-close>Close</button>`);
    });
  });

  function openModal(html){
    let m = document.createElement('div');
    m.className = 'modal-backdrop';
    m.innerHTML = `<div class="modal">${html}</div>`;
    document.body.appendChild(m);
    m.addEventListener('click', (e)=>{
      if(e.target===m || e.target.hasAttribute('data-close')){
        m.remove();
      }
    })
  }

  // Toast utility
  window.toast = function(message){
    const t = document.createElement('div');
    t.className = 'toast';
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(()=>t.classList.add('show'), 10);
    setTimeout(()=>{t.classList.remove('show'); t.remove();}, 3000);
  }
})();
