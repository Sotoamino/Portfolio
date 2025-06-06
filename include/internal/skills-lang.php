<?php

function progress_bar($part, $datas, $pointer) {
    echo '<section id="'.$pointer.'">';
    echo "<h2>" . htmlspecialchars($part) . "</h2>";
    echo '<div class="card">';

    foreach ($datas as $data) {
        $nom = htmlspecialchars($data['nom']);
        $niveau = htmlspecialchars($data['niveau']);
        echo '
        <div class="progress">
            <div class="progress-bar" data-percent="' . $niveau . '%">
                <span class="skill-name">' . $nom . '</span>
            </div>
        </div>';
    }
    echo '</div>'; // fermeture de .card
    echo '</section>';
}

function blocks($mots, $id = null, $title) {
    $containerId = $id ?: 'container_' . uniqid();
    ?>
    <section id="<?php $id ?>">
    <h2><?php htmlspecialchars($title) ?></h2>
    <div class="card">
    <div id="<?= $containerId ?>" class="wordContainer"></div></div></section>
    <style>
        .wordContainer {
            padding: 2rem;
            max-width: 1100px;
            margin: auto;
            position: relative;
            overflow: hidden;
        }
        .mot {
            position: absolute;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: bold;
            white-space: nowrap;
            color: white;
            background-color: var(--color-tag-text, #444);
            transition: transform 0.2s ease;
        }
        .mot:hover {
            transform: scale(1.1);
        }
    </style>

    <script>
        (function() {
            const mots = <?= json_encode($mots) ?>;
            const container = document.getElementById("<?= $containerId ?>");
            const usedPositions = [];

            function isOverlapping(x, y, width, height) {
                return usedPositions.some(pos => {
                    return !(x + width < pos.x || x > pos.x + pos.width || y + height < pos.y || y > pos.y + pos.height);
                });
            }

            mots.forEach(mot => {
                const span = document.createElement("span");
                span.className = `mot`;
                span.textContent = mot.nom;
                container.appendChild(span);

                const fontSize = Math.floor(Math.random() * 10) + 16;
                span.style.fontSize = `${fontSize}px`;

                const { offsetWidth: w, offsetHeight: h } = span;

                let x, y, attempts = 0;
                do {
                    x = Math.floor(Math.random() * (container.clientWidth - w));
                    y = Math.floor(Math.random() * (container.clientHeight - h));
                    attempts++;
                } while (isOverlapping(x, y, w, h) && attempts < 1000);

                usedPositions.push({ x, y, width: w, height: h });
                span.style.left = `${x}px`;
                span.style.top = `${y}px`;
            });
        })();
    </script>
    <?php
}

