<?php
// File: includes/calculation_logic.php

// --- FUNGSI AKTIVARIA DASAR ---

/**
 * Mendapatkan nilai lx dari tabel mortalitas berdasarkan usia.
 * @param string $table_name Nama tabel mortalitas
 * @param int $age Usia
 * @param object $conn Koneksi database
 * @return float|null Nilai lx atau null jika tidak ditemukan
 */
function get_lx($table_name, $age, $conn) {
    $stmt = $conn->prepare("SELECT lx FROM " . $table_name . " WHERE usia = ?");
    if (!$stmt) {
        error_log("Prepare failed for get_lx: " . $conn->error);
        return null;
    }
    $stmt->bind_param("i", $age);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? (float)$row['lx'] : null;
}

/**
 * Menghitung faktor diskon.
 * @param float $i Tingkat suku bunga (desimal)
 * @param int $n Jangka waktu
 * @return float
 */
function calculate_v_n($i, $n) {
    if ($n == 0) return 1;
    return pow(1 + $i, -$n);
}

// --- SIMBOL KOMUTASI AKTUARIA ---
$w = 110; // Usia maksimum

/**
 * Menghitung nilai Dx = v^x * lx
 */
function calculate_Dx($x, $i, $table_name, $conn) {
    $lx = get_lx($table_name, $x, $conn);
    if ($lx === null) return null;
    $v_x = calculate_v_n($i, $x);
    return $v_x * $lx;
}

/**
 * Menghitung nilai Nx = SUM(Dx+i) for i=0 to (w-x)
 */
function calculate_Nx($x, $i, $table_name, $conn) {
    global $w;
    $sum = 0;
    for ($k = $x; $k <= $w; $k++) {
        $dx_k = calculate_Dx($k, $i, $table_name, $conn);
        if ($dx_k === null) break;
        $sum += $dx_k;
    }
    return $sum;
}

/**
 * Menghitung nilai Cx = v^(x+1) * dx
 */
function calculate_Cx($x, $i, $table_name, $conn) {
    $lx = get_lx($table_name, $x, $conn);
    $lx_plus_1 = get_lx($table_name, $x + 1, $conn);
    if ($lx === null || $lx_plus_1 === null) return null;
    $dx = $lx - $lx_plus_1;
    $v_x_plus_1 = calculate_v_n($i, $x + 1);
    return $v_x_plus_1 * $dx;
}

/**
 * Menghitung nilai Mx = SUM(Cx+i) for i=0 to (w-x)
 */
function calculate_Mx($x, $i, $table_name, $conn) {
    global $w;
    $sum = 0;
    for ($k = $x; $k <= $w; $k++) {
        $cx_k = calculate_Cx($k, $i, $table_name, $conn);
        if ($cx_k === null) break;
        $sum += $cx_k;
    }
    return $sum;
}

// --- FUNGSI ANUITAS DAN PREMI ---
function calculate_ax($x, $i, $table_name, $conn) {
    $nx_plus_1 = calculate_Nx($x + 1, $i, $table_name, $conn);
    $dx = calculate_Dx($x, $i, $table_name, $conn);
    return ($dx > 0) ? $nx_plus_1 / $dx : 0;
}
function calculate_äx($x, $i, $table_name, $conn) {
    return 1 + calculate_ax($x, $i, $table_name, $conn);
}
function calculate_äx_n($x, $n, $i, $table_name, $conn) {
    $nx = calculate_Nx($x, $i, $table_name, $conn);
    $nx_plus_n = calculate_Nx($x + $n, $i, $table_name, $conn);
    $dx = calculate_Dx($x, $i, $table_name, $conn);
    return ($dx > 0) ? ($nx - $nx_plus_n) / $dx : 0;
}
function calculate_Ex_n($x, $n, $i, $table_name, $conn) {
    $dx_plus_n = calculate_Dx($x + $n, $i, $table_name, $conn);
    $dx = calculate_Dx($x, $i, $table_name, $conn);
    return ($dx > 0) ? $dx_plus_n / $dx : 0;
}
function calculate_annuity($age, $i, $table_name, $conn) {
    global $w;
    $annuity = 0;
    $lx_at_age = get_lx($table_name, $age, $conn);
    if ($lx_at_age === null || $lx_at_age === 0) return 0;
    for ($k = 0; $k <= ($w - $age); $k++) {
        $v_power_k = calculate_v_n($i, $k);
        $lx_plus_k = get_lx($table_name, $age + $k, $conn);
        if ($lx_plus_k !== null) {
            $annuity += $v_power_k * ($lx_plus_k / $lx_at_age);
        } else {
            break;
        }
    }
    return $annuity;
}

