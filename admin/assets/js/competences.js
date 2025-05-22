$(function () {
  console.log("✅ JS chargé!");

  // Mise à jour dynamique du niveau affiché dans la barre de progression
  $('#competence-list').on('input', '.niveau', function () {
    $(this).next('.niveau-val').text($(this).val());
  });

  // Sauvegarde d'une compétence (nom + niveau)
  $('#competence-list').on('click', '.save', function () {
    const row = $(this).closest('tr');
    const id = row.data('id');
    const nom = row.find('.nom').val();
    const niveau = row.find('.niveau').val();

    $.ajax({
      url: 'actions/competences/update.php',
      type: 'POST',
      data: { id, nom, niveau },
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          $('#message').text(res.message).css({ color: 'green', display: 'block' });
        } else {
          $('#message').text(res.message).css({ color: 'red', display: 'block' });
        }
      },
      error: function () {
        $('#message').text('Erreur lors de la mise à jour.').css({ color: 'red', display: 'block' });
      }
    });
  });

  // Suppression d'une compétence
  $('#competence-list').on('click', '.delete', function () {
    const row = $(this).closest('tr');
    const id = row.data('id');

    if (confirm('Êtes-vous sûr de vouloir supprimer cette compétence ?')) {
      $.ajax({
        url: 'actions/competences/delete.php',
        type: 'POST',
        data: { id },
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            row.remove(); // Retirer la ligne supprimée
          }
          $('#message').text(res.message).css({ color: 'green', display: 'block' });
        },
        error: function () {
          $('#message').text('Erreur lors de la suppression.').css({ color: 'red', display: 'block' });
        }
      });
    }
  });

  // Ajout d'une compétence
  $('#add-form').on('submit', function (e) {
    e.preventDefault();

    const nom = $('#competence-name').val();
    const niveau = $('#competence-level').val();

    $.ajax({
      url: 'actions/competences/add.php',
      type: 'POST',
      data: { nom, niveau },
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          const row = `<tr data-id="${res.id}">
            <td class="drag-handle">⋮⋮</td>
            <td><input type="text" class="nom" value="${nom}"></td>
            <td>
              <input type="range" class="niveau" value="${niveau}" min="0" max="100">
              <span class="niveau-val">${niveau}</span>%
            </td>
            <td>
              <button class="save">💾</button>
              <button class="delete">🗑️</button>
            </td>
          </tr>`;
          $('#competence-list').append(row); // Ajouter la nouvelle compétence à la liste
          $('#message').text(res.message).css({ color: 'green', display: 'block' });
          $('#add-form')[0].reset(); // Réinitialiser le formulaire d'ajout
        } else {
          $('#message').text(res.message).css({ color: 'red', display: 'block' });
        }
      },
      error: function () {
        $('#message').text('Erreur lors de l\'ajout de la compétence.').css({ color: 'red', display: 'block' });
      }
    });
  });

  // Mise à jour de l'ordre des compétences (drag & drop)
  new Sortable(document.getElementById('competence-list'), {
    animation: 150,
    handle: '.drag-handle', // Désigner l'icône comme la zone où on peut glisser
    onStart(evt) {
      console.log('Début du glisser...');
    },
    onEnd(evt) {
      const ids = $('#competence-list tr').map(function () {
        return $(this).data('id');
      }).get();

      $.ajax({
        url: 'actions/competences/update_order.php',
        type: 'POST',
        data: { ids },
        dataType: 'json',
        success: function (res) {
          $('#message').text(res.message);
        },
        error: function () {
          $('#message').text('Erreur lors de la mise à jour de l\'ordre.');
        }
      });
    }
  });
});
