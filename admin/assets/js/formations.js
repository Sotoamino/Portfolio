function initFormations() {
  // Bouton "En cours" dans chaque ligne : vide la date de fin
  $('#formation-list').on('click', '.clear-endDate', function () {
    $(this).siblings('input.endDate').val('');
  });

  // Sauvegarde d'une formation
  $('#formation-list').on('click', '.save', function () {
    const row = $(this).closest('tr');
    const id = row.data('id');
    const titre = row.find('.titre').val();
    const name = row.find('.name').val();
    const startDate = row.find('.startDate').val();
    const endDate = row.find('.endDate').val() || null;
    const link = row.find('.formation-link').val();
    const description = row.find('.description').val();

    $.post('actions/formations/update.php', { id, titre, name, startDate, endDate, link, description }, function (res) {
      $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
    }, 'json');
  });

  // Suppression d'une formation
  $('#formation-list').on('click', '.delete', function () {
    const row = $(this).closest('tr');
    const id = row.data('id');
    if (confirm('Supprimer cette formation ?')) {
      $.post('actions/formations/delete.php', { id }, function (res) {
        if (res.success) row.remove();
        $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
      }, 'json');
    }
  });

  // Ajout d'une formation
  $('#add-form').on('submit', function (e) {
    e.preventDefault();
    const titre = $('#formation-titre').val();
    const name = $('#formation-name').val();
    const startDate = $('#formation-startDate').val();
    const endDate = $('#formation-endDate').val() || null;
    const link = $('#formation-link').val();
    const description = $('#formation-description').val();

    $.post('actions/formations/add.php', { titre, name, startDate, endDate, link, description }, function (res) {
      if (res.success) {
        const newRow = `<tr data-id="${res.id}" class="sortable-item">
          <td class="drag-handle">⋮⋮</td>
          <td><input type="text" class="titre" value="${titre}"></td>
          <td><input type="text" class="name" value="${name}"></td>
          <td><input type="date" class="startDate" value="${startDate}"></td>
          <td>
            <input type="date" class="endDate" value="${endDate || ''}">
            <button type="button" class="clear-endDate" title="Mettre en cours">En cours</button>
          </td>
          <td><input type="text" class="formation-link" value="${link}"></td>
          <td><textarea class="description">${description}</textarea></td>
          <td>
            <button class="open-editor" data-id="${res.id}">✏️ Éditer</button>
            <button class="save">💾</button>
            <button class="delete">🗑️</button>
          </td>
        </tr>`;
        $('#formation-list').append(newRow);
        $('#add-form')[0].reset();
      }
      $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
    }, 'json');
  });

  // Tri drag & drop
  new Sortable(document.getElementById('formation-list'), {
    animation: 150,
    handle: '.drag-handle',
    onEnd: function () {
      const ids = $('#formation-list tr').map(function () {
        return $(this).data('id');
      }).get();

      $.post('actions/formations/update_order.php', { ids }, function (res) {
        $('#message').text(res.message).css({ display: 'block' });
      }, 'json');
    }
  });


  // Auto resize input text based on content length
  $('input[type="text"]').each(function () {
    this.style.width = (this.value.length + 2) + 'ch';
  }).on('input', function () {
    this.style.width = (this.value.length + 2) + 'ch';
  });
};