// --- FUNGSI PERHITUNGAN DAPEN ---
function calculate_gaji_tahunan($gaji_awal, $persen_kenaikan, $usia_kerja_awal, $usia_pensiun) {
    $gaji_tahunan_arr = [];
    $gaji_saat_ini_per_bulan = $gaji_awal;
    for ($usia = $usia_kerja_awal; $usia <= $usia_pensiun; $usia++) {
        $gaji_tahunan_arr[$usia] = $gaji_saat_ini_per_bulan * 12;
        $gaji_saat_ini_per_bulan *= (1 + $persen_kenaikan);
    }
    return $gaji_tahunan_arr;
}
function calculate_pvfs_aan($e, $r, $i, $table_name, $conn) {
    $nx_e = calculate_Nx($e, $i, $table_name, $conn);
    $nx_r = calculate_Nx($r, $i, $table_name, $conn);
    $dx_e = calculate_Dx($e, $i, $table_name, $conn);
    return ($dx_e > 0) ? ($nx_e - $nx_r) / $dx_e : 0;
}
function calculate_pvfb_aan($br, $e, $r, $i, $table_name, $conn) {
    global $w;
    $äx_r_n = calculate_äx_n($r, $w - $r, $i, $table_name, $conn);
    $Ex_e_r = calculate_Ex_n($e, $r - $e, $i, $table_name, $conn);
    return $br * $äx_r_n * $Ex_e_r;
}

/**
 * Implementasi Metode EAN.
 */
