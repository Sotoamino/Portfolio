// Particules
fetch('assets/js/particle-config.php')
  .then(response => response.json())
  .then(data => {
    if(data.file) {
      particlesJS.load('particles-js', `assets/particles/${data.file}`);
    } else {
      console.error('Fichier de configuration de particules introuvable');
    }
  })
  .catch(err => console.error('Erreur chargement config particules:', err));
// Changement de texte automatique


let i = 0;
let j = 0;
let isDeleting = false;
const speed = 100;
const pause = 2000;
const textElement = document.getElementById('changing-text');

function typeEffect() {
    const currentPhrase = phrases[i];
    if (isDeleting) {
        textElement.innerText = currentPhrase.substring(0, j--);
    } else {
        textElement.innerText = currentPhrase.substring(0, j++);
    }

    if (!isDeleting && j === currentPhrase.length + 1) {
        isDeleting = true;
        setTimeout(typeEffect, pause);
        return;
    }

    if (isDeleting && j === 0) {
        isDeleting = false;
        i = (i + 1) % phrases.length;
    }

    setTimeout(typeEffect, isDeleting ? speed / 2 : speed);
}

typeEffect();

// Progress bars animées
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            document.querySelectorAll('.progress div').forEach(bar => {
                bar.style.width = bar.getAttribute('data-percent');
            });
        }
    });
});
observer.observe(document.querySelector('#skills'));

// Envoi Ajax du formulaire
const form = document.getElementById('contactForm');
if (form) {
    form.addEventListener('submit', e => {
        e.preventDefault();
        fetch('contact.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                document.getElementById('message').style.display = 'block';
                form.reset();
            } else {
                alert('Erreur lors de l\'envoi du message.');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur réseau.');
        });
    });
}

	
	
	//navbar
	
  const toggle = document.getElementById("menu-toggle");
  const navLinks = document.getElementById("nav-links");

  toggle.addEventListener("click", () => {
    toggle.classList.toggle("open");
    navLinks.classList.toggle("open");
  });


document.addEventListener("DOMContentLoaded", function() {
    // Sélectionner tous les éléments avec la classe 'accordion'
    const accordions = document.querySelectorAll('.accordion');

    accordions.forEach(accordion => {
        // Ajouter un événement de clic à chaque bouton d'accordion
        accordion.addEventListener('click', function() {
            // Trouver le panel associé
            const panel = this.nextElementSibling;

            // Si le panel est déjà visible, on le cache
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                // Sinon, on l'affiche
                panel.style.display = "block";
            }

            // Pour animer l'ouverture et la fermeture
            panel.classList.toggle('active');
        });
    });
});

if (username) {
  fetch(`https://api.github.com/users/${username}`)
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById("github-profile");
      if(!container) return;
      if (data.message === "Not Found") {
        container.innerHTML = "Utilisateur GitHub introuvable.";
        return;
      }
      container.innerHTML = `
        <img src="${data.avatar_url}" alt="${data.login}">
        <div class="github-details">
          <h2>${data.name || data.login}</h2>
          <p>${data.bio || "Aucune bio disponible."}</p>
          <div class="github-stats">
            <p><strong>Repos publics :</strong> ${data.public_repos}</p>
            <p><strong>Followers :</strong> ${data.followers}</p>
          </div>
          <a class="github-profile-link" href="${data.html_url}" target="_blank">Voir le profil</a>
        </div>
      `;
    })
    .catch(error => {
      document.getElementById("github-profile").innerHTML = "Impossible de charger le profil GitHub.";
      console.error("Erreur : ", error);
    });
}
