<?php
require_once 'config/config.php';
requireRole(['admin', 'gudang']);

require_once 'models/Pakaian.php';
require_once 'models/KategoriPakaian.php';

$database = new Database();
$db = $database->getConnection();

$pakaian = new Pakaian($db);
$kategori = new KategoriPakaian($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $pakaian->kode_pakaian = sanitizeInput($_POST['kode_pakaian']);
                $pakaian->nama_pakaian = sanitizeInput($_POST['nama_pakaian']);
                $pakaian->kategori_id = sanitizeInput($_POST['kategori_id']);
                $pakaian->satuan = sanitizeInput($_POST['satuan']);
                $pakaian->warna = sanitizeInput($_POST['warna']);
                $pakaian->ukuran = sanitizeInput($_POST['ukuran']);
                $pakaian->harga_modal = sanitizeInput($_POST['harga_modal']);
                $pakaian->harga_jual = sanitizeInput($_POST['harga_jual']);
                $pakaian->stok = sanitizeInput($_POST['stok']);
                $pakaian->stok_minimal = sanitizeInput($_POST['stok_minimal']);
                $pakaian->deskripsi = sanitizeInput($_POST['deskripsi']);

                if ($pakaian->create()) {
                    $message = 'Data pakaian berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan data pakaian!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $pakaian->id = sanitizeInput($_POST['id']);
                $pakaian->kode_pakaian = sanitizeInput($_POST['kode_pakaian']);
                $pakaian->nama_pakaian = sanitizeInput($_POST['nama_pakaian']);
                $pakaian->kategori_id = sanitizeInput($_POST['kategori_id']);
                $pakaian->satuan = sanitizeInput($_POST['satuan']);
                $pakaian->warna = sanitizeInput($_POST['warna']);
                $pakaian->ukuran = sanitizeInput($_POST['ukuran']);
                $pakaian->harga_modal = sanitizeInput($_POST['harga_modal']);
                $pakaian->harga_jual = sanitizeInput($_POST['harga_jual']);
                $pakaian->stok = sanitizeInput($_POST['stok']);
                $pakaian->stok_minimal = sanitizeInput($_POST['stok_minimal']);
                $pakaian->deskripsi = sanitizeInput($_POST['deskripsi']);

                if ($pakaian->update()) {
                    $message = 'Data pakaian berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui data pakaian!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $pakaian->id = sanitizeInput($_POST['id']);
                if ($pakaian->delete()) {
                    $message = 'Data pakaian berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus data pakaian!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all pakaian
$stmt = $pakaian->readAll();

// Get all kategori for dropdown
$kategori_stmt = $kategori->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data pakaian - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo APP_NAME; ?></h2>
            </div>
            
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i>📊</i> Dashboard
                    </a>
                </li>
                
                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'kasir'): ?>
                <li class="nav-item">
                    <a href="penjualan.php" class="nav-link">
                        <i>🛒</i> Penjualan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="laporan_penjualan.php" class="nav-link">
                        <i>📈</i> Laporan Penjualan
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'gudang'): ?>
                <li class="nav-item">
                    <a href="pakaian.php" class="nav-link active">
                        <i>👕</i> Data pakaian
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pembelian.php" class="nav-link">
                        <i>📦</i> Pembelian
                    </a>
                </li>
                <li class="nav-item">
                    <a href="stok.php" class="nav-link">
                        <i>📋</i> Manajemen Stok
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="kategori.php" class="nav-link">
                        <i>🏷️</i> Kategori pakaian
                    </a>
                </li>
                <li class="nav-item">
                    <a href="vendor.php" class="nav-link">
                        <i>🏢</i> vendor
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i>👥</i> Manajemen User
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i>🚪</i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <header class="top-nav">
                <h1>Data pakaian</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Add pakaian Form -->
                <div class="form-container">
                    <h2>Tambah pakaian Baru</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kode_pakaian">Kode pakaian</label>
                                <input type="text" id="kode_pakaian" name="kode_pakaian" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_pakaian">Nama pakaian</label>
                                <input type="text" id="nama_pakaian" name="nama_pakaian" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="kategori_id">Kategori</label>
                                <select id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php while ($row = $kategori_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_kategori']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="satuan">Satuan</label>
                                <input type="text" id="satuan" name="satuan" required placeholder="cth: each, lusin, pack">
                            </div>
                        </div>
                         <div class="form-group">
                                <label for="warna">Warna</label>
                                <input type="text" id="warna" name="warna" required placeholder="cth: merah, biru, hijau">
                                    </div>

                         <div class="form-group">
                                <label for="ukuran">Ukuran</label>
                                <input type="text" id="ukuran" name="ukuran" required placeholder="cth: S, M, L, XL">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="harga_modal">Harga modal</label>
                                <input type="number" id="harga_modal" name="harga_modal" required min="0" step="100">
                            </div>
                            <div class="form-group">
                                <label for="harga_jual">Harga Jual</label>
                                <input type="number" id="harga_jual" name="harga_jual" required min="0" step="100">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="stok">Stok</label>
                                <input type="number" id="stok" name="stok" required min="0">
                            </div>
                            <div class="form-group">
                                <label for="stok_minimal">Stok Minimal</label>
                                <input type="number" id="stok_minimal" name="stok_minimal" required min="0">
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Tambah pakaian</button>
                    </form>
                </div>

                <!-- Data pakaian Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar pakaian</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama pakaian</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Harga modal</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th>Stok Min</t>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['kode_pakaian']; ?></td>
                                <td><?php echo $row['nama_pakaian']; ?></td>
                                <td><?php echo $row['nama_kategori']; ?></td>
                                <td><?php echo $row['satuan']; ?></td>
                                <td><?php echo formatCurrency($row['harga_modal']); ?></td>
                                <td><?php echo formatCurrency($row['harga_jual']); ?></td>
                                <td>
                                    <span class="badge <?php echo $row['stok'] <= $row['stok_minimal'] ? 'badge-danger' : 'badge-success'; ?>">
                                        <?php echo $row['stok']; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['stok_minimal']; ?></td>
                                <td>
                                </td>
                                <td>
                                    <button onclick="editpakaian(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                            class="btn btn-warning btn-sm">Edit</button>
                                    <button onclick="deletepakaian(<?php echo $row['id']; ?>)" 
                                            class="btn btn-danger btn-sm">Hapus</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 600px; max-height: 90%; overflow-y: auto;">
            <h2>Edit pakaian</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_kode_pakaian">Kode pakaian</label>
                        <input type="text" id="edit_kode_pakaian" name="kode_pakaian" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nama_pakaian">Nama pakaian</label>
                        <input type="text" id="edit_nama_pakaian" name="nama_pakaian" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_kategori_id">Kategori</label>
                        <select id="edit_kategori_id" name="kategori_id" required>
                            <?php 
                            $kategori_stmt = $kategori->readAll();
                            while ($row = $kategori_stmt->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_satuan">Satuan</label>
                        <input type="text" id="edit_satuan" name="satuan" required>
                    </div>
                </div>
                
                    <div class="form-row">
                     <div class="form-group">
                        <label for="edit_warna">Warna</label>
                        <input type="text" id="edit_warna" name="warna" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_ukuran">Ukuran</label>
                        <input type="number" id="edit_ukuran" name="ukuran" required min="0" step="100">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_harga_modal">Harga Modal</label>
                        <input type="number" id="edit_harga_modal" name="harga_modal" required min="0" step="100">
                    </div>
                    <div class="form-group">
                        <label for="edit_harga_jual">Harga Jual</label>
                        <input type="number" id="edit_harga_jual" name="harga_jual" required min="0" step="100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_stok">Stok</label>
                        <input type="number" id="edit_stok" name="stok" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="edit_stok_minimal">Stok Minimum</label>
                        <input type="number" id="edit_stok_minimal" name="stok_minimal" required min="0">
                    </div>
                            </div>

                <div class="form-group">
                    <label for="edit_deskripsi">Deskripsi</label>
                    <textarea id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editpakaian(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_kode_pakaian').value = data.kode_pakaian;
            document.getElementById('edit_nama_pakaian').value = data.nama_pakaian;
            document.getElementById('edit_kategori_id').value = data.kategori_id;
            document.getElementById('edit_ukuran').value = data.ukuran;
            document.getElementById('edit_warna').value = data.warna;
            document.getElementById('edit_satuan').value = data.satuan;
            document.getElementById('edit_harga_modal').value = data.harga_modal;
            document.getElementById('edit_harga_jual').value = data.harga_jual;
            document.getElementById('edit_stok').value = data.stok;
            document.getElementById('edit_stok_minimal').value = data.stok_minimal;
            document.getElementById('edit_deskripsi').value = data.deskripsi;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deletepakaian(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data pakaian ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('editModal').onclick = function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