function calculate_dapen_EAN($dapen_data, $assumption_data, $conn) {
    $results = [];
    $gaji_awal = (float)$dapen_data['gaji_awal_per_bulan'];
    $kenaikan_gaji = (float)$dapen_data['persentase_kenaikan_gaji'] / 100;
    $usia_kerja_awal = (int)$dapen_data['usia_kerja_pertama'];
    $usia_saat_ini = (int)$dapen_data['usia_saat_ini'];
    $usia_pensiun = (int)$dapen_data['usia_pensiun'];
    $i = (float)$assumption_data['tingkat_suku_bunga'];
    $table_kerja = $assumption_data['mortalita_usia_kerja'];
    $table_pensiun = $assumption_data['mortalita_usia_pensiun'];
    $proporsi_manfaat_pensiun = (float)$assumption_data['proporsi_manfaat_pensiun'] / 100;
    $besar_manfaat_pensiun_persen = (float)$assumption_data['besar_manfaat_pensiun_persen'] / 100;

    $gaji_tahunan_arr = calculate_gaji_tahunan($gaji_awal, $kenaikan_gaji, $usia_kerja_awal, $usia_pensiun);
    $gaji_terakhir = end($gaji_tahunan_arr);
    $manfaat_pensiun_final = $gaji_terakhir * $besar_manfaat_pensiun_persen;
     
    $dx_r_pensiun = calculate_Dx($usia_pensiun, $i, $table_pensiun, $conn);
    $nx_r_plus_1_pensiun = calculate_Nx($usia_pensiun + 1, $i, $table_pensiun, $conn);
    $annuity_pensiun = ($dx_r_pensiun > 0) ? $nx_r_plus_1_pensiun / $dx_r_pensiun : 0;
    $pvfb_at_r = $manfaat_pensiun_final * $annuity_pensiun;

    $nx_e_kerja = calculate_Nx($usia_kerja_awal, $i, $table_kerja, $conn);
    $nx_r_kerja = calculate_Nx($usia_pensiun, $i, $table_kerja, $conn);
    $dx_e_kerja = calculate_Dx($usia_kerja_awal, $i, $table_kerja, $conn);
    $pvfs = ($dx_e_kerja > 0) ? ($nx_e_kerja - $nx_r_kerja) / $dx_e_kerja : 0;

    $nc_rate = ($pvfs > 0) ? $pvfb_at_r / $pvfs : 0;
     
    $pvfb_arr = [];
    $al_arr = [];
    $total_iuran_normal_akumulasi = 0;
    $iuran_normal_bulanan = 0;
    $iuran_normal_tahunan = 0;

    $gaji_saat_ini_per_bulan = $gaji_awal;
    for ($usia = $usia_kerja_awal; $usia <= $usia_pensiun; $usia++) {
        $gaji_tahunan = $gaji_saat_ini_per_bulan * 12;

        $v_power_usia = calculate_v_n($i, $usia - $usia_kerja_awal);
        $lx_usia_kerja = get_lx($table_kerja, $usia, $conn);
        $l0 = (float)$assumption_data['l0'];
        $pvfb_per_tahun = $manfaat_pensiun_final * ($lx_usia_kerja / $l0) * $v_power_usia;
        $pvfb_arr[$usia] = $pvfb_per_tahun;

        $nc_tahunan_x = $gaji_tahunan * $nc_rate;
        $nc_bulanan_x = $nc_tahunan_x / 12;

        if ($usia == $usia_saat_ini) {
            $iuran_normal_bulanan = $nc_bulanan_x;
            $iuran_normal_tahunan = $nc_tahunan_x;
        }
        if ($usia >= $usia_saat_ini) {
            $total_iuran_normal_akumulasi += $nc_tahunan_x;
        }

        $n_x_e = $usia - $usia_kerja_awal;
        $n_r_e = $usia_pensiun - $usia_kerja_awal;
        $äe_x_e = calculate_äx_n($usia_kerja_awal, $n_x_e, $i, $table_kerja, $conn);
        $äe_r_e = calculate_äx_n($usia_kerja_awal, $n_r_e, $i, $table_kerja, $conn);
        $al_x = ($äe_r_e > 0) ? ($äe_x_e / $äe_r_e) * $pvfb_at_r : 0;
        $al_arr[$usia] = $al_x;
         
        $gaji_saat_ini_per_bulan *= (1 + $kenaikan_gaji);
    }
     
    $results = [
        'total_gaji_per_bulan' => calculate_gaji_tahunan($gaji_awal, $kenaikan_gaji, $usia_kerja_awal, $usia_pensiun),
        'total_gaji_per_tahun' => $gaji_tahunan_arr,
        'present_value_future_benefit' => $pvfb_arr,
        'iuran_normal_bulanan' => $iuran_normal_bulanan,
        'iuran_normal_tahunan' => $iuran_normal_tahunan,
        'iuran_normal_total' => $total_iuran_normal_akumulasi,
        'kewajiban_aktuaria' => $al_arr,
        'besar_manfaat_pensiun_persen_val' => $manfaat_pensiun_final * $proporsi_manfaat_pensiun,
        'besar_manfaat_pensiun_100_val' => $manfaat_pensiun_final,
    ];

    return $results;
}


/**
 * Implementasi Metode AAN (Actuarial Accrued Normal).
 */
