<?php
session_start();

require_once 'config.php';


$admin = $pdo->query("SELECT * FROM `admin` LIMIT 1")->fetch();

$ADMIN_USER = $admin["username"];
$ADMIN_PASS = $admin["password"];

if (isset($_GET["logout"]) && $_GET["logout"] === "true") {
    session_destroy();
    header("Location: login.php");
    exit;
}

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("Location: admin.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION["logged_in"] = true;
        $_SESSION["username"] = $username;

        
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inloggen | Admin</title>
    <style>
        :root {
            color-scheme: light;
            --text: #1f2937;
            --muted: #64748b;
            --border: #dfe7f5;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            display: grid;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            place-items: center;
            background:
                radial-gradient(ellipse at 15% 10%, rgba(147, 197, 253, 0.35), transparent 38%),
                linear-gradient(135deg, #eef4ff 0%, #f9fbff 100%);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .login-card {
            width: min(100%, 430px);
            padding: clamp(28px, 6vw, 42px);
            border: 1px solid rgba(223, 231, 245, 0.9);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 24px 60px rgba(37, 99, 235, 0.12);
        }

        .eyebrow {
            margin: 0 0 10px;
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.8rem, 6vw, 2.2rem);
            letter-spacing: -0.04em;
        }

        .intro {
            margin: 10px 0 28px;
            color: var(--muted);
            line-height: 1.6;
        }

        form {
            display: grid;
            gap: 18px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        label {
            font-size: 0.95rem;
            font-weight: 700;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            min-height: 48px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            color: var(--text);
            font: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .error {
            margin: 0 0 20px;
            padding: 12px 14px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .button {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border: 0;
            border-radius: 12px;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .button:hover {
            transform: translateY(-1px);
        }

        .button-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.2);
            color: #fff;
        }

        .button-primary:hover {
            box-shadow: 0 12px 22px rgba(37, 99, 235, 0.26);
        }

        .button-secondary {
            width: 100%;
            margin-top: 12px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
        }

        .button-secondary:hover {
            background: #f8fbff;
        }
    </style>
</head>
<body>
    <main class="login-card">
        <p class="eyebrow">Beheeromgeving</p>
        <h1>Welkom terug</h1>
        <p class="intro">Log in om video's te beheren.</p>

        <?php if (!empty($error)): ?>
            <p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="field">
                <label for="username">Gebruikersnaam</label>
                <input type="text" id="username" name="username" autocomplete="username" required>
            </div>
            <div class="field">
                <label for="password">Wachtwoord</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>
            <button class="button button-primary" type="submit">Inloggen</button>
        </form>

        <a class="button button-secondary" href="index.php">Terug naar video's</a>
    </main>
</body>
</html>