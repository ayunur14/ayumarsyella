<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Pakaian.php';
require_once 'models/Penjualan.php';

$database = new Database();
$db = $database->getConnection();

$pakaian = new pakaian($db);
$penjualan = new Penjualan($db);

$message = '';
$message_type = '';

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'search_pakaian':
            $keyword = sanitizeInput($_GET['keyword']);
            $stmt = $pakaian->search($keyword);
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }
            echo json_encode($results);
            exit;
            
        case 'get_pakaian':
            $pakaian_id = sanitizeInput($_GET['pakaian_id']);
            $pakaian->id = $pakaian_id;
            if ($pakaian->readOne()) {
                echo json_encode([
                    'id' => $pakaian->id,
                    'no_transaksi' => $pakaian->no_transaksi,
                    'costumer_id' => $pakaian->customer_id,
                    'diskon' => $pakaian->diskon,
                    'total_harga' => $pakaian->total_harga,
                    'total_bayar' => $pakaian->total_bayar,
                    'kembalian' => $pakaian->kembalian,
                    'kode_pakaian' => $pakaian->kode_pakaian,
                    'nama_pakaian' => $pakaian->nama_pakaian,
                    'stok' => $pakaian->stok
                ]);
            } else {
                echo json_encode(['error' => 'pakaian tidak ditemukan']);
            }
            exit;
    }
}

