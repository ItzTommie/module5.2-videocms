<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once "config.php";

if (isset($_GET['delete'])) {
    $video_id = $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM videos WHERE video_id = :video_id");
    $stmt->bindParam(':video_id', $video_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "Video deleted successfully.";
    } else {
        echo "Error deleting video.";
    }
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"] ?? "";
    $datum = $_POST["datum"] ?? "";
    $youtube_url = $_POST["youtube_url"] ?? "";
    $thumbnail = "";

    if (isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]["error"] === UPLOAD_ERR_OK) {
        $allowedTypes = ["image/jpeg", "image/png", "image/webp", "image/gif"];
        $fileType = mime_content_type($_FILES["thumbnail"]["tmp_name"]);

        if (!in_array($fileType, $allowedTypes, true)) {
            $message = "Alleen JPG, PNG, WEBP of GIF bestanden zijn toegestaan.";
        } else {
            $uploadDir = __DIR__ . "/uploads";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = strtolower(pathinfo($_FILES["thumbnail"]["name"], PATHINFO_EXTENSION));
            $fileName = "thumb_" . uniqid() . "." . $extension;
            $targetPath = $uploadDir . "/" . $fileName;

            if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $targetPath)) {
                $thumbnail = "uploads/" . $fileName;
            } else {
                $message = "Upload van thumbnail is mislukt.";
            }
        }
    } else {
        $message = "Upload een thumbnail-afbeelding.";
    }

    if (empty($message) && $title !== "" && $datum !== "" && $youtube_url !== "") {
        $stmt = $pdo->prepare("INSERT INTO videos (title, datum, youtube_url, thumbnail) VALUES (:title, :datum, :youtube_url, :thumbnail)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':datum', $datum);
        $stmt->bindParam(':youtube_url', $youtube_url);
        $stmt->bindParam(':thumbnail', $thumbnail);

        if ($stmt->execute()) {
            $message = "Video added successfully.";
        } else {
            $message = "Error adding video.";
        }
    }
}

$query = $pdo->query("SELECT * FROM videos ORDER BY video_id ASC");
$videos = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        :root {
            --bg: #f3f7ff;
            --panel: #ffffff;
            --panel-alt: #eef4ff;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --text: #1f2937;
            --muted: #64748b;
            --border: #dfe7f5;
            --success: #15803d;
            --success-bg: #ecfdf5;
            --danger: #dc2626;
            --danger-bg: #fef2f2;
            --shadow: 0 10px 30px rgba(37, 99, 235, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #eef4ff 0%, #f9fbff 100%);
            color: var(--text);
        }

        .page {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 24px 28px;
            margin-bottom: 28px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(223, 231, 245, 0.8);
            border-radius: 18px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(8px);
        }

        .topbar h1 {
            margin: 0;
            font-size: clamp(1.8rem, 3vw, 2.6rem);
        }

        .user-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--panel-alt);
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 0.95rem;
        }

        .btn,
        .btn-danger,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 12px;
            padding: 10px 18px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .btn:hover,
        .btn-danger:hover,
        .btn-secondary:hover {
            transform: translateY(-1px);
        }

        .btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.2);
        }

        .btn-secondary {
            background: white;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, var(--danger) 100%);
            color: white;
            box-shadow: 0 8px 18px rgba(220, 38, 38, 0.18);
        }

        .dashboard {
            display: grid;
            grid-template-columns: minmax(300px, 420px) minmax(320px, 1fr);
            gap: 26px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .panel-header {
            padding: 22px 24px 18px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%);
        }

        .panel-header h2 {
            margin: 0;
            font-size: 1.4rem;
        }

        .panel-body {
            padding: 24px;
        }

        .alert {
            margin: 0 0 18px;
            padding: 12px 14px;
            border-radius: 12px;
            font-weight: 600;
            background: var(--success-bg);
            color: var(--success);
            border: 1px solid rgba(21, 128, 61, 0.15);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        label {
            font-weight: 700;
            color: var(--text);
        }

        input[type="text"],
        input[type="date"],
        input[type="url"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="url"]:focus {
            outline: none;
            border-color: rgba(37, 99, 235, 0.7);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .video-thumbnail {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            margin-bottom: 14px;
            border-radius: 12px;
            background: var(--panel-alt);
        }

        .video-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .video-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px 18px 16px;
        }

        .video-card h3 {
            margin: 0 0 10px;
            font-size: 1.2rem;
        }

        .meta {
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 14px;
        }

        .meta a {
            color: var(--primary);
            text-decoration: none;
            word-break: break-word;
        }

        .video-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .empty-state {
            color: var(--muted);
            padding: 20px 0 8px;
            font-style: italic;
        }

        @media (max-width: 800px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="topbar">
            <h1>Admin Dashboard</h1>
            <div class="user-row">
                <span class="badge">Ingelogd als: <?= htmlspecialchars($_SESSION["username"] ?? "admin", ENT_QUOTES, "UTF-8") ?></span>
                <a class="btn-secondary" href="login.php?logout=true">Logout</a>
            </div>
        </header>

        <main class="dashboard">
            <section class="panel">
                <div class="panel-header">
                    <h2>Nieuwe Video Toevoegen</h2>
                </div>
                <div class="panel-body">
                    <?php if (!empty($message)): ?>
                        <p class="alert"><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="field">
                            <label for="title">Titel</label>
                            <input type="text" id="title" name="title" required>
                        </div>

                        <div class="field">
                            <label for="datum">Datum</label>
                            <input type="date" id="datum" name="datum" required>
                        </div>

                        <div class="field">
                            <label for="youtube_url">YouTube URL</label>
                            <input type="url" id="youtube_url" name="youtube_url" required>
                        </div>

                        <div class="field">
                            <label for="thumbnail">Thumbnail uploaden</label>
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required>
                        </div>

                        <button class="btn" type="submit">Toevoegen</button>
                    </form>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <h2>Video's Beheren</h2>
                </div>
                <div class="panel-body">
                    <?php if (empty($videos)): ?>
                        <div class="empty-state">Nog geen video's toegevoegd.</div>
                    <?php else: ?>
                        <div class="video-list">
                            <?php foreach ($videos as $video): ?>
                                <article class="video-card">
                                    <?php if (!empty($video["thumbnail"])): ?>
                                        <img class="video-thumbnail" src="<?= htmlspecialchars($video["thumbnail"], ENT_QUOTES, "UTF-8") ?>" alt="<?= htmlspecialchars($video["title"], ENT_QUOTES, "UTF-8") ?>">
                                    <?php endif; ?>
                                    <h3><?= htmlspecialchars($video["title"]) ?></h3>
                                    <div class="meta">
                                        <div>Datum: <?= htmlspecialchars($video["datum"]) ?></div>
                                        <div>Link: <a href="<?= htmlspecialchars($video["youtube_url"]) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($video["youtube_url"]) ?></a></div>
                                    </div>
                                    <div class="video-actions">
                                        <a class="btn-secondary" href="<?= htmlspecialchars($video["youtube_url"]) ?>" target="_blank" rel="noopener noreferrer">Bekijken</a>
                                        <a class="btn-danger" href="admin.php?delete=<?= (int)$video["video_id"] ?>" onclick="return confirm('Weet je zeker dat je deze video wilt verwijderen?')">Verwijderen</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <div style="margin-top: 24px; text-align: right;">
            <button class="btn-secondary" onClick="window.location.href='index.php'">Back to Home</button>
        </div>
    </div>
</body>
</html>
