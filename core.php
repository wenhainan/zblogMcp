<?php
/**
 * zblogMcp 核心文件
 * 负责插件注册、数据表定义、安装与卸载
 */

$zblogmcp_plugin_info = include dirname(__FILE__) . '/config/plugin_info.php';

# 后台界面辅助（图标、转义）
require_once dirname(__FILE__) . '/ui.php';

define('ZBLOGMCP', dirname(__FILE__));
$title = $zblogmcp_plugin_info['name'];
$zblogmcp_config = $zbp->Config('zblogMcp');
$current_file = zblogmcp_current_file();

# 注册插件
RegisterPlugin("zblogMcp", "ActivePlugin_zblogMcp");

# 数据表映射
$table['zblogmcp_key'] = '%pre%zblogmcp_key';
$table['zblogmcp_log'] = '%pre%zblogmcp_log';

# zblogmcp_key 表字段定义 - ApiKey 管理
$datainfo['zblogmcp_key'] = array(
    'ID'          => array('id', 'integer', '', 0),
    'name'        => array('name', 'string', 100, ''),
    'api_key'     => array('api_key', 'string', 500, ''),
    'status'      => array('status', 'integer', '', 1),
    'scope_read'  => array('scope_read', 'integer', '', 1),
    'scope_write' => array('scope_write', 'integer', '', 0),
    'scope_category' => array('scope_category', 'integer', '', 0),
    'scope_tag' => array('scope_tag', 'integer', '', 0),
    'scope_media' => array('scope_media', 'integer', '', 0),
    'scope_comment' => array('scope_comment', 'integer', '', 0),
    'remark'      => array('remark', 'string', 500, ''),
    'last_use_time' => array('last_use_time', 'timestamp', '', 0),
    'last_use_ip' => array('last_use_ip', 'string', 50, ''),
    'use_count'   => array('use_count', 'integer', '', 0),
    'create_time' => array('create_time', 'timestamp', '', 0),
    'update_time' => array('update_time', 'timestamp', '', 0),
);

# zblogmcp_log 表字段定义 - MCP 日志
$datainfo['zblogmcp_log'] = array(
    'ID'            => array('id', 'integer', '', 0),
    'method'        => array('method', 'string', 50, ''),
    'endpoint'      => array('endpoint', 'string', 200, ''),
    'request_body'  => array('request_body', 'string', 2000, ''),
    'response_body' => array('response_body', 'string', 2000, ''),
    'status_code'   => array('status_code', 'integer', '', 0),
    'response_time' => array('response_time', 'integer', '', 0),
    'ip'            => array('ip', 'string', 50, ''),
    'api_key_id'    => array('api_key_id', 'integer', '', 0),
    'create_time'   => array('create_time', 'timestamp', '', 0),
);

/**
 * 激活插件时调用的钩子
 */
function ActivePlugin_zblogMcp()
{
    Add_Filter_Plugin('Filter_Plugin_Admin_TopMenu', 'zblogmcp_add_top');
}

/**
 * 安装插件 - 创建数据表并写入默认配置
 */
function InstallPlugin_zblogMcp()
{
    global $zbp, $zblogmcp_config, $zblogmcp_plugin_info;

    if (!$zbp->db->ExistTable($GLOBALS['table']['zblogmcp_key'])) {
        $s = $zbp->db->sql->CreateTable(
            $GLOBALS['table']['zblogmcp_key'],
            $GLOBALS['datainfo']['zblogmcp_key'],
            'InnoDB', 'utf8mb4', 'utf8_general_ci'
        );
        $zbp->db->QueryMulit($s);
    }

    if (!$zbp->db->ExistTable($GLOBALS['table']['zblogmcp_log'])) {
        $s = $zbp->db->sql->CreateTable(
            $GLOBALS['table']['zblogmcp_log'],
            $GLOBALS['datainfo']['zblogmcp_log'],
            'InnoDB', 'utf8mb4', 'utf8_general_ci'
        );
        $zbp->db->QueryMulit($s);
    }

    # 写入默认配置
    $zblogmcp_config->sql_version = $zblogmcp_plugin_info['sql_version'];
    $zblogmcp_config->menu_name = $zblogmcp_plugin_info['name'];
    $zblogmcp_config->log_max_days = 30;
    $zblogmcp_config->log_enabled = 1;
    $zblogmcp_config->log_body_enabled = 0;
    $zblogmcp_config->default_write_status = ZC_POST_STATUS_AUDITING;
    $zblogmcp_config->article_meta_schema = '';
    $zbp->SaveConfig('zblogMcp');
}

