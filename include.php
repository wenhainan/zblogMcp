<?php
/**
 * zblogMcp 业务逻辑层
 * 包含 ApiKey 管理和 MCP 日志的 CRUD 操作
 */
require_once __DIR__ . '/core.php';

// ========== ApiKey 管理 ==========

/**
 * 新增 ApiKey
 * @Z5Encrypt
 */
function zblogmcp_add_key($param)
{
    global $zbp;
    if (empty($param['name']) || empty($param['api_key'])) {
        zblogmcp_error('名称和 ApiKey 为必填项');
        return;
    }
    $log_table = $zbp->table['zblogmcp_key'];
    $zbp->db->sql->get()
        ->insert($log_table)
        ->data([
            'name'        => $param['name'],
            'api_key'     => $param['api_key'],
            'status'      => isset($param['status']) ? (int) $param['status'] : 1,
            'scope_read'   => isset($param['scope_read']) ? (int) $param['scope_read'] : 1,
            'scope_write'  => isset($param['scope_write']) ? (int) $param['scope_write'] : 1,
            'scope_category' => isset($param['scope_category']) ? (int) $param['scope_category'] : 1,
            'scope_tag' => isset($param['scope_tag']) ? (int) $param['scope_tag'] : 1,
            'scope_media' => isset($param['scope_media']) ? (int) $param['scope_media'] : 1,
            'scope_comment' => isset($param['scope_comment']) ? (int) $param['scope_comment'] : 1,
            'remark'      => isset($param['remark']) ? $param['remark'] : '',
            'create_time' => date('Y-m-d H:i:s', time()),
            'update_time' => date('Y-m-d H:i:s', time()),
        ])
        ->query;
    zblogmcp_redirect('main.php', 'ApiKey 添加成功');
}

/**
 * 更新 ApiKey
 * @Z5Encrypt
 */
function zblogmcp_update_key($param)
{
    global $zbp;
    if (empty($param['id'])) {
        zblogmcp_error('参数错误：缺少 ID');
        return;
    }
    if (empty($param['name']) || empty($param['api_key'])) {
        zblogmcp_error('名称和 ApiKey 为必填项');
        return;
    }
    $log_table = $zbp->table['zblogmcp_key'];
    $zbp->db->sql->get()
        ->update($log_table)
        ->where('=', 'id', (int) $param['id'])
        ->data([
            'name'        => $param['name'],
            'api_key'     => $param['api_key'],
            'status'      => isset($param['status']) ? (int) $param['status'] : 1,
            'scope_read'   => isset($param['scope_read']) ? (int) $param['scope_read'] : 0,
            'scope_write'  => isset($param['scope_write']) ? (int) $param['scope_write'] : 0,
            'scope_category' => isset($param['scope_category']) ? (int) $param['scope_category'] : 0,
            'scope_tag' => isset($param['scope_tag']) ? (int) $param['scope_tag'] : 0,
            'scope_media' => isset($param['scope_media']) ? (int) $param['scope_media'] : 0,
            'scope_comment' => isset($param['scope_comment']) ? (int) $param['scope_comment'] : 0,
            'remark'      => isset($param['remark']) ? $param['remark'] : '',
            'update_time' => date('Y-m-d H:i:s', time()),
        ])
        ->query;
    zblogmcp_redirect('main.php', 'ApiKey 更新成功');
}

/**
 * 删除 ApiKey
 * @Z5Encrypt
 */
function zblogmcp_del_key($id)
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_key'];
    $zbp->db->sql->get()
        ->delete($log_table)
        ->where('=', 'id', (int) $id)
        ->query;
    zblogmcp_success('ApiKey 删除成功');
}

/**
 * 切换 ApiKey 启用/禁用状态
 * @Z5Encrypt
 */
function zblogmcp_toggle_key($id, $status)
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_key'];
    $zbp->db->sql->get()
        ->update($log_table)
        ->where('=', 'id', (int) $id)
        ->data([
            'status'      => (int) $status,
            'update_time' => date('Y-m-d H:i:s', time()),
        ])
        ->query;
    zblogmcp_success($status ? 'ApiKey 已启用' : 'ApiKey 已禁用');
}

/**
 * 获取 ApiKey 列表
 * @Z5Encrypt
 */
function zblogmcp_key_list()
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_key'];
    return $zbp->db->sql->get()
        ->select($log_table)
        ->orderBy('id desc')
        ->query;
}