function calculate_dapen_AAN($dapen_data, $assumption_data, $conn) {
    // Ambil data input dan asumsi
    $gaji_awal = (float)$dapen_data['gaji_awal_per_bulan'];
    $kenaikan_gaji = (float)$dapen_data['persentase_kenaikan_gaji'] / 100;
    $usia_kerja_awal = (int)$dapen_data['usia_kerja_pertama'];
    $usia_saat_ini = (int)$dapen_data['usia_saat_ini'];
    $usia_pensiun = (int)$dapen_data['usia_pensiun'];
    $i = (float)$assumption_data['tingkat_suku_bunga'];
    $table_kerja = $assumption_data['mortalita_usia_kerja'];
    $table_pensiun = $assumption_data['mortalita_usia_pensiun'];
    $proporsi_manfaat_pensiun = (float)$assumption_data['proporsi_manfaat_pensiun'] / 100;
    $besar_manfaat_pensiun_persen = (float)$assumption_data['besar_manfaat_pensiun_persen'] / 100;
     
    $gaji_tahunan_arr = calculate_gaji_tahunan($gaji_awal, $kenaikan_gaji, $usia_kerja_awal, $usia_pensiun);
    $gaji_terakhir = end($gaji_tahunan_arr);
    $manfaat_pensiun_final = $gaji_terakhir * $besar_manfaat_pensiun_persen;

    $dx_r_pensiun = calculate_Dx($usia_pensiun, $i, $table_pensiun, $conn);
    $nx_r_plus_1_pensiun = calculate_Nx($usia_pensiun + 1, $i, $table_pensiun, $conn);
    $annuity_pensiun = ($dx_r_pensiun > 0) ? $nx_r_plus_1_pensiun / $dx_r_pensiun : 0;
    $pvfb_at_r = $manfaat_pensiun_final * $annuity_pensiun;

    $pvfs = calculate_pvfs_aan($usia_kerja_awal, $usia_pensiun, $i, $table_kerja, $conn);
     
    $nc_rate = ($pvfs > 0) ? $pvfb_at_r / $pvfs : 0;
     
    $pvfb_arr = [];
    $al_arr = [];
    $total_iuran_normal_akumulasi = 0;
    $iuran_normal_bulanan = 0;
    $iuran_normal_tahunan = 0;

    $gaji_saat_ini_per_bulan = $gaji_awal;
    for ($usia = $usia_kerja_awal; $usia <= $usia_pensiun; $usia++) {
        $gaji_tahunan = $gaji_saat_ini_per_bulan * 12;
        $gaji_tahunan_arr[$usia] = $gaji_tahunan;

        $v_power_usia = calculate_v_n($i, $usia - $usia_kerja_awal);
        $lx_usia_kerja = get_lx($table_kerja, $usia, $conn);
        $l0 = (float)$assumption_data['l0'];
        $pvfb_per_tahun = $manfaat_pensiun_final * ($lx_usia_kerja / $l0) * $v_power_usia;
        $pvfb_arr[$usia] = $pvfb_per_tahun;
         
        $nc_tahunan_x = $gaji_tahunan * $nc_rate;
        $nc_bulanan_x = $nc_tahunan_x / 12;

        if ($usia == $usia_saat_ini) {
            $iuran_normal_bulanan = $nc_bulanan_x;
            $iuran_normal_tahunan = $nc_tahunan_x;
        }
        if ($usia >= $usia_saat_ini) {
            $total_iuran_normal_akumulasi += $nc_tahunan_x;
        }

        $n_x_e = $usia - $usia_kerja_awal;
        $n_r_e = $usia_pensiun - $usia_kerja_awal;
        $äe_x_e = calculate_äx_n($usia_kerja_awal, $n_x_e, $i, $table_kerja, $conn);
        $äe_r_e = calculate_äx_n($usia_kerja_awal, $n_r_e, $i, $table_kerja, $conn);
        $al_x = ($äe_r_e > 0) ? ($äe_x_e / $äe_r_e) * $pvfb_at_r : 0;
        $al_arr[$usia] = $al_x;
         
        $gaji_saat_ini_per_bulan *= (1 + $kenaikan_gaji);
    }
     
    $results = [
        'total_gaji_per_bulan' => calculate_gaji_tahunan($gaji_awal, $kenaikan_gaji, $usia_kerja_awal, $usia_pensiun),
        'total_gaji_per_tahun' => $gaji_tahunan_arr,
        'present_value_future_benefit' => $pvfb_arr,
        'iuran_normal_bulanan' => $iuran_normal_bulanan,
        'iuran_normal_tahunan' => $iuran_normal_tahunan,
        'iuran_normal_total' => $total_iuran_normal_akumulasi,
        'kewajiban_aktuaria' => $al_arr,
        'besar_manfaat_pensiun_persen_val' => $manfaat_pensiun_final * $proporsi_manfaat_pensiun,
        'besar_manfaat_pensiun_100_val' => $manfaat_pensiun_final,
    ];

    return $results;
}


