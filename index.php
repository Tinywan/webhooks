<?php
error_reporting(1);

// 配置 github 需要配置的 Secret
$secret = 'b3K2E1uNq0X9PYIjptyR5clkBe7JwvFASQxrohaDTMLg68CZ'; // 这是是自定义生成的
$userAgent = $_SERVER['HTTP_USER_AGENT'];

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
    exit("非法的请求");
}

fwrite($fs, date("Y-m-d H:i:s") . ': 仓库名称 [' . $data['repository']['full_name'] . ']' . PHP_EOL);
fwrite($fs, date("Y-m-d H:i:s") . ': 提交消息 [' . $data['commits']['message'] . ']' . PHP_EOL);

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