/**
 * 根据 ID 获取单条 ApiKey 记录
 * @Z5Encrypt
 */
function zblogmcp_get_key($id)
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_key'];
    $data = $zbp->db->sql->get()
        ->select($log_table)
        ->where('=', 'id', (int) $id)
        ->query;
    return !empty($data) ? $data[0] : null;
}

/**
 * @Z5Encrypt
 */
function zblogmcp_key_can_use_tool($key_record, $tool_name)
{
    if (!$key_record) {
        return false;
    }
    $read_tools = ['zblog_list_articles', 'zblog_get_article', 'zblog_list_categories'];
    $write_tools = ['zblog_write_article'];
    $category_tools = ['zblog_create_category'];
    $tag_tools = ['zblog_list_tags', 'zblog_get_tag', 'zblog_save_tag', 'zblog_delete_tag'];
    $media_tools = ['zblog_list_media', 'zblog_get_media', 'zblog_upload_media', 'zblog_delete_media'];
    $comment_tools = ['zblog_list_comments', 'zblog_get_comment', 'zblog_create_comment', 'zblog_review_comment', 'zblog_delete_comment'];
    if (in_array($tool_name, $read_tools, true)) {
        return !empty($key_record['scope_read']);
    }
    if (in_array($tool_name, $write_tools, true)) {
        return !empty($key_record['scope_write']);
    }
    if (in_array($tool_name, $category_tools, true)) {
        return !empty($key_record['scope_category']);
    }
    if (in_array($tool_name, $tag_tools, true)) {
        return !empty($key_record['scope_tag']);
    }
    if (in_array($tool_name, $media_tools, true)) {
        return !empty($key_record['scope_media']);
    }
    if (in_array($tool_name, $comment_tools, true)) {
        return !empty($key_record['scope_comment']);
    }
    return false;
}

/**
 * 根据 api_key 字符串验证并返回记录（供外部 MCP 接口调用）
 * 返回 null 表示无效
 * @Z5Encrypt
 */
function zblogmcp_validate_key($api_key)
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_key'];
    $data = $zbp->db->sql->get()
        ->select($log_table)
        ->where('=', 'api_key', $api_key)
        ->where('=', 'status', 1)
        ->query;
    if (empty($data)) {
        return null;
    }
    $record = $data[0];
    $zbp->db->sql->get()
        ->update($log_table)
        ->where('=', 'id', (int) $record['id'])
        ->data([
            'last_use_time' => date('Y-m-d H:i:s', time()),
            'last_use_ip'   => zblogmcp_get_client_ip(),
            'use_count'     => isset($record['use_count']) ? (int) $record['use_count'] + 1 : 1,
        ])
        ->query;
    return $record;
}

// ========== MCP 日志管理 ==========

/**
 * 记录一条 MCP 请求日志
 * @Z5Encrypt
 */
function zblogmcp_add_log($data)
{
    global $zbp, $zblogmcp_config;
    if (empty($zblogmcp_config->log_enabled)) {
        return;
    }
    $log_table = $zbp->table['zblogmcp_log'];
    $zbp->db->sql->get()
        ->insert($log_table)
        ->data([
            'method'        => isset($data['method']) ? $data['method'] : '',
            'endpoint'      => isset($data['endpoint']) ? $data['endpoint'] : '',
            'request_body'  => isset($data['request_body']) ? mb_substr(zblogmcp_mask_log_payload($data['request_body']), 0, 2000) : '',
            'response_body' => isset($data['response_body']) ? mb_substr(zblogmcp_mask_log_payload($data['response_body']), 0, 2000) : '',
            'status_code'   => isset($data['status_code']) ? (int) $data['status_code'] : 1,
            'response_time' => isset($data['response_time']) ? (int) $data['response_time'] : 0,
            'ip'            => isset($data['ip']) ? $data['ip'] : zblogmcp_get_client_ip(),
            'api_key_id'    => isset($data['api_key_id']) ? (int) $data['api_key_id'] : 0,
            'create_time'   => date('Y-m-d H:i:s', time()),
        ])
        ->query;
}

/**
 * @Z5Encrypt
 */
