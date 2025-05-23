$(function () {
  // Sauvegarde d'une expérience
  $('#projet-list').on('click', '.save', function () {
    const row = $(this).closest('tr');
    const id = row.data('id');
    const titre = row.find('.titre').val();
    const description = row.find('.description').val();
    const link = row.find('.link').val();

    $.post('actions/projets/update.php', { id, titre, description, link }, function (res) {
      $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
    }, 'json');
  });

  // Suppression d'une expérience
  $('#projet-list').on('click', '.delete', function () {
    const row = $(this).closest('tr');
    const id = row.data('id');
    if (confirm('Supprimer cette expérience ?')) {
      $.post('actions/projets/delete.php', { id }, function (res) {
        if (res.success) row.remove();
        $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
      }, 'json');
    }
  });

  // Ajout d'une expérience
  $('#add-form').on('submit', function (e) {
    e.preventDefault();
    const titre = $('#projet-titre').val();
    const description = $('#projet-description').val();
    const link = $('#projet-link').val();

    $.post('actions/projets/add.php', { titre, description, link }, function (res) {
      if (res.success) {
        const newRow = `<tr data-id="${res.id}" class="sortable-item">
          <td class="drag-handle">⋮⋮</td>
          <td><input type="text" class="titre" value="${titre}"></td>
          <td><input type="text" class="description" value="${description}"></td>*
          <td><input type="text" class="description" value="${link}"></td>
          <td>
            <button class="save">💾</button>
            <button class="delete">🗑️</button>
          </td>
        </tr>`;
        $('#projet-list').append(newRow);
        $('#add-form')[0].reset();
      }
      $('#message').text(res.message).css({ color: res.success ? 'green' : 'red', display: 'block' });
    }, 'json');
  });

  // Tri drag & drop
  new Sortable(document.getElementById('projet-list'), {
    animation: 150,
    handle: '.drag-handle',
    onEnd: function () {
      const ids = $('#projet-list tr').map(function () {
        return $(this).data('id');
      }).get();

      $.post('actions/projets/update_order.php', { ids }, function (res) {
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

  // Format automatique des tags dans les inputs classiques (pas bulles)
  $('input.tags').on('input', function () {
    let val = $(this).val();
    val = val.replace(/,\s*/g, ', ');
    $(this).val(val);
  });
});
