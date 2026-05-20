<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request (needed for some browsers)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET — fetch all tickets ────────────────────────────────
if ($method === 'GET') {
    $stmt = $pdo->query(
        'SELECT * FROM tickets ORDER BY id DESC'
    );
    $rows = $stmt->fetchAll();

    // Map DB column names back to JS-friendly keys
    $tickets = array_map(function($r) {
        return [
            'id'          => $r['id'],
            'date'        => $r['date'],
            'user'        => $r['raised_by'],
            'dept'        => $r['department'] ?? '',
            'desc'        => $r['description'],
            'status'      => $r['status'],
            'solution'    => $r['solution'] ?? '',
            'resolver'    => $r['resolver'] ?? '',
            'resolvedate' => $r['resolve_date'] ?? '',
        ];
    }, $rows);

    echo json_encode($tickets);
    exit;
}

// ── POST — create new ticket ───────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);

    if (empty($d['id']) || empty($d['user']) || empty($d['desc'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: id, user, desc']);
        exit;
    }

    $stmt = $pdo->prepare('
        INSERT INTO tickets
            (id, date, raised_by, department, description, status, solution, resolver, resolve_date)
        VALUES
            (:id, :date, :raised_by, :department, :description, :status, :solution, :resolver, :resolve_date)
    ');

    $stmt->execute([
        ':id'           => $d['id'],
        ':date'         => $d['date'],
        ':raised_by'    => $d['user'],
        ':department'   => $d['dept']        ?? null,
        ':description'  => $d['desc'],
        ':status'       => $d['status']      ?? 'Open',
        ':solution'     => $d['solution']    ?? null,
        ':resolver'     => $d['resolver']    ?? null,
        ':resolve_date' => $d['resolvedate'] ?: null,
    ]);

    echo json_encode(['ok' => true, 'id' => $d['id']]);
    exit;
}

// ── PUT — update existing ticket ───────────────────────────
if ($method === 'PUT') {
    $d = json_decode(file_get_contents('php://input'), true);

    if (empty($d['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing ticket id']);
        exit;
    }

    $stmt = $pdo->prepare('
        UPDATE tickets SET
            raised_by    = :raised_by,
            department   = :department,
            description  = :description,
            status       = :status,
            solution     = :solution,
            resolver     = :resolver,
            resolve_date = :resolve_date
        WHERE id = :id
    ');

    $stmt->execute([
        ':raised_by'    => $d['user'],
        ':department'   => $d['dept']        ?? null,
        ':description'  => $d['desc'],
        ':status'       => $d['status']      ?? 'Open',
        ':solution'     => $d['solution']    ?? null,
        ':resolver'     => $d['resolver']    ?? null,
        ':resolve_date' => $d['resolvedate'] ?: null,
        ':id'           => $d['id'],
    ]);

    echo json_encode(['ok' => true]);
    exit;
}

// ── DELETE — remove a ticket ───────────────────────────────
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing ticket id in query string']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM tickets WHERE id = :id');
    $stmt->execute([':id' => $id]);

    echo json_encode(['ok' => true]);
    exit;
}

// Catch-all for unsupported methods
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
