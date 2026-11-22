<?php
require_once __DIR__ . '/../models/OrderModel.php';

requireRole('Admin');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$orderId = (int) ($_GET['id'] ?? ($_POST['order_id'] ?? 0));

if ($orderId <= 0) {
    setFlash('danger', 'ID pesanan tidak valid.');
    redirect('index.php?page=admin-orders');
}

$detailUrl = 'index.php?page=admin-order-detail&id=' . $orderId;

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'verify_payment') {
        $decision = trim($_POST['decision'] ?? '');
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');
        $result = verifyOrderPayment($orderId, $decision, $rejectionReason);

        if (!$result['ok']) {
            if (!empty($result['field'])) {
                redirectWithErrors($detailUrl, [$result['field'] => $result['message']], ['rejection_reason' => $rejectionReason]);
            }
            setFlash('danger', $result['message']);
            redirect($detailUrl);
        }

        if ($decision === 'approve') {
            setFlash('success', 'Bukti pembayaran berhasil divalidasi. Status pesanan kini Aktif.');
        } else {
            setFlash('warning', 'Bukti pembayaran telah ditolak beserta alasan penolakan.');
        }
        redirect($detailUrl);
    }

    if ($action === 'update_status') {
        $newStatus = trim($_POST['new_status'] ?? '');
        $result = updateOrderStatusByAdmin($orderId, $newStatus);

        if (!$result['ok']) {
            setFlash('danger', $result['message']);
            redirect($detailUrl);
        }

        setFlash('success', "Status pesanan berhasil diperbarui menjadi '{$newStatus}'.");
        redirect($detailUrl);
    }
}

$order = getOrderWithDetailsById($orderId);
if (!$order) {
    setFlash('danger', 'Data pesanan tidak ditemukan.');
    redirect('index.php?page=admin-orders');
}

render('admin/order_detail', [
    'title' => sprintf('Detail Pesanan #ORD-%03d - MealMate', (int) $order['order_id']),
    'order' => $order,
    'errors' => getValidationErrors(),
    'old' => getOldInput(),
], 'admin');
