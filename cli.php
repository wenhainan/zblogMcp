<?php
/**
 * zblogMcp 命令行工具
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Access denied');
}

$zblogmcp_cli_usage = <<<TEXT
用法：
  php zb_users/plugin/zblogMcp/cli.php add-key --name="名称" [选项]

选项：
  --key="ApiKey"       自定义 ApiKey，默认自动生成
  --scopes="read,write" 权限范围：read、write、category、tag、media、comment，默认 all
  --remark="备注"      可选备注

示例：
  php zb_users/plugin/zblogMcp/cli.php add-key --name="自动化客户端" --scopes="read,write"
TEXT;

function zblogmcp_cli_fail($message)
{
    fwrite(STDERR, '错误：' . $message . PHP_EOL);
    exit(1);
}

if ($argc < 2) {
    fwrite(STDERR, $zblogmcp_cli_usage . PHP_EOL);
    exit(1);
}

if (in_array($argv[1], array('help', '--help', '-h'), true)) {
    fwrite(STDOUT, $zblogmcp_cli_usage . PHP_EOL);
    exit(0);
}

if ($argv[1] !== 'add-key') {
    zblogmcp_cli_fail('未知命令，仅支持 add-key');
}

$zblogmcp_cli_options = array();
$zblogmcp_cli_allowed_options = array('name', 'key', 'scopes', 'remark');
for ($i = 2; $i < $argc; $i++) {
    if ($argv[$i] === '--help') {
        fwrite(STDOUT, $zblogmcp_cli_usage . PHP_EOL);
        exit(0);
    }
    if (!preg_match('/^--([a-z-]+)=(.*)$/', $argv[$i], $matches)) {
        zblogmcp_cli_fail('参数格式错误：' . $argv[$i] . '，请使用 --选项=值');
    }
    if (!in_array($matches[1], $zblogmcp_cli_allowed_options, true)) {
        zblogmcp_cli_fail('未知选项：--' . $matches[1]);
    }
    $zblogmcp_cli_options[$matches[1]] = $matches[2];
}

$zblogmcp_cli_name = isset($zblogmcp_cli_options['name']) ? trim($zblogmcp_cli_options['name']) : '';
if ($zblogmcp_cli_name === '') {
    zblogmcp_cli_fail('缺少必填选项 --name');
}
if (mb_strlen($zblogmcp_cli_name) > 100) {
    zblogmcp_cli_fail('名称不能超过 100 个字符');
}

$zblogmcp_cli_remark = isset($zblogmcp_cli_options['remark']) ? trim($zblogmcp_cli_options['remark']) : '';
if (mb_strlen($zblogmcp_cli_remark) > 500) {
    zblogmcp_cli_fail('备注不能超过 500 个字符');
}

$zblogmcp_cli_all_scopes = array('read', 'write', 'category', 'tag', 'media', 'comment');
$zblogmcp_cli_scope_option = isset($zblogmcp_cli_options['scopes']) ? trim($zblogmcp_cli_options['scopes']) : 'all';
if ($zblogmcp_cli_scope_option === 'all') {
    $zblogmcp_cli_selected_scopes = $zblogmcp_cli_all_scopes;
} else {
    $zblogmcp_cli_selected_scopes = array_unique(array_filter(array_map('trim', explode(',', $zblogmcp_cli_scope_option))));
    $zblogmcp_cli_invalid_scopes = array_diff($zblogmcp_cli_selected_scopes, $zblogmcp_cli_all_scopes);
    if (empty($zblogmcp_cli_selected_scopes) || !empty($zblogmcp_cli_invalid_scopes)) {
        zblogmcp_cli_fail('权限范围无效，可选值为 all、read、write、category、tag、media、comment');
    }
}

try {
    $zblogmcp_cli_root = dirname(dirname(dirname(__DIR__)));
    require $zblogmcp_cli_root . '/zb_system/function/c_system_base.php';
    $zbp->Load();
    require_once __DIR__ . '/include.php';

    if (!$zbp->CheckPlugin('zblogMcp')) {
        zblogmcp_cli_fail('zblogMcp 插件未启用');
    }
    if (!$zbp->db->ExistTable($zbp->table['zblogmcp_key'])) {
        zblogmcp_cli_fail('ApiKey 数据表不存在，请先在后台重新启用插件');
    }

    if (array_key_exists('key', $zblogmcp_cli_options)) {
        $zblogmcp_cli_api_key = trim($zblogmcp_cli_options['key']);
        if ($zblogmcp_cli_api_key === '') {
            zblogmcp_cli_fail('自定义 ApiKey 不能为空');
        }
    } else {
        $zblogmcp_cli_api_key = zblogmcp_generate_key();
    }
    if (strlen($zblogmcp_cli_api_key) > 500) {
        zblogmcp_cli_fail('ApiKey 不能超过 500 个字符');
    }

    $zblogmcp_cli_existing = $zbp->db->sql->get()
        ->select($zbp->table['zblogmcp_key'])
        ->where('=', 'api_key', $zblogmcp_cli_api_key)
        ->query;
    if (!empty($zblogmcp_cli_existing)) {
        zblogmcp_cli_fail('ApiKey 已存在');
    }

    $zblogmcp_cli_param = array(
        'name' => $zblogmcp_cli_name,
        'api_key' => $zblogmcp_cli_api_key,
        'status' => 1,
        'remark' => $zblogmcp_cli_remark,
    );
    foreach ($zblogmcp_cli_all_scopes as $scope) {
        $zblogmcp_cli_param['scope_' . $scope] = in_array($scope, $zblogmcp_cli_selected_scopes, true) ? 1 : 0;
    }

    zblogmcp_add_key($zblogmcp_cli_param, false);

    fwrite(STDOUT, 'ApiKey 添加成功' . PHP_EOL);
    fwrite(STDOUT, '名称：' . $zblogmcp_cli_name . PHP_EOL);
    fwrite(STDOUT, 'ApiKey：' . $zblogmcp_cli_api_key . PHP_EOL);
    fwrite(STDOUT, '权限：' . implode(',', $zblogmcp_cli_selected_scopes) . PHP_EOL);
} catch (Throwable $e) {
    zblogmcp_cli_fail($e->getMessage());
}
