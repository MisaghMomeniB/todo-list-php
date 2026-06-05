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
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, ...payload }),
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
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Utility: friendly date ──────────────────────────────────
function fmtDate(iso) {
    const d = new Date(iso.replace(' ', 'T'));
    return d.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
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

        const pending   = res.data.filter(t => t.status === 'pending');
        const completed = res.data.filter(t => t.status === 'completed');

        renderList('pending-list',   pending,   'pending');
        renderList('completed-list', completed, 'completed');

        document.getElementById('pending-count').textContent   = pending.length;
        document.getElementById('completed-count').textContent = completed.length;
    } catch (err) {
        toast(err.message || 'Network error.', 'error');
    }
}

function renderList(listId, tasks, statusClass) {
    const ul = document.getElementById(listId);
    ul.innerHTML = '';

    if (tasks.length === 0) {
        const icons = { pending: 'fa-inbox', completed: 'fa-flag-checkered' };
        const msgs  = { pending: 'No pending tasks — great job!', completed: 'No completed tasks yet.' };
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
    if (!title) { input.focus(); return; }

    const btn = document.getElementById('add-task-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner spin"></i> Adding…';

    try {
        const res = await api('add', { title });
        if (!res.success) throw new Error(res.message);
        input.value = '';
        await fetchTasks();
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
        const res = await api('complete', { id });
        if (!res.success) throw new Error(res.message);
        await fetchTasks();
        toast('Task completed! 🎉');
    } catch (err) {
        toast(err.message || 'Could not complete task.', 'error');
    }
}

// ── Delete task ─────────────────────────────────────────────
async function deleteTask(id) {
    if (!confirm('Delete this task?')) return;
    try {
        const res = await api('delete', { id });
        if (!res.success) throw new Error(res.message);
        await fetchTasks();
        toast('Task deleted.');
    } catch (err) {
        toast(err.message || 'Could not delete task.', 'error');
    }
}

// ── Inline edit: switch title div → input ──────────────────
function startEdit(btn, id) {
    const li       = btn.closest('.task-item');
    const titleDiv = li.querySelector('.task-title');
    const original = titleDiv.dataset.title;

    const input   = document.createElement('input');
    input.type      = 'text';
    input.className = 'edit-input';
    input.value     = original;
    input.maxLength = 255;
    titleDiv.replaceWith(input);
    input.focus();
    input.select();

    const actions = li.querySelector('.task-actions');
    actions.innerHTML = `
        <button class="icon-btn btn-save"   title="Save"   onclick="saveEdit(this, ${id})">
            <i class="fa-solid fa-check"></i>
        </button>
        <button class="icon-btn btn-cancel" title="Cancel" onclick="cancelEdit(this, ${id}, ${JSON.stringify(escHtml(original))})">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter')  saveEdit(actions.querySelector('.btn-save'), id);
        if (e.key === 'Escape') cancelEdit(actions.querySelector('.btn-cancel'), id, escHtml(original));
    });
}

// ── Inline edit: save ───────────────────────────────────────
async function saveEdit(btn, id) {
    const li    = btn.closest('.task-item');
    const input = li.querySelector('.edit-input');
    const title = input ? input.value.trim() : '';

    if (!title) { input && input.focus(); return; }

    try {
        const res = await api('edit', { id, title });
        if (!res.success) throw new Error(res.message);
        await fetchTasks();
        toast('Task updated.');
    } catch (err) {
        toast(err.message || 'Could not update task.', 'error');
    }
}

// ── Inline edit: cancel ─────────────────────────────────────
function cancelEdit(btn, id, originalTitle) {
    fetchTasks();
}

// ═══════════════════════════════════════════════════════════
// EVENT LISTENERS
// ═══════════════════════════════════════════════════════════

document.getElementById('add-task-btn')
    .addEventListener('click', addTask);

document.getElementById('new-task-input')
    .addEventListener('keydown', (e) => { if (e.key === 'Enter') addTask(); });

// ── Boot: load tasks on page ready ─────────────────────────
fetchTasks();