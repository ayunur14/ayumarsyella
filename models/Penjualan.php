<?php
class Penjualan {
    private $conn;
    private $table_name = "penjualan";

    public $id;
    public $no_transaksi;
    public $user_id;
    public $customer_id;
    public $diskon;
    public $total_harga;
    public $total_bayar;
    public $kembalian;
    public $note;
    public $tanggal_penjualan;

    public function __construct($db) {
        $this->conn = $db;
    }
public function create() {
    // Gunakan sintaks INSERT INTO (...) VALUES (...) biar lebih stabil
    $query = "INSERT INTO " . $this->table_name . " 
              (no_transaksi, user_id, customer_id, diskon, total_harga, total_bayar, kembalian,note)
              VALUES (:no_transaksi, :user_id, :customer_id, :diskon, :total_harga, :total_bayar, :kembalian,:note)";

    $stmt = $this->conn->prepare($query);

    // Sanitasi input
    $this->no_transaksi = htmlspecialchars(strip_tags($this->no_transaksi));
    $this->user_id = htmlspecialchars(strip_tags($this->user_id));
    $this->diskon = htmlspecialchars(strip_tags($this->diskon));
    $this->total_harga = htmlspecialchars(strip_tags($this->total_harga));
    $this->total_bayar = htmlspecialchars(strip_tags($this->total_bayar));
    $this->kembalian = htmlspecialchars(strip_tags($this->kembalian));
    $this->note = htmlspecialchars(strip_tags($this->note));

    // Bind parameter
    $stmt->bindParam(':no_transaksi', $this->no_transaksi);
    $stmt->bindParam(':user_id', $this->user_id);

    // 🔥 handle customer_id secara aman
    if (!empty($this->customer_id) && is_numeric($this->customer_id)) {
        $stmt->bindParam(':customer_id', $this->customer_id, PDO::PARAM_INT);
    } else {
        // Jika tidak ada customer, kirim NULL (biar sesuai ON DELETE SET NULL)
        $stmt->bindValue(':customer_id', null, PDO::PARAM_NULL);
    }

    $stmt->bindParam(':diskon', $this->diskon);
    $stmt->bindParam(':note', $this->note);
    $stmt->bindParam(':total_harga', $this->total_harga);
    $stmt->bindParam(':total_bayar', $this->total_bayar);
    $stmt->bindParam(':kembalian', $this->kembalian);

    // Eksekusi query
    if ($stmt->execute()) {
        return true;
    }

    // Kalau gagal, tampilkan error PDO (buat debugging opsional)
    $errorInfo = $stmt->errorInfo();
    error_log("Penjualan create() error: " . print_r($errorInfo, true));

    return false;
}


    public function readAll() {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.tanggal_penjualan DESC";

        $stmt = $this->conn->prepare($query);   
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->no_transaksi = $row['no_transaksi'];
            $this->user_id = $row['user_id'];
            $this->customer_id = $row['customer_id'];
            $this->diskon = $row['diskon'];
            $this->note = $row['note'];
            $this->total_harga = $row['total_harga'];
            $this->total_bayar = $row['total_bayar'];
            $this->kembalian = $row['kembalian'];
            $this->tanggal_penjualan = $row['tanggal_penjualan'];
            return true;
        }
        return false;
    }

    public function readRecent($limit = 10) {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.tanggal_penjualan DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getDetailPenjualan($penjualan_id) {
        $query = "SELECT dp.*, o.nama_pakaian, o.kode_pakaian
                  FROM detail_penjualan dp
                  LEFT JOIN pakaian o ON dp.pakaian_id = o.id
                  WHERE dp.penjualan_id = :penjualan_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':penjualan_id', $penjualan_id);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalPenjualanHari() {
        $query = "SELECT COALESCE(SUM(total_harga), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE DATE(tanggal_penjualan) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTotalPenjualanBulan() {
        $query = "SELECT COALESCE(SUM(total_harga), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE MONTH(tanggal_penjualan) = MONTH(CURDATE()) 
                  AND YEAR(tanggal_penjualan) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTotalTransaksiHari() {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE DATE(tanggal_penjualan) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getLaporanPenjualan($start_date, $end_date) {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE DATE(p.tanggal_penjualan) BETWEEN :start_date AND :end_date
                  ORDER BY p.tanggal_penjualan DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();

        return $stmt;
    }

    public function generateNoTransaksi() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE DATE(tanggal_penjualan) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $count = $row['count'] + 1;
        $no_transaksi = 'TRX' . date('Ymd') . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        return $no_transaksi;
    }
}
?>
