<?php
require_once 'config.php';

$query = $pdo->query("SELECT * FROM videos ORDER BY video_id ASC");
$videos = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Video's</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 48px 24px;
            background: #f3f7ff;
            color: #1f2937;
            font-family: Arial, Helvetica, sans-serif;
        }

        main {
            width: min(1100px, 100%);
            margin: 0 auto;
        }

        h1 {
            margin: 0 0 28px;
            font-size: clamp(2rem, 4vw, 2.8rem);
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 24px;
        }

        .video-card {
            overflow: hidden;
            border: 1px solid #dfe7f5;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.08);
            color: inherit;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .video-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 34px rgba(37, 99, 235, 0.15);
        }

        .video-card img,
        .thumbnail-placeholder {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            background: #e8efff;
        }

        .thumbnail-placeholder {
            display: grid;
            place-items: center;
            color: #64748b;
        }

        .video-card h2 {
            margin: 0;
            padding: 18px;
            font-size: 1.15rem;
        }

        .empty-state {
            color: #64748b;
        }
    </style>
</head>
<body>
    <main>
        <h1>Video's</h1>
        <?php if (empty($videos)): ?>
            <p class="empty-state">Er zijn nog geen video's toegevoegd.</p>
        <?php else: ?>
            <div class="video-grid">
                <?php foreach ($videos as $video): ?>
                    <a class="video-card" href="detail.php?id=<?= (int)$video['video_id'] ?>">
                        <?php if (!empty($video['thumbnail'])): ?>
                            <img src="<?= htmlspecialchars($video['thumbnail'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <?php else: ?>
                            <span class="thumbnail-placeholder" aria-hidden="true">Geen thumbnail</span>
                        <?php endif; ?>
                        <h2><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>