<?php
// update.php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mise à jour en cours</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    .progress-container {
      margin-top: 20px;
      width: 100%;
      background-color: #f3f3f3;
      border-radius: 10px;
      overflow: hidden;
    }
    .progress-bar {
      height: 30px;
      width: 0%;
      background-color: #4caf50;
      text-align: center;
      color: white;
      line-height: 30px;
      transition: width 0.3s;
    }
  </style>
</head>
<body>
  <h2>⏳ Mise à jour en cours...</h2>
  <div class="progress-container">
    <div class="progress-bar" id="progress-bar">0%</div>
  </div>
  <div id="log"></div>
  <script>
    const progressBar = document.getElementById("progress-bar");
    const log = document.getElementById("log");

    const evtSource = new EventSource("progress.php");

    evtSource.onmessage = function(event) {
      const data = JSON.parse(event.data);
      progressBar.style.width = data.percent + "%";
      progressBar.textContent = data.percent + "%";
      if (data.message) {
        log.innerHTML += "<p>" + data.message + "</p>";
      }
      if (data.done) {
        evtSource.close();
        log.innerHTML += "<p>✅ Mise à jour terminée ! <a href='../index.php'>Retour</a></p>";
      }
    };

    evtSource.onerror = function() {
      log.innerHTML += "<p>❌ Erreur pendant la mise à jour.</p>";
      evtSource.close();
    };
  </script>
</body>
</html>
