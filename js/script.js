let navbar = document.querySelector('.header .flex .nav-wrapper');
let profile = document.querySelector('.header .flex .profile');
let navPill = document.querySelector('.nav-pill');
const menuBtn = document.querySelector('#menu-btn');
const userBtn = document.querySelector('#user-btn');
const categoryMenuBtn = document.querySelector('#category-menu-btn');
const categoryMenu = document.querySelector('#category-menu');
const categoryFilterForm = document.querySelector('.category-filter-form');

if(categoryMenuBtn && categoryMenu){
   categoryMenuBtn.addEventListener('click', ()=>{
      const open = categoryMenu.hidden;
      categoryMenu.hidden = !open;
      categoryMenuBtn.setAttribute('aria-expanded', String(open));
   });

   document.addEventListener('click', (event)=>{
      if(!categoryMenu.contains(event.target) && !categoryMenuBtn.contains(event.target)){
         categoryMenu.hidden = true;
         categoryMenuBtn.setAttribute('aria-expanded', 'false');
      }
   });
}

if(categoryFilterForm){
   const brandSelect = categoryFilterForm.querySelector('#category-brand-select');
   const typeSelect = categoryFilterForm.querySelector('#category-type-select');

   if(brandSelect && typeSelect){
      brandSelect.addEventListener('change', ()=>{
         typeSelect.replaceChildren(new Option('All equipment', ''));
         const categories = ['Panels', 'Inverters', 'Batteries', 'Breakers', 'Mounting', 'Protection', 'DB Boxes', 'Cables', 'Accessories', 'Monitoring'];
         categories.forEach(category => typeSelect.add(new Option(category, category)));
         typeSelect.disabled = false;
      });
      typeSelect.replaceChildren(new Option('All equipment', ''));
      ['Panels', 'Inverters', 'Batteries', 'Breakers', 'Mounting', 'Protection', 'DB Boxes', 'Cables', 'Accessories', 'Monitoring'].forEach(category => typeSelect.add(new Option(category, category)));
      typeSelect.disabled = false;
   }
}

if(menuBtn){
   menuBtn.onclick = () =>{
      if(navbar){
         const isOpen = navbar.classList.toggle('open');
         menuBtn.setAttribute('aria-expanded', String(isOpen));
      }
      if(profile) profile.classList.remove('active');
   }
}

if(userBtn && profile){
   userBtn.addEventListener('click', ()=>{
      const isOpen = profile.classList.toggle('active');
      userBtn.setAttribute('aria-expanded', String(isOpen));
      if(navbar) navbar.classList.remove('open');
      if(navPill) navPill.classList.remove('open');
      if(menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
   });
}

window.onscroll = () =>{
   if(navbar) navbar.classList.remove('open');
   if(profile) profile.classList.remove('active');
   if(userBtn) userBtn.setAttribute('aria-expanded', 'false');
   if(menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
}

let mainImage = document.querySelector('.quick-view .box .row .image-container .main-image img');
let subImages = document.querySelectorAll('.quick-view .box .row .image-container .sub-image img');

if(mainImage && subImages && subImages.length){
   subImages.forEach(images =>{
      images.onclick = () =>{
         const src = images.getAttribute('src');
         if(src) mainImage.src = src;
      }
   });
}

// Mail modal handling on contact_us.php
const mailModal = document.getElementById('mailModal');
const mailModalClose = document.querySelector('#mailModal .modal-close');
if(mailModal){
   // if server rendered a success/error, fill and show
   const titleEl = document.getElementById('mailModalTitle');
   const bodyEl = document.getElementById('mailModalBody');
   // If page contains inline PHP messages, we leave them; but allow close
   mailModal.classList.remove('active');
   // hook close button
   const closeBtn = mailModal.querySelector('.modal-close');
   if(closeBtn) closeBtn.addEventListener('click', ()=> mailModal.classList.remove('active'));
}

// theme toggle (persist in localStorage)
const themeToggle = document.getElementById('theme-toggle');
function applyTheme(t){
   if(t === 'light'){
      document.body.classList.add('light-theme');
      if(themeToggle){
         const themeIcon = themeToggle.querySelector('i');
         if(themeIcon) themeIcon.className = 'fas fa-moon';
         const themeLabel = themeToggle.querySelector('span');
         if(themeLabel) themeLabel.textContent = 'Switch to dark theme';
      }
   }else{
      document.body.classList.remove('light-theme');
      if(themeToggle){
         const themeIcon = themeToggle.querySelector('i');
         if(themeIcon) themeIcon.className = 'fas fa-sun';
         const themeLabel = themeToggle.querySelector('span');
         if(themeLabel) themeLabel.textContent = 'Switch to light theme';
      }
   }
}
const themeStorageKey = 'solar_store_theme';
const savedTheme = localStorage.getItem(themeStorageKey) || 'dark';
applyTheme(savedTheme);
if(themeToggle){
   themeToggle.addEventListener('click', ()=>{
      const next = document.body.classList.contains('light-theme') ? 'dark' : 'light';
      applyTheme(next);
      localStorage.setItem(themeStorageKey, next);
   });
}

document.addEventListener('click', (event)=>{
   if(profile && !profile.contains(event.target) && !userBtn?.contains(event.target)){
      profile.classList.remove('active');
      if(userBtn) userBtn.setAttribute('aria-expanded', 'false');
   }
});

document.addEventListener('keydown', (event)=>{
   if(event.key === 'Escape'){
      if(profile) profile.classList.remove('active');
      if(userBtn) userBtn.setAttribute('aria-expanded', 'false');
      if(navbar) navbar.classList.remove('open');
      if(menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
      if(categoryMenu) categoryMenu.hidden = true;
      if(categoryMenuBtn) categoryMenuBtn.setAttribute('aria-expanded', 'false');
   }
});

// Solar request modal logic
// modal removed — contact requests are handled on contact_us.php