function zblogmcp_mask_log_payload($payload)
{
    global $zblogmcp_config;
    if (empty($zblogmcp_config->log_body_enabled)) {
        return '[body logging disabled]';
    }
    $decoded = json_decode($payload, true);
    if (!is_array($decoded)) {
        return $payload;
    }
    zblogmcp_mask_log_array($decoded);
    return json_encode($decoded, JSON_UNESCAPED_UNICODE);
}

/**
 * @Z5Encrypt
 */
function zblogmcp_mask_log_array(&$data)
{
    $sensitive = ['api_key', 'token', 'authorization', 'content'];
    foreach ($data as $key => &$value) {
        if (in_array(strtolower((string) $key), $sensitive, true)) {
            $value = '[masked]';
            continue;
        }
        if (is_array($value)) {
            zblogmcp_mask_log_array($value);
        }
    }
}

/**
 * 获取 MCP 日志列表（带分页）
 * @Z5Encrypt
 */
function zblogmcp_log_list($where = [])
{
    return zblogmcp_query('zblogmcp_log', $where);
}

/**
 * 删除单条日志
 * @Z5Encrypt
 */
function zblogmcp_del_log($id)
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_log'];
    $zbp->db->sql->get()
        ->delete($log_table)
        ->where('=', 'id', (int) $id)
        ->query;
    zblogmcp_success('日志删除成功');
}

/**
 * 清空全部日志
 * @Z5Encrypt
 */
function zblogmcp_clear_log()
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_log'];
    $zbp->db->sql->get()
        ->delete($log_table)
        ->where('>', 'id', 0)
        ->query;
    zblogmcp_success('日志已全部清空');
}

/**
 * 清理超过指定天数的旧日志
 * @Z5Encrypt
 */
function zblogmcp_clean_old_logs()
{
    global $zbp, $zblogmcp_config;
    $days = (int) $zblogmcp_config->log_max_days;
    if ($days <= 0) {
        return;
    }
    $log_table = $zbp->table['zblogmcp_log'];
    $cutoff = date('Y-m-d H:i:s', time() - $days * 86400);
    $zbp->db->sql->get()
        ->delete($log_table)
        ->where('<', 'create_time', $cutoff)
        ->query;
}

/**
 * 获取日志统计概览
 * @Z5Encrypt
 */
function zblogmcp_log_stats()
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_log'];

    $total = GetValueInArrayByCurrent(
        $zbp->db->sql->get()->select($log_table)->count(array('*' => 'num'))->query,
        'num'
    );
    $success = GetValueInArrayByCurrent(
        $zbp->db->sql->get()->select($log_table)->where('=', 'status_code', 1)->count(array('*' => 'num'))->query,
        'num'
    );
    $fail = GetValueInArrayByCurrent(
        $zbp->db->sql->get()->select($log_table)->where('=', 'status_code', 0)->count(array('*' => 'num'))->query,
        'num'
    );
    $today = GetValueInArrayByCurrent(
        $zbp->db->sql->get()->select($log_table)
            ->where('>=', 'create_time', date('Y-m-d 00:00:00'))
            ->count(array('*' => 'num'))->query,
        'num'
    );

    return [
        'total'   => (int) $total,
        'success' => (int) $success,
        'fail'    => (int) $fail,
        'today'   => (int) $today,
    ];
}

/**
 * 获取日志详情
 * @Z5Encrypt
 */
function zblogmcp_get_log($id)
{
    global $zbp;
    $log_table = $zbp->table['zblogmcp_log'];
    $data = $zbp->db->sql->get()
        ->select($log_table)
        ->where('=', 'id', (int) $id)
        ->query;
    return !empty($data) ? $data[0] : null;
}

// ========== 工具函数 ==========

/**
 * 获取客户端真实 IP
 * @Z5Encrypt
 */
function zblogmcp_get_client_ip()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
}

/**
 * 生成随机 ApiKey
 * @Z5Encrypt
 */
function zblogmcp_generate_key()
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $max = strlen($chars) - 1;
    $key = 'sk-mcp-';
    for ($i = 0; $i < 40; $i++) {
        $key .= $chars[random_int(0, $max)];
    }
    return $key;
}

/**
 * 对 ApiKey 进行脱敏显示
 * @Z5Encrypt
 */
function zblogmcp_mask_key($key)
{
    $len = strlen($key);
    if ($len <= 4) {
        return str_repeat('*', $len);
    }
    return str_repeat('*', $len - 4) . substr($key, -4);
}
