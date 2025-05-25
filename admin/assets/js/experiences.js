function initExperiences() {
  // Bouton "En cours" dans chaque ligne : vide la date de fin
  $('#experience-list').on('click', '.clear-endDate', function () {
    $(this).siblings('input.endDate').val('');
  });

  // Fonction d'ajout d'un tag dans un container
  function addTag(container, text) {
    const tag = $('<span class="tag"></span>').text(text);
    const removeBtn = $('<span class="remove-tag">×</span>');
    removeBtn.on('click', function () {
      tag.remove();
    });
    tag.append(removeBtn);
    container.find('.tag-input').before(tag);
  }

  // Initialisation des tags existants (à partir de l'input hidden .tags dans la même ligne)
  $('.tag-input-container').each(function () {
    const container = $(this);
    // Récupère les tags depuis l'input caché associé (à adapter si besoin)
    const originalTags = container.closest('td').find('input.tags').val();
    if (originalTags) {
      originalTags.split(',').map(t => t.trim()).filter(Boolean).forEach(tag => addTag(container, tag));
    }
  });

  // Gestion de la saisie dans le champ tag-input
  $(document).on('keydown', '.tag-input', function (e) {
    const container = $(this).closest('.tag-input-container');
    const input = $(this);
    const value = input.val().trim();

    if (e.key === ',' || e.key === 'Enter') {
      e.preventDefault();
      if (value) {
        addTag(container, value);
        input.val('');
      }
    } else if (e.key === 'Backspace' && value === '') {
      // Supprime le dernier tag si champ vide et backspace pressé
      const tags = container.find('.tag');
      if (tags.length > 0) tags.last().remove();
    }
  });

  // Suppression d'un tag au clic sur la croix
  $(document).on('click', '.remove-tag', function () {
    $(this).parent('.tag').remove();
  });

  // Sauvegarde d'une expérience
  $('#experience-list').on('click', '.save', function () {
    const row = $(this).closest('tr');
    const id = row.data('id');
    const titre = row.find('.titre').val();
    const entreprise = row.find('.entreprise').val();
    const startDate = row.find('.startDate').val();
    const endDate = row.find('.endDate').val() || null;

    // Concatène les tags des bulles
    const tags = [];
    row.find('.tag').each(function () {
      tags.push($(this).text().replace('×', '').trim());
    });
    const tagsString = tags.join(',');

    $.post('actions/experiences/update.php', { id, titre, entreprise, startDate, endDate, tags: tagsString }, function (res) {
      $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
    }, 'json');
  });

  // Suppression d'une expérience
  $('#experience-list').on('click', '.delete', function () {
    const row = $(this).closest('tr');
    const id = row.data('id');
    if (confirm('Supprimer cette expérience ?')) {
      $.post('actions/experiences/delete.php', { id }, function (res) {
        if (res.success) row.remove();
        $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
      }, 'json');
    }
  });

  // Ajout d'une expérience
  $('#add-form').on('submit', function (e) {
    e.preventDefault();
    const titre = $('#experience-titre').val();
    const entreprise = $('#experience-entreprise').val();
    const startDate = $('#experience-startDate').val();
    const endDate = $('#experience-endDate').val() || null;
    const tags = $('#experience-tags').val();

    $.post('actions/experiences/add.php', { titre, entreprise, startDate, endDate, tags }, function (res) {
      if (res.success) {
        const newRow = `<tr data-id="${res.id}" class="sortable-item">
          <td class="drag-handle">⋮⋮</td>
          <td><input type="text" class="titre" value="${titre}"></td>
          <td><input type="text" class="entreprise" value="${entreprise}"></td>
          <td><input type="date" class="startDate" value="${startDate}"></td>
          <td>
            <input type="date" class="endDate" value="${endDate || ''}">
            <button type="button" class="clear-endDate" title="Mettre en cours">En cours</button>
          </td>
          <td>
            <div class="tag-input-container" contenteditable="false">
              <input type="text" class="tag-input" placeholder="php, javascript, ...">
              <input type="hidden" class="tags" value="${tags}">
            </div>
          </td>
          <td>
            <button class="open-editor" data-id="${res.id}">✏️ Éditer</button>
            <button class="save">💾</button>
            <button class="delete">🗑️</button>
          </td>
        </tr>`;
        $('#experience-list').append(newRow);
        $('#add-form')[0].reset();
      }
      $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
    }, 'json');
  });

  // Tri drag & drop
  new Sortable(document.getElementById('experience-list'), {
    animation: 150,
    handle: '.drag-handle',
    onEnd: function () {
      const ids = $('#experience-list tr').map(function () {
        return $(this).data('id');
      }).get();

      $.post('actions/experiences/update_order.php', { ids }, function (res) {
        $('#message').text(res.message).css({ display: 'block' });
      }, 'json');
    }
  });

  // Ouverture éditeur popup
  $('#experience-list').on('click', '.open-editor', function () {
    const id = $(this).data('id');
    window.open('editor/experience.php?id=' + id, 'Édition', 'width=800,height=600');
  });

  // Auto resize input text based on content length
  $('input[type="text"]').each(function () {
    this.style.width = (this.value.length + 2) + 'ch';
  }).on('input', function () {
    this.style.width = (this.value.length + 2) + 'ch';
  });

  // Format automatique des tags dans les inputs classiques (pas bulles)
  $('input.tags').on('input', function () {
    let val = $(this).val();
    val = val.replace(/,\s*/g, ', ');
    $(this).val(val);
  });
};
