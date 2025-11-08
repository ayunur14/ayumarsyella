<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Penjualan.php';

$database = new Database();
$db = $database->getConnection();

$penjualan = new Penjualan($db);

$penjualan_id = sanitizeInput($_GET['id']);
$penjualan->id = $penjualan_id;

if (!$penjualan->readOne()) {
    header('Location: penjualan.php');
    exit();
}

$detail_stmt = $penjualan->getDetailPenjualan($penjualan_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - <?php echo APP_NAME; ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .receipt {
            width: 300px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .header p {
            margin: 0;
            font-size: 10px;
        }
        
        .transaction-info {
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .items {
            margin-bottom: 15px;
        }
        
        .item-header {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1fr;
            gap: 5px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1fr;
            gap: 5px;
            margin-bottom: 3px;
        }
        
        .summary {
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .summary-row.total {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .receipt {
                border: none;
                width: 100%;
                max-width: 300px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn">Cetak Struk</button>
        <a href="penjualan.php" class="btn">Transaksi Baru</a>
        <a href="dashboard.php" class="btn">Dashboard</a>
    </div>

    <div class="receipt">
        <div class="header">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Jl. Raya Fashion No. 123<br>
            Telp: (021) 123-4567<br>
            Email: info@fashion.com</p>
        </div>
        
        <div class="transaction-info">
            <div class="info-row">
                <span>No. Transaksi:</span>
                <span><?php echo $penjualan->no_transaksi; ?></span>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span><?php echo date('d/m/Y H:i:s', strtotime($penjualan->tanggal_penjualan)); ?></span>
            </div>
            <div class="info-row">
                <span>Kasir:</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="items">
            <div class="item-header">
                <span>Item</span>
                <span>Qty</span>
                <span>Harga</span>
                <span>Total</span>
            </div>
            
            <?php 
            $subtotal = 0;
            while ($row = $detail_stmt->fetch(PDO::FETCH_ASSOC)): 
                $subtotal += $row['subtotal'];
            ?>
            <div class="item-row">
                <span><?php echo $row['nama_pakaian']; ?></span>
                <span><?php echo $row['jumlah']; ?></span>
                <span><?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?></span>
                <span><?php echo number_format($row['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="summary">
            <?php
            // ambil diskon dari transaksi
            $discountPercent = isset($penjualan->diskon) ? $penjualan->diskon : 0;
            $discountAmount =$subtotal * ($discountPercent / 100);
            $ppn = ($subtotal - $discountAmount) * 0.1;
            ?>
    
        <?php if (!empty($penjualan->note)): ?>
         <div class="summary-row">
        <span>Catatan:</span>
        <span><?php echo htmlspecialchars($penjualan->note); ?></span>
     </div>
  <?php endif; ?>

            <div class="summary-row">
                <span>Subtotal:</span>
                <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
            </div>

            <div class="summary-row">
                <span>Diskon (<?php echo $discountPercent; ?>%):</span>
                <span>- Rp <?php echo number_format($discountAmount, 0, ',', '.'); ?></span>
            </div>

            <div class="summary-row">
                <span>PPN (10%):</span>
                <span>Rp <?php echo number_format($subtotal * 0.1, 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>Rp <?php echo number_format($penjualan->total_harga, 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Bayar:</span>
                <span>Rp <?php echo number_format($penjualan->total_bayar, 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Kembalian:</span>
                <span>Rp <?php echo number_format($penjualan->kembalian, 0, ',', '.'); ?></span>
            </div>
        <div class="summary-row">
                <span>Catatan:</span>
                <span><?php echo htmlspecialchars($penjualan->note); ?></span>
        </div>

        </div>
        
        <div class="footer">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
