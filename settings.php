<?php
/**
 * zblogMcp - 插件配置 / 接入配置页
 */
require_once __DIR__ . '/header.php';

$settings_tab = GetVars('tab', 'GET');
if (!in_array($settings_tab, array('plugin', 'integration'), true)) {
    $settings_tab = 'plugin';
}

if (GetVars('act', 'GET') == 'save') {
    CheckIsRefererValid();
    $zblogmcp_config->log_enabled = (int) GetVars('log_enabled', 'POST');
    $zblogmcp_config->log_body_enabled = (int) GetVars('log_body_enabled', 'POST');
    $zblogmcp_config->log_max_days = (int) GetVars('log_max_days', 'POST');
    $zblogmcp_config->default_write_status = (int) GetVars('default_write_status', 'POST');
    $zblogmcp_config->article_meta_schema = trim((string) GetVars('article_meta_schema', 'POST'));
    $zbp->SaveConfig('zblogMcp');
    zblogmcp_redirect('settings.php?tab=' . $settings_tab, '设置已保存');
}

$mcp_url = $bloghost . 'zb_users/plugin/zblogMcp/mcp.php';

$tools = array(
    array('name' => 'zblog_list_articles', 'description' => '查询文章列表，支持分类、关键词和状态筛选'),
    array('name' => 'zblog_get_article', 'description' => '读取单篇文章的完整内容和元数据'),
    array('name' => 'zblog_write_article', 'description' => '创建或更新博客文章'),
    array('name' => 'zblog_list_categories', 'description' => '查询博客文章分类'),
    array('name' => 'zblog_create_category', 'description' => '创建新的文章分类'),
    array('name' => 'zblog_list_tags', 'description' => '查询博客标签及文章使用数量'),
    array('name' => 'zblog_get_tag', 'description' => '读取单个标签详情'),
    array('name' => 'zblog_save_tag', 'description' => '创建或更新博客标签'),
    array('name' => 'zblog_delete_tag', 'description' => '删除博客标签'),
    array('name' => 'zblog_list_media', 'description' => '查询媒体文件和附件'),
    array('name' => 'zblog_get_media', 'description' => '读取单个媒体文件详情和访问地址'),
    array('name' => 'zblog_upload_media', 'description' => '通过 Base64 上传图片或附件'),
    array('name' => 'zblog_delete_media', 'description' => '删除媒体记录及对应文件'),
    array('name' => 'zblog_list_comments', 'description' => '查询文章评论和审核状态'),
    array('name' => 'zblog_get_comment', 'description' => '读取单条评论详情'),
    array('name' => 'zblog_create_comment', 'description' => '为文章创建评论或回复'),
    array('name' => 'zblog_review_comment', 'description' => '切换评论的审核状态'),
    array('name' => 'zblog_delete_comment', 'description' => '删除评论及其子评论'),
);