/**
 * 卸载插件 - 删除数据表和配置
 */
function UninstallPlugin_zblogMcp()
{
    global $zbp, $table;
    $zbp->db->sql->get()->drop()->table($table['zblogmcp_key'])->ifexists()->query;
    $zbp->db->sql->get()->drop()->table($table['zblogmcp_log'])->ifexists()->query;
}

/**
 * 更新插件 - 补建缺失的数据表和字段
 */
function UpdatePlugin_zblogMcp()
{
    global $zbp, $zblogmcp_config, $zblogmcp_plugin_info;

    if (!$zbp->db->ExistTable($GLOBALS['table']['zblogmcp_key'])) {
        $s = $zbp->db->sql->CreateTable(
            $GLOBALS['table']['zblogmcp_key'],
            $GLOBALS['datainfo']['zblogmcp_key'],
            'InnoDB', 'utf8mb4', 'utf8_general_ci'
        );
        $zbp->db->QueryMulit($s);
    }

    if (!$zbp->db->ExistTable($GLOBALS['table']['zblogmcp_log'])) {
        $s = $zbp->db->sql->CreateTable(
            $GLOBALS['table']['zblogmcp_log'],
            $GLOBALS['datainfo']['zblogmcp_log'],
            'InnoDB', 'utf8mb4', 'utf8_general_ci'
        );
        $zbp->db->QueryMulit($s);
    }

    zblogmcp_upgrade_tables();

    if (empty($zblogmcp_config->log_max_days)) {
        $zblogmcp_config->log_max_days = 30;
    }
    if (empty($zblogmcp_config->log_enabled)) {
        $zblogmcp_config->log_enabled = 1;
    }
    if (empty($zblogmcp_config->menu_name)) {
        $zblogmcp_config->menu_name = $zblogmcp_plugin_info['name'];
    }
    if (!isset($zblogmcp_config->log_body_enabled)) {
        $zblogmcp_config->log_body_enabled = 0;
    }
    if (!isset($zblogmcp_config->default_write_status)) {
        $zblogmcp_config->default_write_status = ZC_POST_STATUS_AUDITING;
    }
    if (!isset($zblogmcp_config->article_meta_schema)) {
        $zblogmcp_config->article_meta_schema = '';
    }
    $zbp->SaveConfig('zblogMcp');
}

function zblogmcp_upgrade_tables()
{
    global $zbp;
    $key_table = $GLOBALS['table']['zblogmcp_key'];
    $columns = [
        'scope_read' => 'TINYINT NOT NULL DEFAULT 1',
        'scope_write' => 'TINYINT NOT NULL DEFAULT 0',
        'scope_category' => 'TINYINT NOT NULL DEFAULT 0',
        'scope_tag' => 'TINYINT NOT NULL DEFAULT 0',
        'scope_media' => 'TINYINT NOT NULL DEFAULT 0',
        'scope_comment' => 'TINYINT NOT NULL DEFAULT 0',
        'last_use_time' => 'DATETIME NULL',
        'last_use_ip' => 'VARCHAR(50) NOT NULL DEFAULT \'\'',
        'use_count' => 'INT NOT NULL DEFAULT 0',
    ];
    foreach ($columns as $column => $definition) {
        @$zbp->db->Query("ALTER TABLE {$key_table} ADD `{$column}` {$definition}");
    }
}

/**
 * 注册顶部导航菜单
 */
function zblogmcp_add_top(&$m)
{
    global $zbp, $zblogmcp_config, $zblogmcp_plugin_info;
    $menu_name = !empty($zblogmcp_config->menu_name) ? $zblogmcp_config->menu_name : $zblogmcp_plugin_info['name'];
    $m[] = MakeTopMenu(
        "root",
        $menu_name,
        $zbp->host . "zb_users/plugin/" . $zblogmcp_plugin_info['id'] . "/main.php",
        "",
        "topmenu_" . $zblogmcp_plugin_info['id'],
        "fa fa-server"
    );
}

