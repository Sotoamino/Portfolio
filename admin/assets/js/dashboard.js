const form = document.getElementById('uploadForm');
  const notification = document.getElementById('notification');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    notification.textContent = '';
    notification.className = '';

    const fileInput = document.getElementById('cv');
    if (!fileInput.files.length) {
      notification.textContent = 'Veuillez sélectionner un fichier PDF.';
      notification.className = 'error';
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
        notification.textContent = result.message;
        notification.className = 'success';
        form.reset();
      } else {
        notification.textContent = result.message;
        notification.className = 'error';
      }
    } catch (err) {
      notification.textContent = 'Erreur réseau ou serveur.';
      notification.className = 'error';
    }
  });