$client_config = json_encode(array(
    'mcpServers' => array(
        'zblog' => array(
            'url'     => $mcp_url,
            'headers' => array('Authorization' => 'Bearer your_api_key_here'),
        ),
    ),
), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$clients = array(
    'claude'    => array('title' => 'Claude Desktop', 'tip' => '在 Claude Desktop 的配置文件中添加：'),
    'cursor'    => array('title' => 'Cursor / VS Code', 'tip' => '在 Cursor 或 VS Code 的 MCP 设置中添加：'),
    'workbuddy' => array('title' => 'WorkBuddy', 'tip' => '在 WorkBuddy 的 MCP Server 配置中添加：'),
    'tarework'  => array('title' => 'TareWork', 'tip' => '在 TareWork 的 MCP 工具配置中添加：'),
    'qclaw'     => array('title' => 'Qclaw', 'tip' => '在 Qclaw 的 MCP 服务配置中添加：'),
    'other'     => array('title' => '其他 AI 工具', 'tip' => '支持 HTTP MCP Server 的 AI 工具可参考以下配置：'),
);

$skill_config = implode("\n", array(
    '# zblog mcp',
    '',
    '通过 zblog mcp 管理博客内容。默认先读取和确认目标，再执行写入、标签、媒体或评论操作。',
    '文章默认遵守插件设置中的写入状态；涉及发布、删除和评论审核时，先向用户确认。',
    '媒体上传使用 Base64 数据，禁止上传可执行脚本或未授权文件。',
));

$log_enabled = !empty($zblogmcp_config->log_enabled);
$log_body_enabled = !empty($zblogmcp_config->log_body_enabled);
$log_max_days = (int) $zblogmcp_config->log_max_days;
$default_write_status = isset($zblogmcp_config->default_write_status) ? (int) $zblogmcp_config->default_write_status : ZC_POST_STATUS_AUDITING;
$article_meta_schema = isset($zblogmcp_config->article_meta_schema) ? (string) $zblogmcp_config->article_meta_schema : '';
?>
<div id="divMain" class="plugin-area">
    <div class="divHeader"><?php echo zblogmcp_h($zblogmcp_plugin_info['name']); ?></div>
    <div class="SubMenu"><?php echo zblogmcp_SubMenu(); ?></div>
    <div id="divMain2">
        <div class="zbm-app">
            <?php if ($settings_tab === 'plugin') : ?>
                <form method="post" action="settings.php?act=save&amp;tab=<?php echo urlencode($settings_tab); ?>">
                    <input type="hidden" name="csrfToken" value="<?php echo zblogmcp_h($zbp->GetCSRFToken()); ?>">
                    <div class="plugin-settings-grid">
                        <div class="zbm-card">
                            <div class="zbm-card__header"><span>日志设置</span></div>
                            <div class="zbm-card__body">
                                <div class="zbm-form-item">
                                    <span class="zbm-form-item__label">记录 MCP 请求日志</span>
                                    <div class="zbm-form-item__content">
                                        <input type="hidden" name="log_enabled" value="0">
                                        <label class="zbm-switch" data-on-text="开启" data-off-text="关闭">
                                            <input type="checkbox" name="log_enabled" value="1" <?php echo $log_enabled ? 'checked' : ''; ?>>
                                            <span class="zbm-switch__slider"></span>
                                        </label>
                                        <span class="zbm-switch-label"><?php echo $log_enabled ? '开启' : '关闭'; ?></span>
                                    </div>
                                </div>
                                <div class="zbm-form-item">
                                    <label class="zbm-form-item__label" for="zbm-log-days">日志保留天数</label>
                                    <div class="zbm-form-item__content">
                                        <div class="zbm-row">
                                            <input class="zbm-input zbm-input--num" type="number" id="zbm-log-days"
                                                   name="log_max_days" min="1" max="365" step="1"
                                                   value="<?php echo $log_max_days; ?>">
                                            <span class="zbm-muted">超过天数的日志将在清理时自动删除</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="zbm-card">
                            <div class="zbm-card__header"><span>MCP 安全配置</span></div>
                            <div class="zbm-card__body">
                                <div class="zbm-form-item">
                                    <span class="zbm-form-item__label">记录请求/响应正文</span>
                                    <div class="zbm-form-item__content">
                                        <input type="hidden" name="log_body_enabled" value="0">
                                        <label class="zbm-switch" data-on-text="开启" data-off-text="关闭">
                                            <input type="checkbox" name="log_body_enabled" value="1" <?php echo $log_body_enabled ? 'checked' : ''; ?>>
                                            <span class="zbm-switch__slider"></span>
                                        </label>
                                        <span class="zbm-switch-label"><?php echo $log_body_enabled ? '开启' : '关闭'; ?></span>
                                    </div>
                                </div>
                                <div class="zbm-form-item">
                                    <span class="zbm-form-item__label">文章默认写入状态</span>
                                    <div class="zbm-form-item__content">
                                        <div class="zbm-check-group">
                                            <label class="zbm-check">
                                                <input type="radio" name="default_write_status" value="0" <?php echo ($default_write_status === 0) ? 'checked' : ''; ?>>公开
                                            </label>
                                            <label class="zbm-check">
                                                <input type="radio" name="default_write_status" value="1" <?php echo ($default_write_status === 1) ? 'checked' : ''; ?>>草稿
                                            </label>
                                            <label class="zbm-check">
                                                <input type="radio" name="default_write_status" value="2" <?php echo ($default_write_status === 2) ? 'checked' : ''; ?>>审核
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="zbm-card">
                            <div class="zbm-card__header"><span>文章扩展字段说明</span></div>
                            <div class="zbm-card__body">
                                <p class="zbm-note zbm-mb8">用于AI识别某些第三方插件或者主题自定义的文章扩展字段，如没有安装过类似的插件无需填写。每行一个字段，格式：字段名=说明。未填写时，AI 会根据字段名、值格式和主题/插件上下文自行推断，无法确认时不会擅自覆盖字段。</p>
                                <textarea class="zbm-textarea" name="article_meta_schema" rows="8"
                                          placeholder="seo_title=SEO标题，用于搜索引擎展示&#10;cover_image=封面图，填写附件ID或图片URL&#10;source_url=原文链接，仅转载文章填写"><?php echo zblogmcp_h($article_meta_schema); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button type="submit" class="zbm-btn zbm-btn--primary">
                            <?php echo zblogmcp_icon('save'); ?><span>保存设置</span>
                        </button>
                    </div>
                </form>
            <?php else : ?>
                <div class="zbm-card mcp-info-card">
                    <div class="zbm-card__header">
                        <span class="zbm-row"><?php echo zblogmcp_icon('zap'); ?><span>MCP 接入信息</span></span>
                    </div>
                    <div class="zbm-card__body">
                        <div class="zbm-desc">
                            <div class="zbm-desc__row">
                                <div class="zbm-desc__label">MCP 端点</div>
                                <div class="zbm-desc__value">
                                    <div class="zbm-row">
                                        <span class="zbm-url-chip"><?php echo zblogmcp_h($mcp_url); ?></span>
                                        <button type="button" class="zbm-btn zbm-btn--sm"
                                                data-zbm-copy="<?php echo zblogmcp_h($mcp_url); ?>"
                                                data-zbm-copy-msg="MCP 端点已复制">
                                            <?php echo zblogmcp_icon('copy'); ?><span>复制</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="zbm-desc__row">
                                <div class="zbm-desc__label">认证方式</div>
                                <div class="zbm-desc__value"><span class="zbm-tag">Bearer Token</span></div>
                            </div>
                            <div class="zbm-desc__row">
                                <div class="zbm-desc__label">协议版本</div>
                                <div class="zbm-desc__value"><span class="zbm-tag zbm-tag--info">2024-11-05</span></div>
                            </div>
                            <div class="zbm-desc__row">
                                <div class="zbm-desc__label">可用工具</div>
                                <div class="zbm-desc__value">
                                    <div class="zbm-tool-list">
                                        <?php foreach ($tools as $tool) : ?>
                                            <div class="zbm-tool-item">
                                                <span class="zbm-tag zbm-tag--plain"><code><?php echo zblogmcp_h($tool['name']); ?></code></span>
                                                <span class="zbm-tool-desc"><?php echo zblogmcp_h($tool['description']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="zbm-card mcp-info-card">
                    <div class="zbm-card__header"><span>客户端配置示例</span></div>
                    <div class="zbm-card__body">
                        <div data-zbm-tabs>
                            <div class="zbm-tabs__nav">
                                <?php $first = true; ?>
                                <?php foreach ($clients as $client_key => $client) : ?>
                                    <button type="button" class="zbm-tab<?php echo $first ? ' is-active' : ''; ?>" data-zbm-tab="<?php echo zblogmcp_h($client_key); ?>"><?php echo zblogmcp_h($client['title']); ?></button>
                                    <?php $first = false; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php $first = true; ?>
                            <?php foreach ($clients as $client_key => $client) : ?>
                                <div class="zbm-tab__panel<?php echo $first ? ' is-active' : ''; ?>" data-zbm-panel="<?php echo zblogmcp_h($client_key); ?>">
                                    <p class="zbm-note zbm-mb8"><?php echo zblogmcp_h($client['tip']); ?></p>
                                    <pre class="zbm-code zbm-code--flat" id="zbm-cfg-<?php echo zblogmcp_h($client_key); ?>"><?php echo zblogmcp_h($client_config); ?></pre>
                                    <div class="zbm-mt8">
                                        <button type="button" class="zbm-btn zbm-btn--sm"
                                                data-zbm-copy-from="#zbm-cfg-<?php echo zblogmcp_h($client_key); ?>"
                                                data-zbm-copy-msg="客户端配置已复制">
                                            <?php echo zblogmcp_icon('filecode'); ?><span>复制配置</span>
                                        </button>
                                    </div>
                                </div>
                                <?php $first = false; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="zbm-card mcp-info-card">
                    <div class="zbm-card__header"><span>技能配置</span></div>
                    <div class="zbm-card__body">
                        <p class="zbm-note zbm-mb8">可复制到支持“技能”或“自定义指令”的 AI 客户端，用于约束博客管理操作。</p>
                        <pre class="zbm-code zbm-code--flat" id="zbm-skill-config"><?php echo zblogmcp_h($skill_config); ?></pre>
                        <div class="zbm-mt8">
                            <button type="button" class="zbm-btn zbm-btn--sm"
                                    data-zbm-copy-from="#zbm-skill-config"
                                    data-zbm-copy-msg="技能配置已复制">
                                <?php echo zblogmcp_icon('sparkle'); ?><span>复制技能配置</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/foot.php'; ?>
