<?php
/**
 * zblogMcp 后台公共头部
 * @var object $zbp
 * @var string $current_file
 * @var string $bloghost
 * @var string $blogpath
 * @var $zblogmcp_config
 */
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
    $zbp->ShowError(6);
    die();
}
$zblogmcp_plugin_info = include __DIR__ . '/config/plugin_info.php';
if (!$zbp->CheckPlugin($zblogmcp_plugin_info['id'])) {
    $zbp->ShowError(48);
    die();
}
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<!-- 原生样式与脚本（本地静态文件，无第三方依赖） -->
<link rel="stylesheet" href="<?php echo $bloghost; ?>zb_users/plugin/zblogMcp/static/css/admin.css">
<script src="<?php echo $bloghost; ?>zb_users/plugin/zblogMcp/static/js/admin.js"></script>
<script>
$(function () {
    $('#nav_plugin').removeClass('on');
    $('#nav_zblogMcp').addClass('on');
});
</script>
