const uploadForm = document.getElementById('uploadForm');
const notification = document.getElementById('notification');

function showNotification(message, type = "success") {
  notification.className = type === "error" ? "error" : "success";
  notification.textContent = message;
  notification.classList.add(type);
  setTimeout(() => {
    notification.textContent = "";
    notification.className = "";
  }, 3000);
}

async function saveSettingToDatabase(settingName, value) {
  try {
    await fetch('../admin/actions/settings/settings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: settingName, value: value })
    });
  } catch (err) {
    showNotification('Erreur lors de la sauvegarde du paramètre.', 'error');
  }
}

function setupToggle(id, settingName) {
  const toggle = document.getElementById(id);
  if (toggle) {
    toggle.addEventListener("change", (e) => {
      const enabled = e.target.checked ? 1 : 0;
      showNotification(`${settingName} ${enabled ? "activé" : "désactivé"}`);
      saveSettingToDatabase(settingName, enabled);
    });
  }
}

// Nouvelle fonction pour select Particle config
function setupParticleConfigSelect() {
  const select = document.getElementById('particleConfigSelect');
  if (select) {
    select.addEventListener('change', (e) => {
      const value = e.target.value;
      showNotification(`Configuration particle_config changée en ${value}`);
      saveSettingToDatabase('particle_config', value);
    });
  }
}

uploadForm.addEventListener('submit', async (e) => {
  e.preventDefault();

  const fileInput = document.getElementById('cv');
  if (!fileInput.files.length) {
    showNotification('Veuillez sélectionner un fichier PDF.', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('cv', fileInput.files[0]);

  try {
    const response = await fetch('../admin/actions/cv/upload_cv.php', {
      method: 'POST',
      body: formData
    });
    const result = await response.json();

    if (result.success) {
      showNotification(result.message, 'success');
      uploadForm.reset();
    } else {
      showNotification(result.message, 'error');
    }
  } catch (err) {
    showNotification('Erreur réseau ou serveur.', 'error');
  }
});

setupToggle("maintenanceToggle", "maintenance_status");
setupToggle("githubToggle", "github_status");
setupToggle("linkedinToggle", "linkedin_status");
setupParticleConfigSelect();

function initDashboard() {
  fetch('/admin/update/check_update.php')
  .then(response => response.json())
  .then(data => {
    const updateContainer = document.getElementById('update-gh');
    if (!updateContainer) return;

    if (data.update_available) {
      updateContainer.innerHTML = `
          <h2>Mise à jour disponible</h2>
          <p>Version actuelle : <strong>${data.current}</strong><br>
             Dernière version : <strong>${data.latest}</strong></p>
          <form method="POST" action="/admin/update/update.js">
            <button type="submit">🔄 Mettre à jour maintenant</button>
          </form>
      `;
    } else {
      updateContainer.innerHTML = `
          <h2>Aucune mise à jour disponible</h2>
          <p>Version actuelle : <strong>${data.current}</strong></p>
          <form method="POST" action="/admin/update/rollback.php" style="margin-top: 1rem;">
            <button type="submit">↩️ Restaurer la dernière sauvegarde</button>
          </form>
      `;
    }
  })
  .catch(error => {
    console.error('Erreur lors de la vérification des mises à jour :', error);
    const updateContainer = document.getElementById('update-gh');
    if (updateContainer) {
      updateContainer.innerHTML = `
          <h2>Erreur</h2>
          <p>Impossible de vérifier les mises à jour.</p>
          <form method="POST" action="/admin/update/rollback.php" style="margin-top: 1rem;">
            <button type="submit">↩️ Restaurer la dernière sauvegarde</button>
          </form>
      `;
    }
  });
};
