<?php
class Pakaian {
    private $conn;
    private $table_name = "Pakaian";

    public $id;
    public $kode_pakaian;
    public $nama_pakaian;
    public $kategori_id;
    public $satuan;
    public $harga_modal;
    public $harga_jual;
    public $stok;
    public $stok_minimum;
    public $deskripsi;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                      SET kode_pakaian=:kode_pakaian, nama_pakaian=:nama_pakaian, kategori_id=:kategori_id, 
                      ukuran=:ukuran, warna=:warna,satuan=:satuan, harga_modal=:harga_modal, 
                      harga_jual=:harga_jual, 
                      stok=:stok, stok_minimal=:stok_minimal,
                      deskripsi=:deskripsi";

        $stmt = $this->conn->prepare($query);

        $this->kode_pakaian = htmlspecialchars(strip_tags($this->kode_pakaian));
        $this->nama_pakaian = htmlspecialchars(strip_tags($this->nama_pakaian));
        $this->kategori_id = htmlspecialchars(strip_tags($this->kategori_id));
        $this->ukuran  = htmlspecialchars(strip_tags($this->ukuran));
        $this->warna = htmlspecialchars(strip_tags($this->warna));
        $this->satuan = htmlspecialchars(strip_tags($this->satuan));
        $this->harga_modal = htmlspecialchars(strip_tags($this->harga_modal));
        $this->harga_jual = htmlspecialchars(strip_tags($this->harga_jual));
        $this->stok = htmlspecialchars(strip_tags($this->stok));
        $this->stok_minimal = htmlspecialchars(strip_tags($this->stok_minimal));
        $this->deskripsi = htmlspecialchars(strip_tags($this->deskripsi));

        $stmt->bindParam(':kode_pakaian', $this->kode_pakaian);
        $stmt->bindParam(':nama_pakaian', $this->nama_pakaian);
        $stmt->bindParam(':kategori_id', $this->kategori_id);
        $stmt->bindParam(':ukuran', $this->ukuran);
        $stmt->bindParam(':warna', $this->warna);
        $stmt->bindParam(':satuan', $this->satuan);
        $stmt->bindParam(':harga_modal', $this->harga_modal);
        $stmt->bindParam(':harga_jual', $this->harga_jual);
        $stmt->bindParam(':stok', $this->stok);
        $stmt->bindParam(':stok_minimal', $this->stok_minimal);
        $stmt->bindParam(':deskripsi', $this->deskripsi);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT o.*, k.nama_kategori 
                  FROM " . $this->table_name . " o
                  LEFT JOIN kategori_pakaian k ON o.kategori_id = k.id
                  ORDER BY o.nama_pakaian";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT o.*, k.nama_kategori 
                  FROM " . $this->table_name . " o
                  LEFT JOIN kategori_pakaian k ON o.kategori_id = k.id
                  WHERE o.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->kode_pakaian = $row['kode_pakaian'];
            $this->nama_pakaian = $row['nama_pakaian'];
            $this->kategori_id = $row['kategori_id'];
            $this->ukuran = $row['ukuran'];
            $this->warna = $row['warna'];
            $this->satuan = $row['satuan'];
            $this->harga_modal = $row['harga_modal'];
            $this->harga_jual = $row['harga_jual'];
            $this->stok = $row['stok'];
            $this->stok_minimal = $row['stok_minimal'];
            $this->deskripsi = $row['deskripsi'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET kode_pakaian=:kode_pakaian, nama_pakaian=:nama_pakaian, kategori_id=:kategori_id, 
                      kategori=:kategori, ukuran=:ukuran, warna=:warna, satuan=:satuan, harga_modal=:harga_modal, harga_jual=:harga_jual, 
                      stok=:stok, stok_minimal=:stok_minimal, 
                      deskripsi=:deskripsi
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->kode_pakaian = htmlspecialchars(strip_tags($this->kode_pakaian));
        $this->nama_pakaian = htmlspecialchars(strip_tags($this->nama_pakaian));
        $this->kategori_id = htmlspecialchars(strip_tags($this->kategori_id));
        $this->satuan = htmlspecialchars(strip_tags($this->satuan));
        $this->ukuran = htmlspecialchars(strip_tags($this->ukuran));
        $this->warna = htmlspecialchars(strip_tags($this->warna));
        $this->harga_modal = htmlspecialchars(strip_tags($this->harga_modal));
        $this->harga_jual = htmlspecialchars(strip_tags($this->harga_jual));
        $this->stok = htmlspecialchars(strip_tags($this->stok));
        $this->stok_minimal = htmlspecialchars(strip_tags($this->stok_minimal));
        $this->deskripsi = htmlspecialchars(strip_tags($this->deskripsi));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':kode_pakaian', $this->kode_pakaian);
        $stmt->bindParam(':nama_pakaian', $this->nama_pakaian);
        $stmt->bindParam(':kategori_id', $this->kategori_id);
        $stmt->bindParam(':satuan', $this->satuan);
        $stmt->bindParam(':ukuran', $this->ukuran);
        $stmt->bindParam(':warna', $this->warna);
        $stmt->bindParam(':harga_modal', $this->harga_modal);
        $stmt->bindParam(':harga_jual', $this->harga_jual);
        $stmt->bindParam(':stok', $this->stok);
        $stmt->bindParam(':stok_minimal', $this->stok_minimal);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateStok($pakaian_id, $jumlah) {
        $query = "UPDATE " . $this->table_name . " SET stok = stok + :jumlah WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jumlah', $jumlah);
        $stmt->bindParam(':id', $pakaian_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getTotalpakaian() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }


    public function getStokMinimal() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE stok <= stok_minimal";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function search($keyword) {
        $query = "SELECT o.*, k.nama_kategori 
                  FROM " . $this->table_name . " o
                  LEFT JOIN kategori_pakaian k ON o.kategori_id = k.id
                  WHERE o.nama_pakaian LIKE :keyword OR o.kode_pakaian LIKE :keyword
                  ORDER BY o.nama_pakaian";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();

        return $stmt;
    }
}
?>