/**
 * Implementasi Metode PUC (Projected Unit Credit).
 */
function calculate_dapen_PUC($dapen_data, $assumption_data, $conn) {
    // Ambil data input dan asumsi
    $gaji_awal = (float)$dapen_data['gaji_awal_per_bulan'];
    $kenaikan_gaji = (float)$dapen_data['persentase_kenaikan_gaji'] / 100;
    $usia_kerja_awal = (int)$dapen_data['usia_kerja_pertama'];
    $usia_saat_ini = (int)$dapen_data['usia_saat_ini'];
    $usia_pensiun = (int)$dapen_data['usia_pensiun'];
    $i = (float)$assumption_data['tingkat_suku_bunga'];
    $table_kerja = $assumption_data['mortalita_usia_kerja'];
    $table_pensiun = $assumption_data['mortalita_usia_pensiun'];
    $proporsi_manfaat_pensiun = (float)$assumption_data['proporsi_manfaat_pensiun'] / 100;
    $besar_manfaat_pensiun_persen = (float)$assumption_data['besar_manfaat_pensiun_persen'] / 100;

    $results = [];
    $usia_maks = 110;

    // Menghitung Manfaat Pensiun (Br)
    $gaji_tahunan_arr = calculate_gaji_tahunan($gaji_awal, $kenaikan_gaji, $usia_kerja_awal, $usia_pensiun);
    $gaji_terakhir = end($gaji_tahunan_arr);
    $manfaat_pensiun_final = $gaji_terakhir * $besar_manfaat_pensiun_persen;

    // Menghitung PVFB dan PVFS menggunakan rumus komutasi
    $dx_r_pensiun = calculate_Dx($usia_pensiun, $i, $table_pensiun, $conn);
    $nx_r_plus_1_pensiun = calculate_Nx($usia_pensiun + 1, $i, $table_pensiun, $conn);
    $annuity_pensiun = ($dx_r_pensiun > 0) ? $nx_r_plus_1_pensiun / $dx_r_pensiun : 0;
    $pvfb_at_r = $manfaat_pensiun_final * $annuity_pensiun;

    // Iuran Normal (NC)
    $tahun_masa_kerja_total = $usia_pensiun - $usia_kerja_awal;
    $nc_tahunan = ($tahun_masa_kerja_total > 0) ? $pvfb_at_r / $tahun_masa_kerja_total : 0;
    $nc_bulanan = $nc_tahunan / 12;

    $pvfb_arr = [];
    $al_arr = [];
    $total_iuran_normal_akumulasi = 0;

    $gaji_saat_ini_per_bulan = $gaji_awal;
    for ($usia = $usia_kerja_awal; $usia <= $usia_pensiun; $usia++) {
        $gaji_tahunan = $gaji_saat_ini_per_bulan * 12;
        $gaji_tahunan_arr[$usia] = $gaji_tahunan;

        $v_power_usia = calculate_v_n($i, $usia - $usia_kerja_awal);
        $lx_usia_kerja = get_lx($table_kerja, $usia, $conn);
        $l0 = (float)$assumption_data['l0'];
        $pvfb_per_tahun = $manfaat_pensiun_final * ($lx_usia_kerja / $l0) * $v_power_usia;
        $pvfb_arr[$usia] = $pvfb_per_tahun;

        if ($usia >= $usia_saat_ini) {
            $total_iuran_normal_akumulasi += $nc_tahunan;
        }

        // Kewajiban Aktuaria (AL)
        $tahun_masa_kerja_saat_ini = $usia - $usia_kerja_awal;
        $al_x = ($tahun_masa_kerja_total > 0) ? ($tahun_masa_kerja_saat_ini / $tahun_masa_kerja_total) * $pvfb_at_r : 0;
        $al_arr[$usia] = $al_x;

        $gaji_saat_ini_per_bulan *= (1 + $kenaikan_gaji);
    }

    $results = [
        'total_gaji_per_bulan' => calculate_gaji_tahunan($gaji_awal, $kenaikan_gaji, $usia_kerja_awal, $usia_pensiun),
        'total_gaji_per_tahun' => $gaji_tahunan_arr,
        'present_value_future_benefit' => $pvfb_arr,
        'iuran_normal_bulanan' => $nc_bulanan,
        'iuran_normal_tahunan' => $nc_tahunan,
        'iuran_normal_total' => $total_iuran_normal_akumulasi,
        'kewajiban_aktuaria' => $al_arr,
        'besar_manfaat_pensiun_persen_val' => $manfaat_pensiun_final * $proporsi_manfaat_pensiun,
        'besar_manfaat_pensiun_100_val' => $manfaat_pensiun_final,
    ];

    return $results;
}


