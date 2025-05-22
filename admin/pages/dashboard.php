<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
  <style>
    #notification { margin-bottom: 20px; padding: 10px; border-radius: 5px; }
    #notification.success { background-color: #d4edda; color: #155724; }
    #notification.error { background-color: #f8d7da; color: #721c24; }
  </style>
</head>
<body>

<h1>Dashboard</h1>

<div id="notification"></div>

<form id="uploadForm">
  <label for="cv">Importer un CV (PDF uniquement) :</label><br />
  <input type="file" name="cv" id="cv" accept="application/pdf" required />
  <button type="submit">Envoyer</button>
</form>

<p>Le tuto d'utilisation est disponible
  <a href="https://github.com/Sotoamino/Portfolio/blob/main/README.md">ici</a>.
</p>
<p>Le site est en cours de développement, il est donc possible que certaines fonctionnalités soient incomplètes ou non fonctionnelles.</p>

</body>
</html>