// Handle transaction submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'process_transaction') {
    try {
        $db->beginTransaction();
        
        // Create penjualan record
        $penjualan->no_transaksi = $penjualan->generateNoTransaksi();
        $penjualan->user_id = $_SESSION['user_id'];
        $penjualan->customer_id = sanitizeInput($_POST['customer_id'] ?? null);
        $penjualan->total_harga = sanitizeInput($_POST['total_harga']);
        $penjualan->total_bayar = sanitizeInput($_POST['total_bayar']);
        $penjualan->diskon = sanitizeInput($_POST['diskon']?? 0 );
        $penjualan->note = sanitizeInput($_POST['note']??'');
        $penjualan->kembalian = sanitizeInput($_POST['kembalian']);
        
        if (!$penjualan->create()) {
            throw new Exception('Gagal membuat transaksi');
        }
        
        $penjualan_id = $db->lastInsertId();
        
        // Process detail penjualan
        $items = json_decode($_POST['items'], true);
        foreach ($items as $item) {
            // Insert detail penjualan
           $detail_query = "INSERT INTO detail_penjualan 
            (penjualan_id, pakaian_id, jumlah, harga_satuan, subtotal,note )
            VALUES (:penjualan_id, :pakaian_id, :jumlah, :harga_satuan, :subtotal, :note)";

       $detail_stmt = $db->prepare($detail_query);
       $detail_stmt->bindParam(':penjualan_id', $penjualan_id);
       $detail_stmt->bindParam(':pakaian_id', $item['id']);
       $detail_stmt->bindParam(':jumlah', $item['quantity']);
       $detail_stmt->bindParam(':harga_satuan', $item['price']);
       $detail_stmt->bindParam(':subtotal', $item['subtotal']);
       $detail_stmt->bindValue(':note', $penjualan->note ?? '');

            
            if (!$detail_stmt->execute()) {
                throw new Exception('Gagal menyimpan detail penjualan');
            }
            
            // Update stok pakaian
            $pakaian->updateStok($item['id'], -$item['quantity']);
        }
        
        $db->commit();
        
        // Redirect to receipt
        header('Location: struk.php?id=' . $penjualan_id);
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            height: calc(100vh - 120px);
        }
        
        .product-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            overflow-y: auto;
        }
        
        .cart-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .product-card {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            border-color: #3498db;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.2);
        }
        
        .product-card.selected {
            border-color: #27ae60;
            background: #f8fff8;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .product-price {
            color: #27ae60;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .product-stock {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 500;
            margin-bottom: 2px;
        }
        
        .item-price {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .quantity-control button {
            width: 25px;
            height: 25px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            cursor: pointer;
            border-radius: 3px;
        }
        
        .quantity-control input {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 2px;
        }
        
        .cart-summary {
            border-top: 2px solid #e1e1e1;
            padding-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 18px;
            color: #2c3e50;
            border-top: 1px solid #e1e1e1;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .payment-section {
            margin-top: 20px;
        }
        
        .payment-section input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .btn-process {
            width: 100%;
            padding: 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .btn-process:hover {
            background: #229954;
        }
        
        .btn-process:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
    </style>
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
                
                <li class="nav-item">
                    <a href="penjualan.php" class="nav-link active">
                        <i>🛒</i> Penjualan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="laporan_penjualan.php" class="nav-link">
                        <i>📈</i> Laporan Penjualan
                    </a>
                </li>
                
                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'gudang'): ?>
                <li class="nav-item">
                    <a href="pakaian.php" class="nav-link">
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
                        <i>🏢</i> Vendor
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
                <h1>Point of Sale (POS)</h1>
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

                <div class="pos-container">
                    <!-- Product Section -->
                    <div class="product-section">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Cari pakaian..." onkeyup="searchProducts()">
                        </div>
                        
                        <div id="productGrid" class="product-grid">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>

                    <!-- Cart Section -->
                    <div class="cart-section">
                        <h3>Keranjang Belanja</h3>
                        
                        <div class="cart-items" id="cartItems">
                            <p style="text-align: center; color: #7f8c8d; padding: 20px;">
                                Keranjang kosong
                            </p>
                        </div>
                        
                        <div class="cart-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="subtotal">Rp 0</span>
                            </div>
                            <div class="payment-section">
                              <input type="number" id="discountInput" placeholder="diskon(%)" onkeyup ="updateSummary()">
                              
                            <div class="summary-row">
                                <span>PPN (10%):</span>
                                <span id="ppn">Rp 0</span>
                        </div>
                             <div class="summary-row total">
                                <span>Total:</span>
                                <span id="total">Rp 0</span>
                            </div>
                        </div>
                        
                        <div class="payment-section">
                              <input type="number" id="paymentInput" placeholder="Jumlah Bayar" onkeyup="calculateChange()">
                              <input type="text" id="noteInput" placeholder="catatan (opsional)">
                            
                                  <div class="summary-row">
                                 <span>Kembalian:</span>
                                  <span id="change">Rp 0</span>
                      </div>
                            <button class="btn-process" id="processBtn" onclick="processTransaction()" disabled>
                            Proses Transaksi
                            </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

   <script>
let cart = [];
let products = [];

// Load products on page load
window.onload = function() {
  searchProducts();
};

function searchProducts() {
  const keyword = document.getElementById('searchInput').value;
  fetch(`penjualan.php?action=search_pakaian&keyword=${encodeURIComponent(keyword)}`)
    .then(r => r.json())
    .then(data => { products = data; displayProducts(data); })
    .catch(err => console.error('Error:', err));
}

function displayProducts(products) {
  const grid = document.getElementById('productGrid');
  grid.innerHTML = '';
  products.forEach(p => {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.onclick = () => addToCart(p);
    card.innerHTML = `
      <div class="product-name">${p.nama_pakaian}</div>
      <div class="product-price">${formatCurrency(p.harga_jual)}</div>
      <div class="product-stock">Stok: ${p.stok} ${p.satuan}</div>`;
    grid.appendChild(card);
  });
}

function addToCart(p) {
  if (p.stok <= 0) return alert('Stok habis!');
  const exist = cart.find(i => i.id === p.id);
  if (exist) {
    if (exist.quantity < p.stok) exist.quantity++;
    else return alert('Stok tidak mencukupi!');
  } else {
    cart.push({
      id: p.id,
      kode_pakaian: p.kode_pakaian,
      nama_pakaian: p.nama_pakaian,
      price: parseFloat(p.harga_jual),
      quantity: 1,
      max_stock: p.stok
    });
  }
  updateCartDisplay();
}

function updateCartDisplay() {
  const cartItems = document.getElementById('cartItems');
  if (!cartItems) return;

  if (cart.length === 0) {
    cartItems.innerHTML = '<p style="text-align:center;color:#7f8c8d;padding:20px;">Keranjang kosong</p>';
    updateSummary();
    return;
  }

  cartItems.innerHTML = '';
  cart.forEach((item, i) => {
    const el = document.createElement('div');
    el.className = 'cart-item';
    el.innerHTML = `
      <div class="item-info">
        <div class="item-name">${item.nama_pakaian}</div>
        <div class="item-price">${formatCurrency(item.price)}</div>
      </div>
      <div class="item-controls">
        <div class="quantity-control">
          <button onclick="updateQuantity(${i},-1)">-</button>
          <input type="number" value="${item.quantity}" min="1" max="${item.max_stock}" onchange="setQuantity(${i},this.value)">
          <button onclick="updateQuantity(${i},1)">+</button>
        </div>
        <button onclick="removeFromCart(${i})" class="btn btn-danger btn-sm">Hapus</button>
      </div>`;
    cartItems.appendChild(el);
  });
  updateSummary();
}

function updateQuantity(i, c) {
  const item = cart[i];
  const q = item.quantity + c;
  if (q >= 1 && q <= item.max_stock) {
    item.quantity = q;
    updateCartDisplay();
  }
}
function setQuantity(i, v) {
  const q = parseInt(v);
  if (q >= 1 && q <= cart[i].max_stock) {
    cart[i].quantity = q;
    updateCartDisplay();
  }
}
function removeFromCart(i) {
  cart.splice(i, 1);
  updateCartDisplay();
}

// ==== HITUNGAN DASAR ====
function getTotals() {
  const subtotal = cart.reduce((s, i) => s + i.price * i.quantity, 0);
  const discountPercent = parseFloat(document.getElementById('discountInput')?.value) || 0;
  const discountAmount = subtotal * (discountPercent / 100);
  const base = subtotal - discountAmount;
  const ppn = base * 0.1;
  const total = base + ppn;
  return { subtotal, discountPercent, discountAmount, ppn, total };
}

// ==== UPDATE UI ====
function updateSummary() {
  const { subtotal, ppn, total } = getTotals();
  document.getElementById('subtotal').textContent = formatCurrency(subtotal);
  document.getElementById('ppn').textContent = formatCurrency(ppn);
  document.getElementById('total').textContent = formatCurrency(total);
  calculateChange();
}

function calculateChange() {
  const { total } = getTotals();
  const pay = parseFloat(document.getElementById('paymentInput').value) || 0;
  const change = pay - total;
  document.getElementById('change').textContent = formatCurrency(Math.max(0, change));
  document.getElementById('processBtn').disabled = cart.length === 0 || pay < total;
}

function processTransaction() {
  const { total, discountPercent } = getTotals();
  const pay = parseFloat(document.getElementById('paymentInput').value) || 0;
  const change = pay - total;
  if (pay < total) return alert('Jumlah bayar kurang!');

  cart.forEach(i => i.subtotal = i.price * i.quantity);
  const note = document.getElementById('noteInput')?.value.trim() || '';

  const form = document.createElement('form');
  form.method = 'POST';
  form.innerHTML = `
    <input type="hidden" name="action" value="process_transaction">
    <input type="hidden" name="items" value='${JSON.stringify(cart)}'>
    <input type="hidden" name="diskon" value="${discountPercent}">
    <input type="hidden" name="note" value="${note}">
    <input type="hidden" name="total_harga" value="${total}">
    <input type="hidden" name="total_bayar" value="${pay}">
    <input type="hidden" name="kembalian" value="${change}">`;
  document.body.appendChild(form);
  form.submit();
}

function formatCurrency(a) {
  return 'Rp ' + new Intl.NumberFormat('id-ID').format(a);
}
</script>

</body>
</html>