/**
 * Implementasi Metode ILP (Individual Level Premium).
 */
function calculate_dapen_ILP($dapen_data, $assumption_data, $conn) {
    // Ambil data input dan asumsi
    $gaji_awal = (float)$dapen_data['gaji_awal_per_bulan'];
    $kenaikan_gaji = (float)$dapen_data['persentase_kenaikan_gaji'] / 100;
    $usia_kerja_awal = (int)$dapen_data['usia_kerja_pertama'];
    $usia_saat_ini = (int)$dapen_data['usia_saat_ini'];
    $usia_pensiun = (int)$dapen_data['usia_pensiun'];
    $i = (float)$assumption_data['tingkat_suku_bunga'];
    $table_kerja = $assumption_data['mortalita_usia_kerja'];
    $table_pensiun = $assumption_data['mortalita_usia_pensiun'];
    $proporsi_manfaat_pensiun = (float)$assumption_data['proporsi_manfaat_pensiun'] / 100;
    $besar_manfaat_pensiun_persen = (float)$assumption_data['besar_manfaat_pensiun_persen'] / 100;

    $results = [];
    $usia_maks = 110;

    // Menghitung Manfaat Pensiun (Br)
    $gaji_tahunan_arr = calculate_gaji_tahunan($gaji_awal, $kenaikan_gaji, $usia_kerja_awal, $usia_pensiun);
    $gaji_terakhir = end($gaji_tahunan_arr);
    $manfaat_pensiun_final = $gaji_terakhir * $besar_manfaat_pensiun_persen;

    // Menghitung PVFB dan PVFS menggunakan rumus komutasi
    $dx_r_pensiun = calculate_Dx($usia_pensiun, $i, $table_pensiun, $conn);
    $nx_r_plus_1_pensiun = calculate_Nx($usia_pensiun + 1, $i, $table_pensiun, $conn);
    $annuity_pensiun = ($dx_r_pensiun > 0) ? $nx_r_plus_1_pensiun / $dx_r_pensiun : 0;
    $pvfb_at_r = $manfaat_pensiun_final * $annuity_pensiun;

    // Iuran Normal (NC)
    $dx_r_kerja = calculate_Dx($usia_pensiun, $i, $table_kerja, $conn);
    $nx_e_kerja = calculate_Nx($usia_kerja_awal, $i, $table_kerja, $conn);
    $nx_r_kerja = calculate_Nx($usia_pensiun, $i, $table_kerja, $conn);
    $nc_ilpr = ($nx_e_kerja - $nx_r_kerja > 0) ? ($manfaat_pensiun_final * $annuity_pensiun * $dx_r_kerja) / ($nx_e_kerja - $nx_r_kerja) : 0;
    $nc_tahunan = $nc_ilpr; // NC ILP adalah premi tahunan level
    $nc_bulanan = $nc_tahunan / 12;

    $pvfb_arr = [];
    $al_arr = [];
    $total_iuran_normal_akumulasi = 0;

    $gaji_saat_ini_per_bulan = $gaji_awal;
    for ($usia = $usia_kerja_awal; $usia <= $usia_pensiun; $usia++) {
        $gaji_tahunan = $gaji_saat_ini_per_bulan * 12;
        $gaji_tahunan_arr[$usia] = $gaji_tahunan;

        $v_power_usia = calculate_v_n($i, $usia - $usia_kerja_awal);
        $lx_usia_kerja = get_lx($table_kerja, $usia, $conn);
        $l0 = (float)$assumption_data['l0'];
        $pvfb_per_tahun = $manfaat_pensiun_final * ($lx_usia_kerja / $l0) * $v_power_usia;
        $pvfb_arr[$usia] = $pvfb_per_tahun;
         
        if ($usia >= $usia_saat_ini) {
            $total_iuran_normal_akumulasi += $nc_tahunan;
        }

        // Kewajiban Aktuaria (AL)
        $nx_x_kerja = calculate_Nx($usia, $i, $table_kerja, $conn);
        $dx_x_kerja = calculate_Dx($usia, $i, $table_kerja, $conn);
        $al_x = ($dx_x_kerja > 0) ? $nc_ilpr * ($nx_e_kerja - $nx_x_kerja) / $dx_x_kerja : 0;
        $al_arr[$usia] = $al_x;

        $gaji_saat_ini_per_bulan *= (1 + $kenaikan_gaji);
    }
     
    $results = [
        'total_gaji_per_bulan' => calculate_gaji_tahunan($gaji_awal, $kenaikan_gaji, $usia_kerja_awal, $usia_pensiun),
        'total_gaji_per_tahun' => $gaji_tahunan_arr,
        'present_value_future_benefit' => $pvfb_arr,
        'iuran_normal_bulanan' => $nc_bulanan,
        'iuran_normal_tahunan' => $nc_tahunan,
        'iuran_normal_total' => $total_iuran_normal_akumulasi,
        'kewajiban_aktuaria' => $al_arr,
        'besar_manfaat_pensiun_persen_val' => $manfaat_pensiun_final * $proporsi_manfaat_pensiun,
        'besar_manfaat_pensiun_100_val' => $manfaat_pensiun_final,
    ];

    return $results;
}

