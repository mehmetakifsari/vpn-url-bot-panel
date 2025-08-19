<?php
// run_bot.php — VPN + Bot kontrol AJAX endpointi
declare(strict_types=1);
ini_set('default_charset','UTF-8');
header('Content-Type: application/json; charset=UTF-8');
session_start();

if (empty($_SESSION['auth'])) {
    echo json_encode(['error' => 'Yetkisiz erişim']);
    exit;
}

$BASE = '/var/www/proje.amrdanismanlik.com/proje8';
$VPNCTL = $BASE.'/vpnctl.sh';
$VPN_JOB = $BASE.'/vpn_job.py';
$PID_FILE = '/tmp/vpn_job.pid';

$action = $_POST['action'] ?? '';

function run_cmd($cmd) {
    return shell_exec($cmd." 2>&1");
}

if ($action === 'start') {
    if (file_exists($PID_FILE)) {
        echo json_encode(['status'=>'error','msg'=>'Bot zaten çalışıyor.']);
        exit;
    }
    $cmd = "nohup python3 $VPN_JOB > $BASE/vpn_job.log 2>&1 & echo $! > $PID_FILE";
    run_cmd($cmd);
    echo json_encode(['status'=>'ok','msg'=>'Bot başlatıldı.']);
}
elseif ($action === 'stop') {
    if (file_exists($PID_FILE)) {
        $pid = trim(file_get_contents($PID_FILE));
        run_cmd("kill $pid");
        unlink($PID_FILE);
    }
    echo json_encode(['status'=>'ok','msg'=>'Bot durduruldu.']);
}
elseif ($action === 'status') {
    if (file_exists($PID_FILE)) {
        $pid = trim(file_get_contents($PID_FILE));
        $alive = trim(run_cmd("ps -p $pid -o pid="));
        if ($alive) {
            echo json_encode(['status'=>'running','pid'=>$pid]);
        } else {
            unlink($PID_FILE);
            echo json_encode(['status'=>'stopped']);
        }
    } else {
        echo json_encode(['status'=>'stopped']);
    }
}
else {
    echo json_encode(['error'=>'Geçersiz istek']);
}
