<?php
// ============================================================
// api.php  –  JSON REST-style endpoint for task CRUD
//
// Accepted POST body (JSON):
//   { "action": "add",      "title": "…" }
//   { "action": "edit",     "id": 1, "title": "…" }
//   { "action": "delete",   "id": 1 }
//   { "action": "complete", "id": 1 }
//   { "action": "list" }        ← also accepted as GET
//
// Every response is:  { "success": true|false, "data": … }
// ============================================================

declare(strict_types=1);

// ── Bootstrap ────────────────────────────────────────────────
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');
// Allow the page to call this file from the same origin
header('X-Content-Type-Options: nosniff');

// ── Read request ─────────────────────────────────────────────
$raw    = file_get_contents('php://input');
$body   = json_decode($raw, true) ?? [];

// Also support plain POST fields and GET for "list"
$action = trim((string)($body['action'] ?? $_POST['action'] ?? $_GET['action'] ?? ''));

// ── Helper: send JSON and exit ────────────────────────────────
function respond(bool $success, mixed $data = null, string $message = ''): never
{
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

// ── Helper: sanitise a task title ────────────────────────────
function sanitiseTitle(mixed $raw): string
{
    $title = trim((string)$raw);
    // Strip all HTML tags, collapse whitespace
    $title = strip_tags($title);
    $title = preg_replace('/\s+/', ' ', $title);
    return $title;
}

// ── Route ─────────────────────────────────────────────────────
try {
    $pdo = getDbConnection();

    switch ($action) {

        // ── LIST ──────────────────────────────────────────────
        case 'list': {
                $stmt = $pdo->query(
                    "SELECT id, title, status, created_at
                   FROM tasks
                  ORDER BY status ASC, created_at DESC"
                    //  'completed' > 'pending' alphabetically → pending first
                );
                $rows = $stmt->fetchAll();
                respond(true, $rows);
            }

            // ── ADD ───────────────────────────────────────────────
        case 'add': {
                $title = sanitiseTitle($body['title'] ?? $_POST['title'] ?? '');
                if ($title === '') {
                    respond(false, null, 'Task title cannot be empty.');
                }
                if (mb_strlen($title) > 255) {
                    respond(false, null, 'Task title is too long (max 255 characters).');
                }

                $stmt = $pdo->prepare(
                    "INSERT INTO tasks (title) VALUES (:title)"
                );
                $stmt->execute([':title' => $title]);
                $newId = (int)$pdo->lastInsertId();

                // Return the freshly created row
                $row = $pdo->prepare(
                    "SELECT id, title, status, created_at FROM tasks WHERE id = :id"
                );
                $row->execute([':id' => $newId]);
                respond(true, $row->fetch(), 'Task added.');
            }

            // ── EDIT ──────────────────────────────────────────────
        case 'edit': {
                $id    = filter_var($body['id'] ?? $_POST['id'] ?? 0, FILTER_VALIDATE_INT);
                $title = sanitiseTitle($body['title'] ?? $_POST['title'] ?? '');

                if (!$id || $id <= 0) {
                    respond(false, null, 'Invalid task ID.');
                }
                if ($title === '') {
                    respond(false, null, 'Task title cannot be empty.');
                }
                if (mb_strlen($title) > 255) {
                    respond(false, null, 'Task title is too long (max 255 characters).');
                }

                $stmt = $pdo->prepare(
                    "UPDATE tasks SET title = :title WHERE id = :id"
                );
                $stmt->execute([':title' => $title, ':id' => $id]);

                if ($stmt->rowCount() === 0) {
                    respond(false, null, 'Task not found.');
                }
                respond(true, ['id' => $id, 'title' => $title], 'Task updated.');
            }

            // ── COMPLETE ──────────────────────────────────────────
        case 'complete': {
                $id = filter_var($body['id'] ?? $_POST['id'] ?? 0, FILTER_VALIDATE_INT);

                if (!$id || $id <= 0) {
                    respond(false, null, 'Invalid task ID.');
                }

                $stmt = $pdo->prepare(
                    "UPDATE tasks SET status = 'completed' WHERE id = :id AND status = 'pending'"
                );
                $stmt->execute([':id' => $id]);

                if ($stmt->rowCount() === 0) {
                    respond(false, null, 'Task not found or already completed.');
                }
                respond(true, ['id' => $id], 'Task marked as completed.');
            }

            // ── DELETE ────────────────────────────────────────────
        case 'delete': {
                $id = filter_var($body['id'] ?? $_POST['id'] ?? 0, FILTER_VALIDATE_INT);

                if (!$id || $id <= 0) {
                    respond(false, null, 'Invalid task ID.');
                }

                $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
                $stmt->execute([':id' => $id]);

                if ($stmt->rowCount() === 0) {
                    respond(false, null, 'Task not found.');
                }
                respond(true, ['id' => $id], 'Task deleted.');
            }

            // ── Unknown action ─────────────────────────────────────
        default:
            respond(false, null, 'Unknown action: ' . htmlspecialchars($action));
    }
} catch (PDOException $e) {
    // Never expose raw DB errors to clients in production
    error_log('DB error: ' . $e->getMessage());
    respond(false, null, 'A database error occurred. Please try again.');
} catch (Throwable $e) {
    error_log('App error: ' . $e->getMessage());
    respond(false, null, 'An unexpected error occurred.');
}
