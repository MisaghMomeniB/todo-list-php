<div align="center">

<img src="https://img.shields.io/badge/-%E2%9C%93%20Tasky-D4622A?style=for-the-badge&logoColor=white" alt="Tasky" height="48">

# Tasky — PHP Todo List Application

**A clean, modern, single-page todo app built with PHP, MySQL & Vanilla JS**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES2022-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-2E7D60?style=flat-square)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.0.0-D4622A?style=flat-square)]()

<br>

> Add tasks · Edit inline · Mark complete · Delete · All without page reload.

<br>

</div>

---

## ✨ Features

| | Feature | Description |
|---|---|---|
| ➕ | **Add Task** | Type a title and press `Enter` or click **Add Task** |
| ✏️ | **Inline Edit** | Click the pen icon — title becomes an input field; save with `Enter`, cancel with `Esc` |
| ✅ | **Mark Complete** | One click moves the task from Pending → Completed list |
| 🗑️ | **Delete Task** | Confirmation-guarded hard delete |
| 🔔 | **Toast Feedback** | Non-blocking success/error notifications on every action |
| 📱 | **Responsive** | Mobile-first layout, stacks cleanly on narrow screens |
| 🔒 | **Secure** | Prepared statements, input sanitisation, XSS-safe output |

---

## 🗂️ Project Structure

```
tasky/
├── index.php          # Main UI — HTML, CSS design system, JS AJAX controller
├── api.php            # JSON API endpoint — CRUD routing via PDO
├── setup.sql          # One-time DB + table creation script
└── config/
    └── db.php         # PDO connection factory (getDbConnection)
```

---

## ⚙️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.x |
| Database | MySQL 8.x |
| Frontend | HTML5 + CSS3 (custom properties, animations) |
| JavaScript | Vanilla ES2022 — Fetch API |
| Fonts | [Syne](https://fonts.google.com/specimen/Syne) + [DM Sans](https://fonts.google.com/specimen/DM+Sans) via Google Fonts |
| Icons | [Font Awesome 6.5](https://fontawesome.com/) via CDN |

---

## 🗄️ Database Schema

**Database:** `todo_list` &nbsp;|&nbsp; **Table:** `tasks`

```sql
CREATE TABLE `tasks` (
    `id`         INT UNSIGNED                    NOT NULL AUTO_INCREMENT,
    `title`      VARCHAR(255)                    NOT NULL,
    `status`     ENUM('pending', 'completed')    NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP                       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🚀 Local Setup

### Prerequisites

- PHP 8.x with `pdo_mysql` extension enabled
- MySQL 8.x running locally
- Git (optional)

---

### 1 · Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/tasky.git
cd tasky
```

---

### 2 · Configure the database connection

Open `config/db.php` and set your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'todo_list');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');   // ← change this
define('DB_CHARSET', 'utf8mb4');
```

> ⚠️ **Never commit real credentials.** Add `config/db.php` to `.gitignore` on public repos.

---

### 3 · Create the database

```bash
mysql -u root -p < setup.sql
```

Or inside the MySQL shell:

```sql
SOURCE /path/to/tasky/setup.sql;
```

---

### 4 · Start the PHP development server

```bash
php -S localhost:8080
```

---

### 5 · Open the app

```
http://localhost:8080
```

---

## 🔌 API Reference

All requests are **HTTP POST** with a JSON body to `api.php`.  
Every response follows the envelope:

```json
{ "success": true, "data": { ... }, "message": "..." }
```

| Action | Payload | Description |
|---|---|---|
| `list` | — | Returns all tasks — pending first, then completed |
| `add` | `{ "title": "…" }` | Inserts a new pending task; returns the created row |
| `edit` | `{ "id": 1, "title": "…" }` | Updates the task title |
| `complete` | `{ "id": 1 }` | Sets `status` → `completed` |
| `delete` | `{ "id": 1 }` | Hard-deletes the task row |

**Example request:**

```js
const res = await fetch('api.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ action: 'add', title: 'Buy groceries' }),
});
const json = await res.json();
// { success: true, data: { id: 1, title: "Buy groceries", status: "pending", ... } }
```

---

## 🔄 AJAX Flow

The frontend holds **no local state** — after every mutation, `fetchTasks()` re-renders both lists from the server.

```
Page Load
  └─ fetchTasks()  →  POST api.php { action: "list" }
        └─ renders #pending-list + #completed-list

User: Add / Complete / Delete
  └─ api(action, payload)  →  POST api.php
        └─ success  →  fetchTasks()  →  both lists refresh

User: Inline Edit
  └─ startEdit()   →  DOM swap: title div → <input>   [no request]
  └─ saveEdit()    →  api("edit", {id, title})  →  fetchTasks()
  └─ cancelEdit()  →  fetchTasks()  (restore from server)
```

---

## 🔒 Security

- **Prepared statements** — all queries use PDO named placeholders (`:id`, `:title`)
- **Input sanitisation** — `strip_tags()` + whitespace normalisation on every title
- **Integer validation** — IDs pass through `FILTER_VALIDATE_INT`, never raw-cast
- **XSS prevention** — all output in the browser is escaped via `escHtml()`
- **Error safety** — raw PDO exceptions are logged server-side; clients receive only a generic message
- **HTTP headers** — `Content-Type: application/json` + `X-Content-Type-Options: nosniff`

---

## 🛠️ Troubleshooting

| Problem | Fix |
|---|---|
| `JSON.parse` error | Open `api.php?action=list` directly in browser. Remove any stray `echo` from `config/db.php` |
| `A database error occurred` | Verify `DB_PASS` matches your MySQL password. Check MySQL service is running |
| `Class "PDO" not found` | Uncomment `extension=pdo_mysql` in `php.ini`, restart server |
| `Unknown database 'todo_list'` | Re-run `setup.sql` |
| `php` not recognised (Windows) | Add `C:\php` to system `PATH`, open a new terminal |
| Port 8080 in use | Use another port: `php -S localhost:9090` |

---

## 📁 `.gitignore` recommendation

```gitignore
# Exclude DB credentials from version control
config/db.php

# OS & editor noise
.DS_Store
Thumbs.db
.vscode/
*.log
```

---

## 📜 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

<div align="center">

Made with ☕ and PHP &nbsp;·&nbsp; **Tasky v1.0**

</div>
