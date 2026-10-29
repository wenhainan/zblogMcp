<?php
/**
 * zblogMcp - ApiKey 新增/编辑页
 */
require_once __DIR__ . '/header.php';

$is_edit = false;
$key_data = array(
    'id' => 0,
    'name' => '',
    'api_key' => '',
    'status' => 1,
    'scope_read' => 1,
    'scope_write' => 1,
    'scope_category' => 1,
    'scope_tag' => 1,
    'scope_media' => 1,
    'scope_comment' => 1,
    'remark' => '',
);

if (GetVars('id', 'GET')) {
    $is_edit = true;
    $row = zblogmcp_get_key(GetVars('id', 'GET'));
    if ($row) {
        $key_data = array_merge($key_data, $row);
    } else {
        zblogmcp_error('ApiKey 记录不存在');
    }
}

if (GetVars('act', 'GET') == 'save') {
    CheckIsRefererValid();
    if ($is_edit || GetVars('id', 'POST')) {
        zblogmcp_update_key($_POST);
    } else {
        zblogmcp_add_key($_POST);
    }
}

# 新增时自动填充一个随机 ApiKey
if (!$is_edit && $key_data['api_key'] === '') {
    $key_data['api_key'] = zblogmcp_generate_key();
}

$input_type = $is_edit ? 'password' : 'text';
$scopes = array(
    'scope_read'     => '读取文章',
    'scope_write'    => '写入文章',
    'scope_category' => '管理分类',
    'scope_tag'      => '标签管理',
    'scope_media'    => '媒体管理',
    'scope_comment'  => '评论管理',
);
?>
<div id="divMain" class="plugin-area">
    <div class="divHeader"><?php echo zblogmcp_h($zblogmcp_plugin_info['name']); ?></div>
    <div class="SubMenu"><?php echo zblogmcp_SubMenu(); ?></div>
    <div id="divMain2">
        <div class="zbm-app">
            <div class="zbm-card" style="max-width:760px;">
                <div class="zbm-card__header">
                    <span><?php echo $is_edit ? '编辑 ApiKey' : '新增 ApiKey'; ?></span>
                </div>
                <div class="zbm-card__body">
                    <form method="post" action="key_edit.php?act=save">
                        <input type="hidden" name="csrfToken" value="<?php echo zblogmcp_h($zbp->GetCSRFToken()); ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $key_data['id']; ?>">

                        <div class="zbm-form-item">
                            <label class="zbm-form-item__label" for="zbm-name"><span class="zbm-req">*</span>名称</label>
                            <div class="zbm-form-item__content">
                                <input class="zbm-input" type="text" id="zbm-name" name="name" maxlength="100" required
                                       placeholder="例如：生产环境 Key、测试 Key"
                                       value="<?php echo zblogmcp_h($key_data['name']); ?>">
                            </div>
                        </div>

                        <div class="zbm-form-item">
                            <label class="zbm-form-item__label" for="zbm-api-key"><span class="zbm-req">*</span>ApiKey</label>
                            <div class="zbm-form-item__content">
                                <div class="zbm-row">
                                    <input class="zbm-input zbm-input--key" type="<?php echo $input_type; ?>" id="zbm-api-key"
                                           name="api_key" maxlength="500" required placeholder="输入或生成 ApiKey"
                                           value="<?php echo zblogmcp_h($key_data['api_key']); ?>" style="flex:1; min-width:220px;">
                                    <button type="button" class="zbm-btn zbm-btn--sm"
                                            data-zbm-toggle-input="#zbm-api-key"
                                            data-label-full="隐藏"
                                            data-label-mask="显示"><?php echo $is_edit ? '显示' : '隐藏'; ?></button>
                                    <button type="button" class="zbm-btn zbm-btn--primary zbm-btn--plain zbm-btn--sm"
                                            data-zbm-random-key="#zbm-api-key">
                                        <?php echo zblogmcp_icon('sparkle'); ?><span>随机生成</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="zbm-form-item">
                            <span class="zbm-form-item__label">状态</span>
                            <div class="zbm-form-item__content">
                                <div class="zbm-check-group">
                                    <label class="zbm-check">
                                        <input type="radio" name="status" value="1" <?php echo ((int) $key_data['status'] === 1) ? 'checked' : ''; ?>>启用
                                    </label>
                                    <label class="zbm-check">
                                        <input type="radio" name="status" value="0" <?php echo ((int) $key_data['status'] === 1) ? '' : 'checked'; ?>>禁用
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="zbm-form-item">
                            <span class="zbm-form-item__label">权限范围</span>
                            <div class="zbm-form-item__content">
                                <div class="zbm-check-group">
                                    <?php foreach ($scopes as $field => $title) : ?>
                                        <label class="zbm-check">
                                            <input type="hidden" name="<?php echo $field; ?>" value="0">
                                            <input type="checkbox" name="<?php echo $field; ?>" value="1"
                                                <?php echo !empty($key_data[$field]) ? 'checked' : ''; ?>>
                                            <?php echo zblogmcp_h($title); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="zbm-form-item">
                            <label class="zbm-form-item__label" for="zbm-remark">备注</label>
                            <div class="zbm-form-item__content">
                                <textarea class="zbm-textarea" id="zbm-remark" name="remark" rows="3" maxlength="500"
                                          placeholder="可选备注信息"><?php echo zblogmcp_h($key_data['remark']); ?></textarea>
                            </div>
                        </div>

                        <div class="zbm-form-item">
                            <span class="zbm-form-item__label"></span>
                            <div class="zbm-form-item__content">
                                <button type="submit" class="zbm-btn zbm-btn--primary">
                                    <?php echo zblogmcp_icon('save'); ?><span>保存</span>
                                </button>
                                <a class="zbm-btn" href="main.php">
                                    <?php echo zblogmcp_icon('back'); ?><span>返回列表</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/foot.php'; ?>
