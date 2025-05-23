<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../../tools/sqlconnect.php';

$id = $_GET['id'] ?? null;
$mode = $_GET['mode'] ?? 'edit';

if (!$id) {
    die('ID manquant');
}

$stmt = $pdo->prepare("SELECT content FROM experiences WHERE id = ?");
$stmt->execute([$id]);
$experience = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$experience) {
    die('Expérience introuvable');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title><?= $mode === 'preview' ? 'Prévisuel' : 'Éditeur' ?> | Expérience</title>
    <?php if ($mode === 'preview'): ?>
    <link rel="stylesheet" href="../../../assets/css/experiences.css">
    <?php endif; ?>
    <style>
        img { max-width: 100%; height: auto; }
        .ui-resizable-handle {
            background: #4285f4;
            border: 1px solid #fff;
            width: 10px;
            height: 10px;
            z-index: 90;
        }
        #loader {
            display: none;
            font-style: italic;
            color: #666;
            margin-top: 10px;
        }
        #message {
            margin-top: 10px;
        }
    </style>
    <?php if ($mode === 'edit'): ?>

<!-- jQuery & jQuery UI -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-resizable-dom@0.35.0/dist/jquery-resizable.min.js"></script>


<!-- Trumbowyg -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>

<!-- Plugins -->
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/upload/trumbowyg.upload.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/resizimg/trumbowyg.resizimg.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/colors/trumbowyg.colors.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/fontsize/trumbowyg.fontsize.min.js"></script>
    <?php endif; ?>
  
</head>
<body>

<h2><?= $mode === 'preview' ? 'Prévisuel' : 'Édition du contenu de l\'expérience' ?></h2>

<a href="experience.php?id=<?= $id ?>&mode=<?= $mode === 'preview' ? 'edit' : 'preview' ?>" style="float:right;text-decoration:none;color:black">
    <strong><?= $mode === 'preview' ? '‹ Éditeur' : 'Prévisuel ›' ?></strong>
</a>

<?php if ($mode === 'preview'): ?>
    <div style="border:1px solid black;padding:10px;">
        <?= $experience['content'] ?>
    </div>
<?php else: ?>
<form id="edit-form">
    <label for="editor">Contenu détaillé</label>
    <br/><br/><br/>
    <textarea id="editor"><?= htmlspecialchars($experience['content']) ?></textarea>
    <button type="submit">Enregistrer</button>
</form>

<div id="message"></div>



    <script>
    $(document).ready(function() {
        $('#editor').trumbowyg({
            btns: [
                ['viewHTML'],
                ['undo', 'redo'],
                ['formatting'],
                ['strong', 'em', 'del'],
                ['superscript', 'subscript'],
                ['link'],
                ['upload'],
                ['fontsize'],
                ['foreColor', 'backColor'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['fullscreen']
            ],
            plugins: {
                upload: {
                    serverPath: '/admin/editor/upload.php',
                    fileFieldName: 'file',
                    urlPropertyName: 'file.url'
                },
                resizimg: {
                    minSize: 64,
                    step: 16
                },
                autogrow: true
            }
        });

        function saveContent(callback) {
            const contenu = $('#editor').trumbowyg('html');
            $('#loader').show();
            $('#message').text('');

            $.ajax({
                url: 'save_content.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    id: <?= json_encode($id) ?>,
                    content: contenu
                }),
                success: function(resp) {
                    $('#loader').hide();
                    $('#message').text('Contenu enregistré !').css('color', 'green');
                    if (callback) callback(true);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#loader').hide();
                    let errorMessage = 'Erreur lors de la sauvegarde';
                    try {
                        const response = JSON.parse(jqXHR.responseText);
                        if (response.message) errorMessage += ' : ' + response.message;
                    } catch (e) {
                        errorMessage += ' : ' + errorThrown;
                    }
                    $('#message').text(errorMessage).css('color', 'red');
                    if (callback) callback(false);
                }
            });
        }

        $('#edit-form').on('submit', function(e) {
            e.preventDefault();
            saveContent();
        });

        $('a[href*="mode=preview"]').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            saveContent(function(success) {
                if (success) window.location.href = url;
            });
        });
    });
    </script>
<?php endif; ?>

</body>
</html>