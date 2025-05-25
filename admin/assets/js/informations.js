// ===== POPUP réseaux sociaux =====
function initInformations() {
  const addSocialBtn = document.getElementById('add-social');
  const popup = document.getElementById('add-social-popup');
  const closePopupBtn = document.getElementById('close-popup');
  const socialsContainer = document.getElementById('socials-container');

  if (!addSocialBtn) console.error('add-social button introuvable');
  if (!popup) console.error('popup add-social-popup introuvable');
  if (!closePopupBtn) console.error('close-popup button introuvable');
  if (!socialsContainer) console.error('socials-container introuvable');

  addSocialBtn?.addEventListener('click', () => {
    console.log('Bouton Ajouter un réseau cliqué');
    popup.style.display = 'block';
  });

  closePopupBtn?.addEventListener('click', () => {
    console.log('Bouton Annuler popup cliqué');
    popup.style.display = 'none';
  });

  // Choix réseau social dans popup
  const socialChoiceButtons = document.querySelectorAll('.social-choice');
  console.log('Boutons choix réseaux trouvés:', socialChoiceButtons.length);

  socialChoiceButtons.forEach(button => {
    button.addEventListener('click', () => {
      const network = button.getAttribute('data-network');
      console.log('Réseau choisi:', network);

      if ([...socialsContainer.children].some(div => div.getAttribute('data-network') === network)) {
        console.warn('Ce réseau est déjà ajouté');
        alert('Ce réseau est déjà ajouté.');
        return;
      }

      const iconClass = button.querySelector('i').className;
      const div = document.createElement('div');
      div.className = 'social-input';
      div.setAttribute('data-network', network);
      div.innerHTML = `
        <i class="${iconClass}"></i>
        <input type="text" name="${network}" placeholder="https://${network}.com/...">
        <button type="button" class="remove-social">×</button>
      `;

      socialsContainer.appendChild(div);
      popup.style.display = 'none';
    });
  });

  // Suppression d'un réseau via délégation
  socialsContainer?.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-social')) {
      console.log('Suppression du réseau:', e.target.parentElement.getAttribute('data-network'));
      e.target.parentElement.remove();
    }
  });

  // ===== Gestion des mots-clés =====
  const keywordInput = document.getElementById('keyword-input');
  const keywordsContainer = document.getElementById('keywords-container');

  if (!keywordInput) console.error('keyword-input introuvable');
  if (!keywordsContainer) console.error('keywords-container introuvable');

  document.querySelectorAll('.keyword-tag .remove-keyword').forEach(btn => {
    btn.addEventListener('click', () => {
      const tag = btn.parentElement;
      console.log('Suppression mot-clé existant:', tag.textContent.trim());
      tag.remove();
    });
  });

  keywordInput?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const value = keywordInput.value.trim();
      if (!value) {
        console.warn('Mot-clé vide, ignoré');
        return;
      }

      const existingKeywords = [...keywordsContainer.querySelectorAll('.keyword-tag')].map(el => el.textContent.trim().slice(0, -1));
      if (existingKeywords.includes(value)) {
        console.warn('Mot-clé déjà existant:', value);
        alert('Ce mot-clé existe déjà.');
        keywordInput.value = '';
        return;
      }

      const span = document.createElement('span');
      span.className = 'keyword-tag';
      span.textContent = value;

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'remove-keyword';
      btn.textContent = '×';

      btn.addEventListener('click', () => {
        console.log('Suppression mot-clé:', value);
        span.remove();
      });

      span.appendChild(btn);
      keywordsContainer.appendChild(span);
      console.log('Mot-clé ajouté:', value);

      keywordInput.value = '';
    }
  });

  // ===== Formulaire de sauvegarde =====
  const form = document.getElementById('settings-form');
  if (!form) {
    console.error('Formulaire settings-form introuvable');
  } else {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      console.log('Formulaire soumis');

      const payload = {
        first_name: document.getElementById('prenom')?.value.trim(),
        last_name: document.getElementById('nom')?.value.trim(),
        email: document.getElementById('email')?.value.trim(),
        phone: document.getElementById('phone')?.value.trim(),
        location: document.getElementById('localisation')?.value.trim(),
        keywords: Array.from(document.querySelectorAll('#keywords-container .keyword-tag'))
          .map(span => span.textContent.replace('×', '').trim())
      };

      document.querySelectorAll('#socials-container input').forEach(input => {
        const name = input.name;
        if (name) {
          payload[name] = input.value.trim();
        }
      });

      fetch('actions/settings/save.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
      })
      .then(response => {
        return response.text().then(text => {
          console.log('Réponse brute du serveur:', text);
          try {
            return JSON.parse(text);
          } catch (e) {
            console.error('Erreur de parsing JSON:', e);
            throw e;
          }
        });
      })
      .then(data => {
        console.log('Succès:', data);
        alert('Enregistrement réussi !');
      })
      .catch(error => {
        console.error('Erreur lors de l\'enregistrement', error);
        alert('Erreur lors de l\'enregistrement');
      });
    });
  }
};

// ===== Fonctions d’extraction des liens =====
window.extractLinkedin = function(input) {
  if (input.name === 'linkedin') {
    const val = input.value.trim();
    input.value = val.replace(/.*linkedin\.com\/in\/([^\/\s]+).*/, 'https://linkedin.com/in/$1');
    console.log('Lien LinkedIn corrigé:', input.value);
  }
}

window.extractGithub = function(input) {
  if (input.name === 'github') {
    const val = input.value.trim();
    input.value = val.replace(/.*github\.com\/([^\/\s]+).*/, 'https://github.com/$1');
    console.log('Lien Github corrigé:', input.value);
  }
}
