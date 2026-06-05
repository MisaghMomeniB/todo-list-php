<?php
// index.php – Shell only; all data is loaded via AJAX from api.php
// No PHP database logic here – keeping concerns cleanly separated.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasky – Todo List</title>

    <!-- Google Fonts: Syne (display) + DM Sans (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">

    <!-- Font Awesome (icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">

    <!-- App styles -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header>
        <div class="logo-mark"><i class="fa-solid fa-list-check"></i></div>
        <h1>Task<span>y</span></h1>
    </header>

    <main>

        <!-- ── Add task card ───────────────────── -->
        <div class="card">
            <div class="add-form">
                <input type="text" id="new-task-input"
                    placeholder="What needs to be done?"
                    maxlength="255"
                    autocomplete="off">
                <button class="btn btn-primary" id="add-task-btn">
                    <i class="fa-solid fa-plus"></i> Add Task
                </button>
            </div>
        </div>

        <!-- ── Pending tasks ───────────────────── -->
        <div class="card" id="pending-card">
            <div class="section-header">
                <h2>Pending</h2>
                <span class="badge badge-pending" id="pending-count">0</span>
            </div>
            <ul class="task-list" id="pending-list">
                <!-- injected by JS -->
            </ul>
        </div>

        <!-- ── Completed tasks ─────────────────── -->
        <div class="card" id="completed-card">
            <div class="section-header">
                <h2>Completed</h2>
                <span class="badge badge-completed" id="completed-count">0</span>
            </div>
            <ul class="task-list" id="completed-list">
                <!-- injected by JS -->
            </ul>
        </div>

    </main>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <!-- App logic -->
    <script src="app.js"></script>

</body>

</html>