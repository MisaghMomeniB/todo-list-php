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

    <style>
        /* ═══════════════════════════════════════════
   CSS CUSTOM PROPERTIES (design tokens)
═══════════════════════════════════════════ */
        :root {
            --bg: #f5f3ef;
            /* warm off-white canvas            */
            --surface: #ffffff;
            --surface-alt: #faf9f7;
            --border: #e8e4de;
            --text: #1a1714;
            --text-muted: #8a847a;
            --accent: #d4622a;
            /* burnt-orange – the brand accent   */
            --accent-soft: #fbe9e0;
            --green: #2e7d60;
            --green-soft: #dff0e9;
            --red: #c0392b;
            --red-soft: #fdecea;
            --shadow-sm: 0 1px 3px rgba(26, 23, 20, .06);
            --shadow-md: 0 4px 16px rgba(26, 23, 20, .10);
            --shadow-lg: 0 8px 32px rgba(26, 23, 20, .13);
            --radius: 14px;
            --radius-sm: 8px;
            --transition: .22s cubic-bezier(.4, 0, .2, 1);
        }

        /* ── Reset & base ─────────────────────────── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 0 16px 80px;
            /* subtle dot-grid texture */
            background-image: radial-gradient(circle, #d6d0c8 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* ── Header ──────────────────────────────── */
        header {
            max-width: 660px;
            margin: 0 auto;
            padding: 52px 0 36px;
            display: flex;
            align-items: flex-end;
            gap: 16px;
        }

        .logo-mark {
            width: 46px;
            height: 46px;
            background: var(--accent);
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 22px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(212, 98, 42, .35);
        }

        header h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2rem, 5vw, 2.8rem);
            font-weight: 800;
            letter-spacing: -.03em;
            line-height: 1;
            color: var(--text);
        }

        header h1 span {
            color: var(--accent);
        }

        /* ── Main container ──────────────────────── */
        main {
            max-width: 660px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        /* ── Card (shared surface) ───────────────── */
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            padding: 28px;
        }

        /* ── Add-task form ───────────────────────── */
        .add-form {
            display: flex;
            gap: 10px;
        }

        .add-form input[type="text"] {
            flex: 1;
            padding: 13px 16px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 15px;
            color: var(--text);
            background: var(--surface-alt);
            outline: none;
            transition: border-color var(--transition), box-shadow var(--transition);
        }

        .add-form input[type="text"]::placeholder {
            color: var(--text-muted);
        }

        .add-form input[type="text"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 13px 20px;
            border: none;
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 3px 10px rgba(212, 98, 42, .30);
        }

        .btn-primary:hover {
            background: #bf5524;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(212, 98, 42, .38);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* ── Section headers ─────────────────────── */
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .section-header h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .badge {
            min-width: 22px;
            height: 22px;
            border-radius: 100px;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 700;
            padding: 0 7px;
        }

        .badge-pending {
            background: var(--accent-soft);
            color: var(--accent);
        }

        .badge-completed {
            background: var(--green-soft);
            color: var(--green);
        }

        /* ── Task list ───────────────────────────── */
        .task-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ── Task item ───────────────────────────── */
        .task-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            background: var(--surface-alt);
            transition: box-shadow var(--transition), transform var(--transition),
                border-color var(--transition), opacity var(--transition);
            animation: slideIn .3s ease both;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .task-item:hover {
            box-shadow: var(--shadow-md);
            border-color: #d8d2ca;
            transform: translateY(-1px);
        }

        /* Status dot */
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-dot.pending {
            background: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .status-dot.completed {
            background: var(--green);
            box-shadow: 0 0 0 3px var(--green-soft);
        }

        /* Title area */
        .task-title-wrap {
            flex: 1;
            min-width: 0;
        }

        .task-title {
            font-size: 15px;
            font-weight: 500;
            word-break: break-word;
            transition: color var(--transition);
        }

        .task-item.completed-item .task-title {
            color: var(--text-muted);
            text-decoration: line-through;
            text-decoration-color: #b0a99e;
        }

        .task-date {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Inline-edit input */
        .edit-input {
            width: 100%;
            border: 1.5px solid var(--accent);
            border-radius: 6px;
            padding: 5px 9px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        /* Action buttons */
        .task-actions {
            display: flex;
            gap: 4px;
            flex-shrink: 0;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 13px;
            transition: all var(--transition);
            background: transparent;
        }

        .icon-btn:hover {
            transform: scale(1.12);
        }

        .btn-complete {
            color: var(--green);
        }

        .btn-complete:hover {
            background: var(--green-soft);
        }

        .btn-edit {
            color: #5b7fc4;
        }

        .btn-edit:hover {
            background: #eaf0fb;
        }

        .btn-save {
            color: var(--green);
        }

        .btn-save:hover {
            background: var(--green-soft);
        }

        .btn-cancel {
            color: var(--text-muted);
        }

        .btn-cancel:hover {
            background: #f0ece6;
        }

        .btn-delete {
            color: var(--red);
        }

        .btn-delete:hover {
            background: var(--red-soft);
        }

        /* ── Empty state ─────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 32px 16px;
            color: var(--text-muted);
            font-size: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .empty-state i {
            font-size: 28px;
            opacity: .35;
        }

        /* ── Toast notification ──────────────────── */
        #toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 9999;
        }

        .toast {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            box-shadow: var(--shadow-lg);
            animation: toastIn .3s ease both, toastOut .4s ease 2.6s both;
            max-width: 320px;
        }

        .toast.success {
            background: var(--green);
        }

        .toast.error {
            background: var(--red);
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes toastOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(12px);
            }
        }

        /* ── Spinner overlay ─────────────────────── */
        .spin {
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── Responsive tweaks ───────────────────── */
        @media (max-width: 500px) {
            .add-form {
                flex-direction: column;
            }

            .btn-primary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
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


    <script>
        /* ═══════════════════════════════════════════════════════════
   TASKY  –  client-side AJAX controller
   -------------------------------------------------------
   AJAX FLOW:
   1. On page load → fetchTasks() → GET api.php?action=list
      → renders both #pending-list and #completed-list

   2. User interactions (add / edit / complete / delete) each:
      a. Call api(action, payload) – posts JSON to api.php
      b. On success → call fetchTasks() to re-render both lists
         (single source of truth; no manual DOM diffing needed)
      c. On error → show a toast notification

   3. Inline editing is pure DOM – no server round-trip until
      the user clicks Save, then edit() is called.
═══════════════════════════════════════════════════════════ */

        const API = 'api.php';

        // ── Utility: call the API ───────────────────────────────────
        async function api(action, payload = {}) {
            const res = await fetch(API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action,
                    ...payload
                }),
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        }

        // ── Utility: toast ──────────────────────────────────────────
        function toast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            el.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${escHtml(msg)}`;
            document.getElementById('toast-container').appendChild(el);
            setTimeout(() => el.remove(), 3200);
        }

        // ── Utility: escape HTML to prevent XSS ────────────────────
        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ── Utility: friendly date ──────────────────────────────────
        function fmtDate(iso) {
            const d = new Date(iso.replace(' ', 'T'));
            return d.toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        // ── Render a single <li> for a task ────────────────────────
        function buildTaskItem(task) {
            const li = document.createElement('li');
            li.className = `task-item${task.status === 'completed' ? ' completed-item' : ''}`;
            li.dataset.id = task.id;

            const isPending = task.status === 'pending';

            li.innerHTML = `
    <span class="status-dot ${task.status}" title="${escHtml(task.status)}"></span>
    <div class="task-title-wrap">
      <div class="task-title" data-title="${escHtml(task.title)}">${escHtml(task.title)}</div>
      <div class="task-date">${fmtDate(task.created_at)}</div>
    </div>
    <div class="task-actions">
      ${isPending ? `
        <button class="icon-btn btn-complete" title="Mark complete" onclick="completeTask(${task.id})">
          <i class="fa-solid fa-circle-check"></i>
        </button>
        <button class="icon-btn btn-edit" title="Edit task" onclick="startEdit(this, ${task.id})">
          <i class="fa-solid fa-pen"></i>
        </button>
      ` : ''}
      <button class="icon-btn btn-delete" title="Delete task" onclick="deleteTask(${task.id})">
        <i class="fa-solid fa-trash-can"></i>
      </button>
    </div>
  `;
            return li;
        }

        // ── Fetch all tasks and re-render both lists ────────────────
        async function fetchTasks() {
            try {
                const res = await api('list');
                if (!res.success) throw new Error(res.message || 'Could not load tasks.');

                const pending = res.data.filter(t => t.status === 'pending');
                const completed = res.data.filter(t => t.status === 'completed');

                renderList('pending-list', pending, 'pending');
                renderList('completed-list', completed, 'completed');

                document.getElementById('pending-count').textContent = pending.length;
                document.getElementById('completed-count').textContent = completed.length;

            } catch (err) {
                toast(err.message || 'Network error.', 'error');
            }
        }

        function renderList(listId, tasks, statusClass) {
            const ul = document.getElementById(listId);
            ul.innerHTML = '';
            if (tasks.length === 0) {
                const icons = {
                    pending: 'fa-inbox',
                    completed: 'fa-flag-checkered'
                };
                const msgs = {
                    pending: 'No pending tasks — great job!',
                    completed: 'No completed tasks yet.'
                };
                ul.innerHTML = `
      <li class="empty-state">
        <i class="fa-solid ${icons[statusClass]}"></i>
        ${msgs[statusClass]}
      </li>`;
                return;
            }
            tasks.forEach(task => ul.appendChild(buildTaskItem(task)));
        }

        // ── Add task ────────────────────────────────────────────────
        async function addTask() {
            const input = document.getElementById('new-task-input');
            const title = input.value.trim();
            if (!title) {
                input.focus();
                return;
            }

            const btn = document.getElementById('add-task-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner spin"></i> Adding…';

            try {
                const res = await api('add', {
                    title
                });
                if (!res.success) throw new Error(res.message);

                input.value = '';
                await fetchTasks(); // ← refresh both lists
                toast('Task added!');
            } catch (err) {
                toast(err.message || 'Could not add task.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-plus"></i> Add Task';
                input.focus();
            }
        }

        // ── Complete task ───────────────────────────────────────────
        async function completeTask(id) {
            try {
                const res = await api('complete', {
                    id
                });
                if (!res.success) throw new Error(res.message);
                await fetchTasks(); // ← refresh both lists
                toast('Task completed! 🎉');
            } catch (err) {
                toast(err.message || 'Could not complete task.', 'error');
            }
        }

        // ── Delete task ─────────────────────────────────────────────
        async function deleteTask(id) {
            if (!confirm('Delete this task?')) return;
            try {
                const res = await api('delete', {
                    id
                });
                if (!res.success) throw new Error(res.message);
                await fetchTasks(); // ← refresh both lists
                toast('Task deleted.');
            } catch (err) {
                toast(err.message || 'Could not delete task.', 'error');
            }
        }

        // ── Inline edit: switch title div → input ──────────────────
        function startEdit(btn, id) {
            const li = btn.closest('.task-item');
            const titleDiv = li.querySelector('.task-title');
            const original = titleDiv.dataset.title;

            // Replace title div with an input
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'edit-input';
            input.value = original;
            input.maxLength = 255;
            titleDiv.replaceWith(input);
            input.focus();
            input.select();

            // Replace action buttons temporarily
            const actions = li.querySelector('.task-actions');
            actions.innerHTML = `
    <button class="icon-btn btn-save"   title="Save"   onclick="saveEdit(this, ${id})">
      <i class="fa-solid fa-check"></i>
    </button>
    <button class="icon-btn btn-cancel" title="Cancel" onclick="cancelEdit(this, ${id}, ${JSON.stringify(escHtml(original))})">
      <i class="fa-solid fa-xmark"></i>
    </button>
  `;

            // Allow pressing Enter to save, Escape to cancel
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') saveEdit(actions.querySelector('.btn-save'), id);
                if (e.key === 'Escape') cancelEdit(actions.querySelector('.btn-cancel'), id, escHtml(original));
            });
        }

        // ── Inline edit: save ───────────────────────────────────────
        async function saveEdit(btn, id) {
            const li = btn.closest('.task-item');
            const input = li.querySelector('.edit-input');
            const title = input ? input.value.trim() : '';

            if (!title) {
                input && input.focus();
                return;
            }

            try {
                const res = await api('edit', {
                    id,
                    title
                });
                if (!res.success) throw new Error(res.message);
                await fetchTasks(); // ← refresh both lists
                toast('Task updated.');
            } catch (err) {
                toast(err.message || 'Could not update task.', 'error');
            }
        }

        // ── Inline edit: cancel (restore original title) ───────────
        function cancelEdit(btn, id, originalTitle) {
            // Fastest way: just re-render from server (ensures consistency)
            fetchTasks();
        }

        // ═══════════════════════════════════════════════════════════
        // EVENT LISTENERS
        // ═══════════════════════════════════════════════════════════

        // Add button click
        document.getElementById('add-task-btn')
            .addEventListener('click', addTask);

        // Enter key in the add-task input
        document.getElementById('new-task-input')
            .addEventListener('keydown', (e) => {
                if (e.key === 'Enter') addTask();
            });

        // ── Boot: load tasks on page ready ─────────────────────────
        fetchTasks();
    </script>

</body>

</html>