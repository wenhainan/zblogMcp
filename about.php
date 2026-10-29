<?php
/**
 * zblogMcp - 关于页
 */
require_once __DIR__ . '/header.php';
?>
<div id="divMain" class="plugin-area">
    <div class="divHeader"><?php echo zblogmcp_h($zblogmcp_plugin_info['name']); ?></div>
    <div class="SubMenu"><?php echo zblogmcp_SubMenu(); ?></div>
    <div id="divMain2">
        <div class="zbm-app">
            <div class="zbm-card" style="max-width:760px;">
                <div class="zbm-card__header">
                    <span class="zbm-row"><?php echo zblogmcp_icon('info'); ?><span>关于</span></span>
                </div>
                <div class="zbm-card__body">
                    <div class="zbm-desc">
                        <div class="zbm-desc__row">
                            <div class="zbm-desc__label">开发者</div>
                            <div class="zbm-desc__value">
                                <a href="https://blog.waytomilky.com/" target="_blank" rel="noopener noreferrer">玄火</a>
                            </div>
                        </div>
                        <div class="zbm-desc__row">
                            <div class="zbm-desc__label">其他作品</div>
                            <div class="zbm-desc__value">
                                <a href="https://app.zblogcn.com/?auth=8a4f3086-399a-4543-a0be-24c676a0a6b5" target="_blank" rel="noopener noreferrer">查看其他作品</a>
                            </div>
                        </div>
                        <div class="zbm-desc__row">
                            <div class="zbm-desc__label">QQ</div>
                            <div class="zbm-desc__value">610176732</div>
                        </div>
                        <div class="zbm-desc__row">
                            <div class="zbm-desc__label">QQ技术群</div>
                            <div class="zbm-desc__value">335011903</div>
                        </div>
                        <div class="zbm-desc__row">
                            <div class="zbm-desc__label">赞助开发者</div>
                            <div class="zbm-desc__value">
                                <a href="https://blog.waytomilky.com/14.html" target="_blank" rel="noopener noreferrer">支持开发者</a>
                            </div>
                        </div>
                        <div class="zbm-desc__row">
                            <div class="zbm-desc__label">开发者声明</div>
                            <div class="zbm-desc__value">本插件由「玄火」捐助社区，助力 zblog 社区发展壮大！</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="zbm-card" style="max-width:760px;">
                <div class="zbm-card__header">
                    <span class="zbm-row"><?php echo zblogmcp_icon('compass'); ?><span>快速开始</span></span>
                </div>
                <div class="zbm-card__body">
                    <div class="zbm-steps">
                        <div class="zbm-step">
                            <div class="zbm-step__dot">1</div>
                            <div class="zbm-step__title">创建 ApiKey</div>
                            <div class="zbm-step__desc">新增或选择一个已启用的 ApiKey，确认权限范围满足 Claw 使用场景。</div>
                        </div>
                        <div class="zbm-step">
                            <div class="zbm-step__dot">2</div>
                            <div class="zbm-step__title">复制配置</div>
                            <div class="zbm-step__desc">点击操作栏的“复制配置”，自动复制包含 MCP 地址和 Bearer Token 的 JSON。</div>
                        </div>
                        <div class="zbm-step">
                            <div class="zbm-step__dot">3</div>
                            <div class="zbm-step__title">粘贴到 Claw</div>
                            <div class="zbm-step__desc">打开 Claw 的 MCP 服务配置，将 JSON 粘贴保存，刷新或重启后即可调用博客工具。</div>
                        </div>
                    </div>

                    <div class="zbm-alert zbm-alert--info">
                        <?php echo zblogmcp_icon('info'); ?>
                        <span>复制配置会按当前站点域名和当前行 ApiKey 自动组装，可直接用于支持 HTTP MCP Server 的 Claw 工具。</span>
                    </div>
                    <div class="zbm-alert zbm-alert--warning" style="margin-top:10px;">
                        <?php echo zblogmcp_icon('warning'); ?>
                        <span>如果 MCP 配置入口比较难找，可以把复制的配置发给 Claw，并提示它“请帮我把这段 MCP 配置添加到当前工具的 MCP 服务配置中”。</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/foot.php'; ?>
