<?php
error_reporting(1);

// 配置 github 需要配置的 Secret
$secret = '1989BC88338CB4DABEF20BD7C54FD0D6';

$userAgent = $_SERVER['HTTP_USER_AGENT'];

// 默认签名字符串
//$signature = 'sha1=e0ec9317f440f3fd47631852ef585c6b2680e8f8';

// 匹配 Github 钩子事件
if (substr_count($userAgent, 'GitHub') >= 1) {
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
// 匹配 Coding 钩子事件
} elseif (substr_count($userAgent, 'Coding') >= 1) {
    $signature = $_SERVER['HTTP_X_CODING_SIGNATURE'];
}

list($hash_type, $hash_value) = explode('=', $signature, 2);
$jsonContent = file_get_contents("php://input");
$checkHash = hash_hmac($hash_type, $jsonContent, $secret); // e0ec9317f440f3fd47631852ef585c6b2680e8f8

$fs = fopen('./auto_hook.log', 'a');
$data = json_decode($jsonContent, true);
if(empty($data)){
    exit("错误的请求");
}

fwrite($fs, date("Y-m-d H:i:s") . ': 当前仓库名称 [' . $data['pusher']['name'] . ']' . PHP_EOL);

// sha1 验证
if ($checkHash && $checkHash === $hash_value) {
    fwrite($fs, date('Y-m-d H:i:s').': '.' 认证成功，开始交付... ' . PHP_EOL);
    $repository = $data['repository']['name'];

    $pwd = getcwd();
    $command = 'cd .. && cd ' . $repository . ' && git pull';
    fwrite($fs, date('Y-m-d H:i:s').': '. ' $> ' . $command . PHP_EOL);

    if (!empty($repository)) {
        shell_exec($command);
        fwrite($fs, date('Y-m-d H:i:s').':  '.$repository . ' 交付完成 ' . PHP_EOL);
    }
    $fs and fclose($fs);
}