/**
 * 获取当前访问的文件名
 */
function zblogmcp_current_file()
{
    $php_self = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
    return $php_self;
}

/**
 * 渲染子菜单导航
 */
function zblogmcp_SubMenu()
{
    $url = zblogmcp_current_file();
    $tab = GetVars('tab', 'GET');
    $menus = include dirname(__FILE__) . '/config/menu.php';
    $str = '';
    foreach ($menus as $menu) {
        $menu_file = isset($menu['file']) ? $menu['file'] : strtok($menu['url'], '?');
        $active = ($url == $menu_file);
        if ($active && isset($menu['tab'])) {
            $active = ($tab == $menu['tab']);
        }
        $class = $active ? 'm-now' : '';
        $class .= ($menu['position'] == 'right') ? ' m-right' : ' m-left';
        $str .= '<a href="' . $menu['url'] . '" target="' . $menu['target'] . '"><span class="' . $class . '">' . $menu['title'] . '</span></a>';
    }
    return $str;
}

/**
 * 保存插件配置
 */
function zblogmcp_save($param = [])
{
    global $zbp;
    foreach ($param as $field => $value) {
        $zbp->Config('zblogMcp')->$field = $value;
    }
    $zbp->SaveConfig('zblogMcp');
}

/**
 * 操作成功提示并跳转
 */
function zblogmcp_success($msg = '操作成功')
{
    global $zbp;
    $zbp->SetHint('good', $msg);
    Redirect(zblogmcp_current_file());
}

/**
 * 操作失败提示并跳转
 */
function zblogmcp_error($msg = '操作失败')
{
    global $zbp;
    $zbp->SetHint('bad', $msg);
    Redirect(zblogmcp_current_file());
}

/**
 * 跳转到指定页面
 */
function zblogmcp_redirect($url, $msg = '操作成功')
{
    global $zbp;
    $zbp->SetHint('good', $msg);
    Redirect($url);
}

/**
 * 通用分页查询
 */
function zblogmcp_query($table, $where = [])
{
    global $zbp;
    $page_size = $zbp->Config('system')->ZC_MANAGE_COUNT;
    $log_table = $zbp->table[$table];
    $query = $_GET;
    unset($query['page']);
    $query['page'] = '{%page%}';
    $query_string = http_build_query($query);
    $query_string = str_replace('%7B%25page%25%7D', '{%page%}', $query_string);
    $page_url = zblogmcp_current_file() . ($query_string ? '?' . $query_string : '?page={%page%}');
    $p = new Pagebar($page_url, false);
    $p->PageCount = $page_size;
    $p->PageNow = (int) GetVars('page', 'GET') == 0 ? 1 : (int) GetVars('page', 'GET');
    $p->PageBarCount = $zbp->pagebarcount;
    $p->UrlRule->Rules['{%page%}'] = GetVars('page');
    $p->Count = GetValueInArrayByCurrent(
        $zbp->db->sql->get()->where($where)->select($log_table)->count(array('*' => 'num'))->where(array())->query,
        'num'
    );
    $log_data = $zbp->db->sql->get()
        ->select($log_table)
        ->orderBy('id desc')
        ->where($where)
        ->limit(($p->PageNow - 1) * $page_size, $page_size)
        ->query;
    $p->make();
    return [
        'log' => $log_data,
        'p'   => $p,
    ];
}

/**
 * 分页栏渲染
 */
function zblogmcp_page_render($p)
{
    $html = '<div class="zbm-pager"><span class="zbm-pager__total">共 ' . (int) $p->Count . ' 条</span>';
    foreach ($p->buttons as $page => $href) {
        $active = ((string) $page === (string) $p->PageNow) ? 'is-active' : '';
        $html .= '<a class="' . $active . '" href="' . zblogmcp_h($href) . '">' . zblogmcp_h($page) . '</a>';
    }
    return $html . '</div>';
}
