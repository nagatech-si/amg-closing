<?php
    $q = file_get_contents('php://input');
    $exp = json_decode($q, true);
    $parameter = $exp['parameter'];
    $data = array();

    //$conn = mysqli_connect("localhost", "root", "berasputih", "db_manufacture");
    $conn = mysqli_connect("nagatech-web.clkz2ekrv9lh.ap-southeast-1.rds.amazonaws.com", "nsi", "berasputih", "db_manufacture");
    
    $sql = "SELECT * FROM tp_system";
    $q_system = mysqli_query($conn, $sql);
    $tglsystem = "";
    if ($q_system->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($q_system)) {
            $tglsystem = $row["tgl_system"];
            $inputDate = date("Y/m/d h:i:s");
        }
    }
    
    $tanggal = $tglsystem;

    $delete = "DELETE FROM tt_saldo_outstand_divisi WHERE tanggal='$tanggal'";
    $conn->query($delete);

    $sql = "SELECT b.asal_divisi,b.kode_jenis_bahan,SUM(b.stock_akhir) AS stock_akhir,SUM(b.berat_akhir) AS berat_akhir,(sum(b.tambahan)+sum(b.batu_admin2)) as tambahan FROM tt_stock_card a INNER JOIN tt_po_job_order b ON (b.no_job_order = a.no_job_order AND b.kode_barang = a.kode_barang AND a.divisi=b.asal_divisi AND b.update_date = a.input_date) WHERE b.asal_divisi=b.tujuan_divisi AND a.status='OPEN' AND b.status_job NOT IN ('DONE','CANC','GB') AND b.asal_divisi IN ('POTONG SPRU','FILLING','FR','FR2','FR3','HANDSETTING1','HANDSETTING2','POLISHING','PLATTING') AND a.type NOT IN ('N') GROUP BY b.asal_divisi,b.kode_jenis_bahan;";
    if ($conn){
    }else{
        $data['pesan'] = "gagal ".mysqli_connect_error();
        $json = json_encode($data);
        echo $json;
        exit;
    }
    
    mysqli_set_charset($conn, 'utf8');
    $query = mysqli_query($conn, $sql);        
    if ($query->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($query)) {
            $divisi = $row["asal_divisi"];
            $kode_jenis_bahan = $row["kode_jenis_bahan"];                
            $stock_akhir = number_format((float)$row["stock_akhir"], 3, '.', '');
            $berat_akhir = number_format((float)$row["berat_akhir"], 0, '.', '');
            $tambahan = number_format((float)$row["tambahan"], 3, '.', '');
            
            $insert = "INSERT INTO tt_saldo_outstand_divisi (tanggal,divisi,kode_jenis_bahan,total_stock,total_berat,total_tambahan,input_date) VALUES ('$tanggal','$divisi','$kode_jenis_bahan','$stock_akhir','$berat_akhir','$tambahan','$inputDate')";
            $conn->query($insert);
        }

        $data['pesan'] = "berhasil";
        $json = json_encode($data);
    } else {
        $data['pesan'] = "gagal";
        $json = json_encode($data);
    }
    echo $json;
    exit;
?>