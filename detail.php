<?php
require_once 'config.php';

$videoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($videoId === false || $videoId === null) {
    http_response_code(404);
    $video = null;
} else {
    $query = $pdo->prepare("SELECT * FROM videos WHERE video_id = :video_id");
    $query->bindValue(':video_id', $videoId, PDO::PARAM_INT);
    $query->execute();
    $video = $query->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        http_response_code(404);
    }
}

$youtubeId = '';
if ($video && !empty($video['youtube_url']) && preg_match(
    '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~',
    $video['youtube_url'],
    $matches
)) {
    $youtubeId = $matches[1];
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $video ? htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') . ' | Video' : 'Video niet gevonden' ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px 20px;
            background: #f3f7ff;
            color: #1f2937;
            font-family: Arial, Helvetica, sans-serif;
        }

        main {
            width: min(900px, 100%);
            margin: 0 auto;
            padding: clamp(22px, 5vw, 42px);
            border: 1px solid #dfe7f5;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.08);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 24px;
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(1.8rem, 4vw, 2.5rem);
        }

        .date {
            margin: 0 0 24px;
            color: #64748b;
        }

        .video-frame {
            width: 100%;
            aspect-ratio: 16 / 9;
            border: 0;
            border-radius: 14px;
            background: #0f172a;
        }
    </style>
</head>
<body>
    <main>
        <a class="back-link" href="index.php">&larr; Terug naar video's</a>
        <?php if (!$video): ?>
            <h1>Video niet gevonden</h1>
            <p>Deze video bestaat niet (meer).</p>
        <?php else: ?>
            <h1><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="date">Datum: <?= htmlspecialchars($video['datum'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($youtubeId !== ''): ?>
                <iframe
                    class="video-frame"
                    src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($youtubeId, ENT_QUOTES, 'UTF-8') ?>?rel=0"
                    title="<?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?>"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen></iframe>
            <?php else: ?>
                <p>Geen geldige YouTube-link voor deze video.</p>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>