// --- FUNGSI PERHITUNGAN ASURANSI JIWA (ASJI) ---

/**
 * Menghitung Premi Tunggal Bersih untuk Asuransi Jiwa Seumur Hidup
 * Rumus: Ax = Mx / Dx
 */
function calculate_Ax_lifetime($x, $i, $table_name, $conn) {
    $mx = calculate_Mx($x, $i, $table_name, $conn);
    $dx = calculate_Dx($x, $i, $table_name, $conn);
    return ($dx > 0) ? $mx / $dx : 0;
}

/**
 * Menghitung Premi Tunggal Bersih untuk Asuransi Jiwa Berjangka (n tahun)
 * Rumus: Âx:n = (Mx - Mx+n) / Dx
 */
function calculate_Ax_term($x, $n, $i, $table_name, $conn) {
    $mx = calculate_Mx($x, $i, $table_name, $conn);
    $mx_plus_n = calculate_Mx($x + $n, $i, $table_name, $conn);
    $dx = calculate_Dx($x, $i, $table_name, $conn);
    return ($dx > 0) ? ($mx - $mx_plus_n) / $dx : 0;
}

/**
 * Menghitung Premi Tunggal Bersih untuk Asuransi Jiwa Dwiguna (n tahun)
 * Rumus: Ax:n = (Mx - Mx+n + Dx+n) / Dx
 */
