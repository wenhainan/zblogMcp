<?php
/**
 * zblogMcp - MCP 日志查看页
 */
require_once __DIR__ . '/header.php';

if (GetVars('act', 'GET') == 'del') {
    CheckIsRefererValid();
    zblogmcp_del_log(GetVars('id', 'GET'));
}
if (GetVars('act', 'GET') == 'clear') {
    CheckIsRefererValid();
    zblogmcp_clear_log();
}
if (GetVars('act', 'GET') == 'clean_old') {
    CheckIsRefererValid();
    zblogmcp_clean_old_logs();
    zblogmcp_success('已清理超过 ' . $zblogmcp_config->log_max_days . ' 天的旧日志');
}

$stats = zblogmcp_log_stats();
$log_result = zblogmcp_log_list();
$logs = $log_result['log'];
$p = $log_result['p'];

$keys = zblogmcp_key_list();
$key_name_map = array();
foreach ($keys as $k) {
    $key_name_map[$k['id']] = $k['name'];
}

$log_max_days = (int) $zblogmcp_config->log_max_days;
$clean_url = 'log.php?act=clean_old&amp;csrfToken=' . urlencode($zbp->GetCSRFToken());
$clear_url = 'log.php?act=clear&amp;csrfToken=' . urlencode($zbp->GetCSRFToken());
$logs_json = json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<div id="divMain" class="plugin-area">
    <div class="divHeader"><?php echo zblogmcp_h($zblogmcp_plugin_info['name']); ?></div>
    <div class="SubMenu"><?php echo zblogmcp_SubMenu(); ?></div>
    <div id="divMain2">
        <div class="zbm-app">

            <div class="zbm-stats">
                <div class="zbm-stat">
                    <div class="zbm-stat__title">总请求数</div>
                    <div class="zbm-stat__value"><?php echo (int) $stats['total']; ?></div>
                </div>
                <div class="zbm-stat zbm-stat--success">
                    <div class="zbm-stat__title">成功</div>
                    <div class="zbm-stat__value"><?php echo (int) $stats['success']; ?><span class="zbm-stat__suffix">成功</span></div>
                </div>
                <div class="zbm-stat zbm-stat--danger">
                    <div class="zbm-stat__title">失败</div>
                    <div class="zbm-stat__value"><?php echo (int) $stats['fail']; ?><span class="zbm-stat__suffix">失败</span></div>
                </div>
                <div class="zbm-stat">
                    <div class="zbm-stat__title">今日请求</div>
                    <div class="zbm-stat__value"><?php echo (int) $stats['today']; ?></div>
                </div>
            </div>

            <div class="zbm-card">
                <div class="zbm-card__body">
                    <div class="zbm-row">
                        <a class="zbm-btn zbm-btn--warning zbm-btn--plain zbm-btn--sm"
                           href="<?php echo $clean_url; ?>"
                           data-zbm-confirm="确定清理 <?php echo $log_max_days; ?> 天前的旧日志？"
                           data-zbm-confirm-title="提示"
                           data-zbm-confirm-ok="确定">
                            <?php echo zblogmcp_icon('clock'); ?><span>清理 <?php echo $log_max_days; ?> 天前日志</span>
                        </a>
                        <a class="zbm-btn zbm-btn--danger zbm-btn--plain zbm-btn--sm"
                           href="<?php echo $clear_url; ?>"
                           data-zbm-confirm="确定清空全部日志？此操作不可恢复！"
                           data-zbm-confirm-title="警告"
                           data-zbm-confirm-ok="确定清空"
                           data-zbm-confirm-type="error">
                            <?php echo zblogmcp_icon('trash'); ?><span>清空全部日志</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="zbm-card">
                <div class="zbm-card__body">
                    <div class="zbm-table-wrap">
                        <table class="zbm-table">
                            <thead>
                                <tr>
                                    <th class="is-center" style="width:60px;">ID</th>
                                    <th style="min-width:120px;">方法</th>
                                    <th style="min-width:140px;">工具/端点</th>
                                    <th class="is-center" style="width:70px;">状态</th>
                                    <th class="is-center" style="width:80px;">耗时</th>
                                    <th style="min-width:110px;">IP</th>
                                    <th style="min-width:90px;">ApiKey</th>
                                    <th class="is-center" style="min-width:100px;">时间</th>
                                    <th class="is-center" style="width:150px;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)) : ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="zbm-empty">暂无 MCP 请求日志</div>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($logs as $row) : ?>
                                        <?php
                                        $log_id = (int) $row['id'];
                                        $is_ok = ((int) $row['status_code'] === 1);
                                        $key_label = '-';
                                        if (!empty($row['api_key_id']) && isset($key_name_map[$row['api_key_id']])) {
                                            $key_label = $key_name_map[$row['api_key_id']];
                                        }
                                        $delete_url = 'log.php?act=del&amp;id=' . $log_id . '&amp;csrfToken=' . urlencode($zbp->GetCSRFToken());
                                        ?>
                                        <tr>
                                            <td class="is-center"><?php echo $log_id; ?></td>
                                            <td><span class="zbm-tag zbm-tag--plain"><code><?php echo zblogmcp_h($row['method']); ?></code></span></td>
                                            <td>
                                                <?php if ($row['endpoint'] !== '') : ?>
                                                    <code><?php echo zblogmcp_h($row['endpoint']); ?></code>
                                                <?php else : ?>
                                                    <span class="zbm-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="is-center">
                                                <span class="zbm-tag <?php echo $is_ok ? 'zbm-tag--success' : 'zbm-tag--danger'; ?>">
                                                    <?php echo $is_ok ? '成功' : '失败'; ?>
                                                </span>
                                            </td>
                                            <td class="is-center"><?php echo (int) $row['response_time']; ?>ms</td>
                                            <td><?php echo zblogmcp_h($row['ip']); ?></td>
                                            <td>
                                                <?php if ($key_label !== '-') : ?>
                                                    <?php echo zblogmcp_h($key_label); ?>
                                                <?php else : ?>
                                                    <span class="zbm-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="is-center"><?php echo zblogmcp_h(substr((string) $row['create_time'], 5, 16)); ?></td>
                                            <td>
                                                <div class="zbm-actions">
                                                    <button type="button" class="zbm-btn zbm-btn--sm" data-zbm-log-detail="<?php echo $log_id; ?>">
                                                        <?php echo zblogmcp_icon('info'); ?><span>详情</span>
                                                    </button>
                                                    <a class="zbm-btn zbm-btn--danger zbm-btn--sm"
                                                       href="<?php echo $delete_url; ?>"
                                                       data-zbm-confirm="确定删除此条日志？"
                                                       data-zbm-confirm-title="提示"
                                                       data-zbm-confirm-ok="确定">
                                                        <?php echo zblogmcp_icon('trash'); ?><span>删除</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($logs)) : ?>
                        <?php echo zblogmcp_page_render($p); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="zbm-dialog" id="zbmLogDialog">
                <div class="zbm-dialog__mask" data-zbm-dialog-close></div>
                <div class="zbm-dialog__box">
                    <div class="zbm-dialog__header">
                        <span>请求详情</span>
                        <button type="button" class="zbm-dialog__close" data-zbm-dialog-close aria-label="关闭">&times;</button>
                    </div>
                    <div class="zbm-dialog__body" id="zbmLogDialogBody"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script type="application/json" id="zbm-log-data"><?php echo $logs_json; ?></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var logs = [];
    try {
        logs = JSON.parse(document.getElementById('zbm-log-data').textContent || '[]');
    } catch (error) {
        logs = [];
    }

    var body = document.getElementById('zbmLogDialogBody');

    function findLog(id) {
        for (var i = 0; i < logs.length; i++) {
            if (String(logs[i].id) === String(id)) {
                return logs[i];
            }
        }
        return null;
    }

    function formatJson(text) {
        if (!text) {
            return '(空)';
        }
        try {
            return JSON.stringify(JSON.parse(text), null, 2);
        } catch (error) {
            return text;
        }
    }

    function makeNode(tag, className, text) {
        var node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (text !== undefined && text !== null) {
            node.textContent = String(text);
        }
        return node;
    }

    function descRow(label, value, isCode) {
        var row = makeNode('div', 'zbm-desc__row');
        row.appendChild(makeNode('div', 'zbm-desc__label', label));
        var cell = makeNode('div', 'zbm-desc__value');
        if (isCode) {
            cell.appendChild(makeNode('code', '', value));
        } else {
            cell.textContent = value === '' ? '-' : String(value);
        }
        row.appendChild(cell);
        return row;
    }

    function render(log) {
        body.innerHTML = '';
        if (!log) {
            body.appendChild(makeNode('div', 'zbm-muted', '未找到该条日志'));
            return;
        }

        var desc = makeNode('div', 'zbm-desc');
        desc.appendChild(descRow('方法', log.method, true));
        desc.appendChild(descRow('工具/端点', log.endpoint || '-', true));
        desc.appendChild(descRow('状态', log.status_code == 1 ? '成功' : '失败', false));
        desc.appendChild(descRow('耗时', (log.response_time || 0) + 'ms', false));
        desc.appendChild(descRow('IP', log.ip, false));
        desc.appendChild(descRow('时间', log.create_time, false));
        body.appendChild(desc);

        body.appendChild(makeNode('div', 'zbm-block-title', '请求内容'));
        body.appendChild(makeNode('pre', 'zbm-code', formatJson(log.request_body)));

        body.appendChild(makeNode('div', 'zbm-block-title', '响应内容'));
        body.appendChild(makeNode('pre', 'zbm-code', formatJson(log.response_body)));
    }

    var buttons = document.querySelectorAll('[data-zbm-log-detail]');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function () {
            render(findLog(this.getAttribute('data-zbm-log-detail')));
            window.Zbm.openDialog('#zbmLogDialog');
        });
    }
});
</script>
<?php require_once __DIR__ . '/foot.php'; ?>
