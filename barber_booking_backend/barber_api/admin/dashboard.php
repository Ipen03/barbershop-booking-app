<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

require_once '../config.php';

$msg = "";
$msg_type = "success";

// 1. PROSES UPDATE STATUS BOOKING
if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['id']) && isset($_GET['status'])) {
    $booking_id = intval($_GET['id']);
    $new_status = $_GET['status'];

    if (in_array($new_status, ['Pending', 'Confirmed', 'Completed', 'Cancelled'])) {
        try {
            $stmt = $conn->prepare("UPDATE bookings SET status = :status WHERE id = :id");
            $stmt->bindParam(":status", $new_status);
            $stmt->bindParam(":id", $booking_id);
            if ($stmt->execute()) {
                $msg = "Status pemesanan #$booking_id berhasil diperbarui menjadi <b>$new_status</b>.";
                $msg_type = "success";
            } else {
                $msg = "Gagal memperbarui status pemesanan.";
                $msg_type = "error";
            }
        } catch (PDOException $e) {
            $msg = "Error: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}

// 2. AMBIL DATA STATISTIK
try {
    // Total bookings
    $total_bookings = $conn->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    // Pending bookings
    $pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
    // Total customers
    $total_customers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    // Estimasi pendapatan (Khusus yang status Confirmed & Completed)
    $revenue = $conn->query("SELECT SUM(s.price) FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.status IN ('Confirmed', 'Completed')")->fetchColumn();
    $revenue = $revenue ? $revenue : 0;
} catch (PDOException $e) {
    $total_bookings = 0;
    $pending_bookings = 0;
    $total_customers = 0;
    $revenue = 0;
}

// 3. AMBIL DAFTAR RESERVASI (JOIN DATA)
$bookings = [];
try {
    $query = "SELECT b.id, b.booking_date, b.booking_time, b.status, b.notes, 
                     u.name as user_name, u.phone as user_phone, 
                     s.name as service_name, s.price as service_price, 
                     ba.name as barber_name 
              FROM bookings b
              JOIN users u ON b.user_id = u.id
              JOIN services s ON b.service_id = s.id
              JOIN barbers ba ON b.barber_id = ba.id
              ORDER BY b.booking_date DESC, b.booking_time DESC";
    $bookings = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msg = "Gagal memuat data booking: " . $e->getMessage();
    $msg_type = "error";
}

// 4. AMBIL DAFTAR PELANGGAN
$customers = [];
try {
    $customers = $conn->query("SELECT id, name, email, phone, created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msg = "Gagal memuat data pelanggan: " . $e->getMessage();
    $msg_type = "error";
}

// Fungsi Format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Gentleman's Barbershop</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: #121212;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar Header */
        header {
            background-color: #1E1E1E;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .logo-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #ffc107;
            font-size: 20px;
            font-weight: bold;
        }

        .logo-text h1 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }

        .logo-text span {
            font-size: 11px;
            color: #ffc107;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-logout {
            padding: 8px 18px;
            background: rgba(244, 67, 54, 0.15);
            border: 1px solid rgba(244, 67, 54, 0.3);
            border-radius: 8px;
            color: #ff5252;
            font-weight: bold;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #ff5252;
            color: #ffffff;
            box-shadow: 0 0 10px rgba(244, 67, 54, 0.2);
        }

        /* Container & Main Layout */
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 30px auto;
            padding: 0 20px;
            flex: 1;
        }

        /* Notification Toast */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.15);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #4caf50;
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.15);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: #f44336;
        }

        /* Statistics Widget Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: #1E1E1E;
            padding: 20px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
        }

        .icon-blue { background: rgba(33, 150, 243, 0.1); color: #2196f3; }
        .icon-orange { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
        .icon-green { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
        .icon-yellow { background: rgba(255, 193, 7, 0.1); color: #ffc107; }

        .stat-info span {
            font-size: 12px;
            color: #aaaaaa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }

        .stat-info h3 {
            font-size: 22px;
            font-weight: 700;
        }

        /* Tabs Navigation */
        .tab-menu {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 10px;
        }

        .tab-btn {
            background: none;
            border: none;
            color: #888888;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            padding: 8px 16px;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-btn.active {
            color: #ffc107;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -11px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #ffc107;
            border-radius: 3px 3px 0 0;
        }

        /* Tab Content Box */
        .tab-content {
            display: none;
            background: #1E1E1E;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.03);
            overflow-x: auto;
        }

        .tab-content.active {
            display: block;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Beautiful Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: rgba(255, 255, 255, 0.02);
            color: #aaaaaa;
            padding: 14px 16px;
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #dddddd;
            vertical-align: middle;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.01);
        }

        /* Badge Status */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending { background: rgba(255, 152, 0, 0.15); border: 1px solid rgba(255, 152, 0, 0.4); color: #ff9800; }
        .badge-confirmed { background: rgba(76, 175, 80, 0.15); border: 1px solid rgba(76, 175, 80, 0.4); color: #4caf50; }
        .badge-completed { background: rgba(33, 150, 243, 0.15); border: 1px solid rgba(33, 150, 243, 0.4); color: #2196f3; }
        .badge-cancelled { background: rgba(244, 67, 54, 0.15); border: 1px solid rgba(244, 67, 54, 0.4); color: #f44336; }

        /* Action Buttons */
        .actions-cell {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-confirm { background: #4caf50; color: #ffffff; border: none; }
        .btn-confirm:hover { background: #3d8b40; }

        .btn-complete { background: #2196f3; color: #ffffff; border: none; }
        .btn-complete:hover { background: #1976d2; }

        .btn-cancel { background: #f44336; color: #ffffff; border: none; }
        .btn-cancel:hover { background: #d32f2f; }

        .notes-text {
            font-size: 12px;
            color: #888888;
            font-style: italic;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notes-text:hover {
            white-space: normal;
            word-break: break-all;
            color: #aaaaaa;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #888888;
        }

        .empty-state-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }

        footer {
            background-color: #121212;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #444444;
            border-top: 1px solid rgba(255, 255, 255, 0.02);
            margin-top: auto;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-title">
            <div class="logo-icon">✂</div>
            <div class="logo-text">
                <h1>GENTLEMAN'S</h1>
                <span>Dashboard Admin Portal</span>
            </div>
        </div>
        <a href="logout.php" class="btn-logout">LOG OUT</a>
    </header>

    <div class="container">
        <!-- Toast Notification -->
        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>">
                <span><?php echo $msg; ?></span>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-blue">📅</div>
                <div class="stat-info">
                    <span>Total Pemesanan</span>
                    <h3><?php echo $total_bookings; ?></h3>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon icon-orange">⏳</div>
                <div class="stat-info">
                    <span>Menunggu Persetujuan</span>
                    <h3><?php echo $pending_bookings; ?></h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-green">👥</div>
                <div class="stat-info">
                    <span>Total Pelanggan</span>
                    <h3><?php echo $total_customers; ?></h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-yellow">💰</div>
                <div class="stat-info">
                    <span>Estimasi Pendapatan</span>
                    <h3><?php echo formatRupiah($revenue); ?></h3>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="tab-menu">
            <button class="tab-btn active" onclick="openTab(event, 'tab-bookings')">Reservasi Masuk (<?php echo count($bookings); ?>)</button>
            <button class="tab-btn" onclick="openTab(event, 'tab-customers')">Daftar Pelanggan (<?php echo count($customers); ?>)</button>
        </div>

        <!-- TAB BOOKINGS -->
        <div id="tab-bookings" class="tab-content active">
            <div class="section-title">
                <span>Kelola Reservasi Pelanggan</span>
            </div>
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <p>Belum ada data reservasi masuk.</p>
                </div>
            <?php else: ?>
                <table style="min-width: 1000px;">
                    <thead>
                        <tr>
                            <th>No Booking</th>
                            <th>Pelanggan</th>
                            <th>Nomor HP</th>
                            <th>Layanan</th>
                            <th>Barber</th>
                            <th>Tanggal & Jam</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><b>#<?php echo $b['id']; ?></b></td>
                                <td><?php echo htmlspecialchars($b['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($b['user_phone']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($b['service_name']); ?><br>
                                    <span style="font-size: 11px; color: #ffc107;"><?php echo formatRupiah($b['service_price']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($b['barber_name']); ?></td>
                                <td>
                                    <?php 
                                        $d = DateTime::createFromFormat('Y-m-d', $b['booking_date']);
                                        echo $d ? $d->format('d M Y') : $b['booking_date'];
                                    ?><br>
                                    <span style="font-size: 12px; color: #aaaaaa;"><?php echo htmlspecialchars($b['booking_time']); ?> WIB</span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($b['status']); ?>">
                                        <?php echo $b['status']; ?>
                                    </span>
                                </td>
                                <td class="notes-text" title="<?php echo htmlspecialchars($b['notes'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($b['notes'] ? $b['notes'] : '-'); ?>
                                </td>
                                <td class="actions-cell">
                                    <?php if ($b['status'] === 'Pending'): ?>
                                        <a href="?action=update&id=<?php echo $b['id']; ?>&status=Confirmed" class="btn-action btn-confirm">Setujui</a>
                                        <a href="?action=update&id=<?php echo $b['id']; ?>&status=Cancelled" class="btn-action btn-cancel">Tolak</a>
                                    <?php elseif ($b['status'] === 'Confirmed'): ?>
                                        <a href="?action=update&id=<?php echo $b['id']; ?>&status=Completed" class="btn-action btn-complete">Selesai</a>
                                        <a href="?action=update&id=<?php echo $b['id']; ?>&status=Cancelled" class="btn-action btn-cancel">Batalkan</a>
                                    <?php else: ?>
                                        <span style="color: #666666; font-size: 12px; font-style: italic;">No Action</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- TAB CUSTOMERS -->
        <div id="tab-customers" class="tab-content">
            <div class="section-title">
                <span>Data Pelanggan Terdaftar</span>
            </div>
            <?php if (empty($customers)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <p>Belum ada pelanggan terdaftar.</p>
                </div>
            <?php else: ?>
                <table style="min-width: 600px;">
                    <thead>
                        <tr>
                            <th>ID User</th>
                            <th>Nama Lengkap</th>
                            <th>Alamat Email</th>
                            <th>Nomor WhatsApp</th>
                            <th>Tanggal Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td><b>#<?php echo $c['id']; ?></b></td>
                                <td><?php echo htmlspecialchars($c['name']); ?></td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                <td><?php echo htmlspecialchars($c['phone']); ?></td>
                                <td>
                                    <?php 
                                        $d = new DateTime($c['created_at']);
                                        echo $d->format('d M Y, H:i');
                                    ?> WIB
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        Gentleman's Barbershop &copy; 2026 - Proyek Akhir Pemrograman Aplikasi Mobile
    </footer>

    <script>
        function openTab(evt, tabId) {
            // Sembunyikan semua tab content
            const tabContents = document.getElementsByClassName("tab-content");
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove("active");
            }

            // Nonaktifkan semua tab button
            const tabBtns = document.getElementsByClassName("tab-btn");
            for (let i = 0; i < tabBtns.length; i++) {
                tabBtns[i].classList.remove("active");
            }

            // Tampilkan tab terpilih & aktifkan button
            document.getElementById(tabId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>