function calculate_Ax_endowment($x, $n, $i, $table_name, $conn) {
    $mx = calculate_Mx($x, $i, $table_name, $conn);
    $mx_plus_n = calculate_Mx($x + $n, $i, $table_name, $conn);
    $dx_plus_n = calculate_Dx($x + $n, $i, $table_name, $conn);
    $dx = calculate_Dx($x, $i, $table_name, $conn);
    return ($dx > 0) ? ($mx - $mx_plus_n + $dx_plus_n) / $dx : 0;
}

/**
 * Fungsi utama untuk perhitungan Asuransi Jiwa
 */
function calculate_asji($asji_data, $assumption_data, $conn) {
    global $w;
    $results = [];
    $jenis_asji = $asji_data['jenis_asji'];
    $usia = (int)$asji_data['usia_saat_ini'];
    $jangka_waktu = (int)$asji_data['jangka_waktu'];
    $besar_santunan = (float)$asji_data['besar_santunan'];
    $freq_pembayaran = (int)$asji_data['jumlah_pembayaran_setahun'];
    $metode_pembayaran = $asji_data['metode_pembayaran'];
    $i = (float)$assumption_data['tingkat_suku_bunga'];
    $table_mortalita = $assumption_data['mortalita_usia_kerja'];

    $premi_tunggal = 0;
    if ($jenis_asji == 'Seumur Hidup') {
        $premi_tunggal = calculate_Ax_lifetime($usia, $i, $table_mortalita, $conn);
    } elseif ($jenis_asji == 'Berjangka') {
        $premi_tunggal = calculate_Ax_term($usia, $jangka_waktu, $i, $table_mortalita, $conn);
    } elseif ($jenis_asji == 'Dwiguna') {
        $premi_tunggal = calculate_Ax_endowment($usia, $jangka_waktu, $i, $table_mortalita, $conn);
    }

    $äx = calculate_äx($usia, $i, $table_mortalita, $conn);
    $äx_m = $äx + (($freq_pembayaran - 1) / (2 * $freq_pembayaran));
     
    $premi_per_bayar = 0;
    if ($äx_m > 0) {
        $premi_per_bayar = $besar_santunan * $premi_tunggal / ($äx_m * $freq_pembayaran);
    }
     
    $results = [
        'jenis_asji' => $jenis_asji,
        'usia_saat_ini' => $usia,
        'jangka_waktu' => $jangka_waktu,
        'besar_santunan' => $besar_santunan,
        'premi_tunggal' => $premi_tunggal,
        'premi_per_bayar' => $premi_per_bayar,
        'detail_tahunan' => [],
    ];
     
    $jangka_akhir = ($jenis_asji == 'Seumur Hidup') ? $w : $usia + $jangka_waktu;
    for ($k = $usia; $k <= $jangka_akhir; $k++) {
        $current_age = $k;
        $lx = get_lx($table_mortalita, $current_age, $conn);
        $dx = calculate_Dx($current_age, $i, $table_mortalita, $conn);
        $cx = calculate_Cx($current_age, $i, $table_mortalita, $conn);
        $mx = calculate_Mx($current_age, $i, $table_mortalita, $conn);
         
        $results['detail_tahunan'][$current_age] = [
            'lx' => $lx,
            'dx' => $dx,
            'cx' => $cx,
            'mx' => $mx,
        ];
    }

    return $results;
}
?>