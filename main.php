<?php
/**
 * zblogMcp - ApiKey 管理主页
 */
require_once __DIR__ . '/header.php';

# 处理删除操作
if (GetVars('act', 'GET') == 'del') {
    CheckIsRefererValid();
    zblogmcp_del_key(GetVars('id', 'GET'));
}

# 处理切换状态操作
if (GetVars('act', 'GET') == 'toggle') {
    CheckIsRefererValid();
    zblogmcp_toggle_key(GetVars('id', 'GET'), GetVars('status', 'GET'));
}

$keys = zblogmcp_key_list();
$mcp_url = $bloghost . 'zb_users/plugin/zblogMcp/mcp.php';
$csrf_token = $zbp->GetCSRFToken();
?>
<div id="divMain" class="plugin-area">
    <div class="divHeader"><?php echo zblogmcp_h($zblogmcp_plugin_info['name']); ?></div>
    <div class="SubMenu"><?php echo zblogmcp_SubMenu(); ?></div>
    <div id="divMain2">
        <div class="zbm-app">
            <div class="zbm-card">
                <div class="zbm-card__header">
                    <span>ApiKey 管理</span>
                    <a class="zbm-btn zbm-btn--primary zbm-btn--sm" href="key_edit.php">
                        <?php echo zblogmcp_icon('key'); ?><span>新增 ApiKey</span>
                    </a>
                </div>
                <div class="zbm-card__body">
                    <div class="zbm-table-wrap">
                        <table class="zbm-table">
                            <thead>
                                <tr>
                                    <th class="is-center" style="width:60px;">ID</th>
                                    <th style="min-width:130px;">名称</th>
                                    <th style="min-width:280px;">ApiKey</th>
                                    <th class="is-center" style="width:80px;">状态</th>
                                    <th style="min-width:80px;">备注</th>
                                    <th class="is-center" style="min-width:110px;">创建时间</th>
                                    <th class="is-center" style="width:300px;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($keys)) : ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="zbm-empty">
                                                <div>暂无 ApiKey 记录</div>
                                                <a class="zbm-btn zbm-btn--primary zbm-btn--sm" href="key_edit.php">
                                                    <?php echo zblogmcp_icon('key'); ?><span>立即创建</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($keys as $row) : ?>
                                        <?php
                                        $key_id = (int) $row['id'];
                                        $api_key = (string) $row['api_key'];
                                        $key_name = (string) $row['name'];
                                        $is_enabled = ((int) $row['status'] === 1);
                                        $client_config = json_encode(array(
                                            'mcpServers' => array(
                                                'zblog' => array(
                                                    'url'     => $mcp_url,
                                                    'headers' => array('Authorization' => 'Bearer ' . $api_key),
                                                ),
                                            ),
                                        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                        $toggle_url = 'main.php?act=toggle&amp;id=' . $key_id . '&amp;status=' . ($is_enabled ? 0 : 1) . '&amp;csrfToken=' . urlencode($csrf_token);
                                        $delete_url = 'main.php?act=del&amp;id=' . $key_id . '&amp;csrfToken=' . urlencode($csrf_token);
                                        ?>
                                        <tr>
                                            <td class="is-center"><?php echo $key_id; ?></td>
                                            <td><span class="zbm-tag zbm-tag--plain"><?php echo zblogmcp_h($key_name); ?></span></td>
                                            <td>
                                                <div class="zbm-row">
                                                    <code class="zbm-key"
                                                          id="zbm-key-<?php echo $key_id; ?>"
                                                          data-state="mask"
                                                          data-full="<?php echo zblogmcp_h($api_key); ?>"
                                                          data-mask="<?php echo zblogmcp_h(zblogmcp_mask_key($api_key)); ?>"><?php echo zblogmcp_h(zblogmcp_mask_key($api_key)); ?></code>
                                                    <button type="button"
                                                            class="zbm-btn zbm-btn--sm"
                                                            data-zbm-toggle-key="#zbm-key-<?php echo $key_id; ?>"
                                                            data-label-full="隐藏"
                                                            data-label-mask="显示">显示</button>
                                                </div>
                                            </td>
                                            <td class="is-center">
                                                <span class="zbm-tag <?php echo $is_enabled ? 'zbm-tag--success' : 'zbm-tag--danger'; ?>">
                                                    <?php echo $is_enabled ? '启用' : '禁用'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo zblogmcp_h($row['remark']); ?></td>
                                            <td class="is-center"><?php echo zblogmcp_h(substr((string) $row['create_time'], 0, 16)); ?></td>
                                            <td>
                                                <div class="zbm-actions">
                                                    <button type="button"
                                                            class="zbm-btn zbm-btn--primary zbm-btn--plain zbm-btn--sm"
                                                            data-zbm-copy="<?php echo zblogmcp_h($api_key); ?>"
                                                            data-zbm-copy-msg="ApiKey 已复制">
                                                        <?php echo zblogmcp_icon('copy'); ?><span>复制</span>
                                                    </button>
                                                    <button type="button"
                                                            class="zbm-btn zbm-btn--success zbm-btn--plain zbm-btn--sm"
                                                            data-zbm-copy="<?php echo zblogmcp_h($client_config); ?>"
                                                            data-zbm-copy-msg="MCP 客户端配置已复制">
                                                        <?php echo zblogmcp_icon('filecode'); ?><span>复制配置</span>
                                                    </button>
                                                    <a class="zbm-btn zbm-btn--sm" href="key_edit.php?id=<?php echo $key_id; ?>">
                                                        <?php echo zblogmcp_icon('edit'); ?><span>编辑</span>
                                                    </a>
                                                    <a class="zbm-btn zbm-btn--sm zbm-btn--<?php echo $is_enabled ? 'warning' : 'success'; ?>"
                                                       href="<?php echo $toggle_url; ?>"
                                                       data-zbm-confirm="确定<?php echo $is_enabled ? '禁用' : '启用'; ?> ApiKey「<?php echo zblogmcp_h($key_name); ?>」？"
                                                       data-zbm-confirm-title="提示"
                                                       data-zbm-confirm-ok="确定">
                                                        <?php echo zblogmcp_icon($is_enabled ? 'ban' : 'check'); ?>
                                                        <span><?php echo $is_enabled ? '禁用' : '启用'; ?></span>
                                                    </a>
                                                    <a class="zbm-btn zbm-btn--danger zbm-btn--sm"
                                                       href="<?php echo $delete_url; ?>"
                                                       data-zbm-confirm="确定删除 ApiKey「<?php echo zblogmcp_h($key_name); ?>」？删除后不可恢复！"
                                                       data-zbm-confirm-title="警告"
                                                       data-zbm-confirm-ok="确定删除"
                                                       data-zbm-confirm-type="error">
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
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/foot.php'; ?>
