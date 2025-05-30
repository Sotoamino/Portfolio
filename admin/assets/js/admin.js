document.addEventListener('DOMContentLoaded', function () {
  // Code pour charger dynamiquement la page
  const links = document.querySelectorAll('[data-page]');
  const mainContent = document.getElementById('main-content');

  const pageScripts = {
    'competences': ['assets/js/competences.js'],
    'langues': ['assets/js/langues.js'],
    'experiences': ['assets/js/experiences.js'],
    'formations' : ['assets/js/formations.js'],
    'informations': ['assets/js/informations.js'],
    'dashboard': ['assets/js/dashboard.js'],
    'projets': ['assets/js/projets.js'],
    
    
    
    // Ajouter d'autres pages et leurs scripts ici
};

function isValidPage(page) {
  return Object.keys(pageScripts).includes(page);
}

function loadPage(page) {
  if (!isValidPage(page)) {
    mainContent.innerHTML = "<p>Page non autorisée.</p>";
    return;
  }
  fetch(`pages/${page}.php`)
    .then(response => {
      if (!response.ok) throw new Error("Erreur de chargement");
      return response.text();
    })
    .then(html => {
      mainContent.innerHTML = html;
      if (pageScripts[page]) {
        pageScripts[page].forEach(scriptSrc => {
        if (!document.querySelector(`script[src="${scriptSrc}"]`)) {
          const script = document.createElement('script');
          script.src = scriptSrc;
          script.type = 'text/javascript';
          script.onload = function () {
          console.log(`${scriptSrc} chargé !`);
          // Appeler la fonction d'initialisation après le chargement du script
          function capitalize(str) {
            if (typeof str !== 'string') return '';
              return str.charAt(0).toUpperCase() + str.slice(1);
            }
            if (typeof window[`init${capitalize(page)}`] === 'function') {
              window[`init${capitalize(page)}`]();
            }
          };
          document.body.appendChild(script);
        }
      });
    }
    history.pushState(null, '', `?page=${page}`);
  })
  .catch(error => {
    mainContent.innerHTML = "<p>Erreur lors du chargement.</p>";
    console.error(error);
  });
}

  // Fonction pour attacher les événements
  function attachEventHandlers() {
    // Remettre ici tout ton code JS (celui pour la gestion des compétences)
    $(function () {
      // ton code ici pour ajouter la gestion des compétences, etc.
    });
  }

  links.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const page = link.getAttribute('data-page');
      loadPage(page);
    });
  });

  // Chargement initial
  const urlParams = new URLSearchParams(window.location.search);
  const defaultPage = urlParams.get('page') || 'dashboard';
  loadPage(defaultPage);
});