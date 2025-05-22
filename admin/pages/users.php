<?php
require_once '../tools/sqlconnect.php';

$stmt = $pdo->query("SELECT id, username, email FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Gestion des utilisateurs</h2>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Nom d'utilisateur</th>
      <th>Email</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $user): ?>
    <tr>
      <td><?= htmlspecialchars($user['id']) ?></td>
      <td><?= htmlspecialchars($user['username']) ?></td>
      <td><?= htmlspecialchars($user['email']) ?></td>
      <td>
        <button data-id="<?= $user['id'] ?>" class="edit-user">✏️ Modifier</button>
        <button data-id="<?= $user['id'] ?>" class="delete-user">🗑️ Supprimer</button>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<button id="add-user">➕ Ajouter un utilisateur</button>

<script>
  $('#add-user').click(function () {
    $('#content').load('pages/user_add_form.php');
  });

  $('.edit-user').click(function () {
    const id = $(this).data('id');
    $('#content').load('pages/user_edit_form.php?id=' + id);
  });

  $('.delete-user').click(function () {
    if (confirm("Supprimer cet utilisateur ?")) {
      const id = $(this).data('id');
      $.post('actions/delete_user.php', { id }, function(response) {
        alert(response);
        $('a[data-page="users"]').click(); // Recharge la page
      });
    }
  });
</script>
