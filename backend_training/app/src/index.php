<?php
require_once 'config.php'; // 設定ファイルを読み込み

// レスポンスのヘッダーを設定（JSON形式で返す）
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

global $pdo;

// ルーティング設定
$routes = [
    'GET' => [
        '#^/todos$#' => 'handleGetTodos',
        '#^/health$#' => 'handleHealthCheck',
    ],
    'POST' => [
        '#^/todos$#' => 'handlePostTodo',
    ],
    'PUT' => [
        '#^/todos/(\d+)$#' => 'handleUpdateTodo',
    ],
    'DELETE' => [
        '#^/todos/(\d+)$#' => 'handleDeleteTodo',
    ]
];

// ルーティング処理
if (isset($routes[$method])) {
    foreach ($routes[$method] as $pattern => $handler) {
        if (preg_match($pattern, $requestUri, $matches)) {
            array_shift($matches);
            call_user_func_array($handler, array_merge([$pdo], $matches));
            exit;
        }
    }
}

http_response_code(404);
echo json_encode(['error' => 'Not Found']);
exit;

// ==========================
// **GET /todos**: すべてのタスクを取得
// ==========================
function handleGetTodos(PDO $pdo): void
{
    try {
        $stmt = $pdo->query("SELECT todos.id, todos.title, statuses.name FROM todos JOIN statuses ON todos.status_id = statuses.id;");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'ok', 'data' => $result]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to get todos', 'error' => $e->getMessage()]);
    }
    exit;
}

// ==========================
// **POST /todos**: 新しいタスクを追加
// ==========================
function handlePostTodo(PDO $pdo): void
{
    try {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['title']) || empty($input['title'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Title is required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO todos (title, status_id) VALUES (:title, 1)");
        $stmt->bindParam(':title', $input['title']);
        $stmt->execute();

        http_response_code(201);
        echo json_encode(['status' => 'ok', 'message' => 'Todo added']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add todo', 'error' => $e->getMessage()]);
    }
    exit;
}

// ==========================
// **DELETE /todos/{id}**: タスクを削除
// ==========================
function handleDeleteTodo(PDO $pdo, int $id): void
{
    try {
        $stmt = $pdo->prepare("DELETE FROM todos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Todo not found']);
            exit;
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'message' => 'Todo deleted']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete todo', 'error' => $e->getMessage()]);
    }
    exit;
}

// ==========================
// **PUT /todos/{id}**: タスクを更新
// ==========================
function handleUpdateTodo(PDO $pdo, int $id): void
{
    try {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['title']) || empty($input['title'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Title is required']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE todos SET title = :title WHERE id = :id");
        $stmt->bindParam(':title', $input['title']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Todo not found']);
            exit;
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'message' => 'Todo updated']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update todo', 'error' => $e->getMessage()]);
    }
    exit;
}

// ==========================
// **GET /health**: サーバーのヘルスチェック
// ==========================
function handleHealthCheck(PDO $pdo): void
{
    try {
        $stmt = $pdo->query("SELECT 1");
        $result = $stmt->fetchColumn();

        if ($result == 1) {
            echo json_encode(['status' => 'ok', 'database' => 'connected']);
        } else {
            throw new RuntimeException('Database connection failed');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed', 'error' => $e->getMessage()]);
    }
    exit;
}

