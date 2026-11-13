# zblogMcp 功能说明

## 一、插件简介

`zblogMcp` 是一个用于连接 Z-BlogPHP 与 AI 客户端的 MCP 服务插件。安装并启用后，AI 客户端可以通过标准 MCP 协议访问博客内容，在 ApiKey 授权范围内完成文章、分类、标签、媒体和评论等数据的读取与管理。

## 二、主要功能

### 1. ApiKey 管理

- 新增、编辑、删除 ApiKey。
- ApiKey 支持启用和禁用。
- ApiKey 支持备注，便于区分不同 AI 客户端或使用场景。
- 后台列表中可以显示或隐藏完整 ApiKey，也可以一键复制。
- 支持一键复制包含服务地址和 Bearer Token 的 MCP 客户端配置。

### 2. 权限控制

每个 ApiKey 可以单独配置以下权限：

- **读取权限**：读取文章、文章详情、分类。
- **文章写入权限**：创建或更新文章。
- **分类管理权限**：创建分类。
- **标签管理权限**：查询、新增、更新和删除标签。
- **媒体管理权限**：查询、读取、上传和删除媒体文件。
- **评论管理权限**：查询、读取、创建、审核和删除评论。

未授权的 MCP 工具会被拒绝调用。建议按照实际用途创建不同 ApiKey，并只开启必要权限。

### 3. MCP 服务接口

服务地址：

```text
/zb_users/plugin/zblogMcp/mcp.php
```

当前提供的工具包括：

| 工具 | 功能 |
| --- | --- |
| `zblog_list_articles` | 分页查询文章，支持分类、关键词和状态筛选 |
| `zblog_get_article` | 获取单篇文章详情 |
| `zblog_write_article` | 创建或更新文章 |
| `zblog_list_categories` | 查询文章分类 |
| `zblog_create_category` | 创建文章分类 |
| `zblog_list_tags` | 查询标签及使用数量 |
| `zblog_get_tag` | 获取标签详情 |
| `zblog_save_tag` | 创建或更新标签 |
| `zblog_delete_tag` | 删除标签 |
| `zblog_list_media` | 查询媒体文件和附件 |
| `zblog_get_media` | 获取媒体详情和访问地址 |
| `zblog_upload_media` | 通过 Base64 上传媒体文件 |
| `zblog_delete_media` | 删除媒体记录及文件 |
| `zblog_list_comments` | 查询评论和审核状态 |
| `zblog_get_comment` | 获取评论详情 |
| `zblog_create_comment` | 创建评论或回复 |
| `zblog_review_comment` | 修改评论审核状态 |
| `zblog_delete_comment` | 删除评论及其子评论 |

### 4. MCP 请求日志

- 记录 MCP 请求方法、接口、状态码、响应时间、ApiKey、IP 和创建时间。
- 支持查看日志详情。
- 支持删除单条日志和清空日志。
- 支持按配置自动清理超过指定天数的旧日志。
- 支持关闭日志记录，或单独控制是否记录请求体和响应体。
- 日志中的 ApiKey 等敏感字段会进行脱敏处理。

### 5. 文章扩展字段

插件配置支持填写文章 `Meta` 字段结构说明。配置后，AI 客户端能够根据说明读取或写入文章扩展字段，适配主题或其他插件定义的自定义数据。

## 三、后台菜单

插件后台包含以下页面：

- **ApiKey 管理**：查看和管理所有 ApiKey。
- **新增 ApiKey**：创建 ApiKey 并设置权限。
- **MCP 日志**：查看和维护接口调用日志。
- **插件配置**：配置日志开关、日志保留天数、默认文章状态等。
- **接入配置**：查看 MCP 地址和客户端接入信息。
- **关于**：查看开发者信息、快速开始说明和赞助开发者链接。

## 四、接入方式

在支持远程 MCP 服务的 AI 客户端中添加以下配置，将其中的 `YOUR_API_KEY` 替换为后台创建的 ApiKey：

```json
{
  "mcpServers": {
    "zblog": {
      "url": "http://your-domain/zb_users/plugin/zblogMcp/mcp.php",
      "headers": {
        "Authorization": "Bearer YOUR_API_KEY"
      }
    }
  }
}
```

接入步骤：

1. 在 Z-BlogPHP 后台启用 `zblogMcp` 插件。
2. 进入“新增 ApiKey”，填写名称并设置权限。
3. 确认 ApiKey 处于启用状态。
4. 将 MCP 地址和 Bearer Token 配置到 AI 客户端。
5. 让 AI 客户端刷新 MCP 工具列表并开始调用。

### CLI 添加 ApiKey

插件启用后，可在站点根目录通过 CLI 添加 ApiKey。未传 `--key` 时会自动生成随机 ApiKey，成功后输出完整密钥：

```bash
php zb_users/plugin/zblogMcp/cli.php add-key --name="自动化客户端" --scopes="read,write"
```

可选参数：

- `--name`：必填，密钥名称。
- `--key`：可选，自定义 ApiKey。
- `--scopes`：可选，权限范围，支持 `read`、`write`、`category`、`tag`、`media`、`comment`，多个权限用逗号分隔，默认 `all`。
- `--remark`：可选，备注信息。

## 五、配置说明

- **日志开关**：控制是否记录 MCP 请求日志。
- **记录请求和响应内容**：控制日志中是否保存请求体、响应体。开启后仍会对敏感字段进行脱敏。
- **日志保留天数**：自动清理旧日志的天数，默认 30 天。
- **默认文章状态**：AI 创建文章时未传入 `status` 的默认状态，可设置为公开、草稿或审核中。
- **文章 Meta 字段说明**：填写扩展字段的名称、类型和用途，供 AI 客户端理解和维护自定义字段。

## 六、安全建议

- 不要把 ApiKey 提交到公开代码仓库、文章或截图中。
- 生产环境建议使用 HTTPS，避免 Bearer Token 在网络中明文传输。
- 为只读客户端关闭文章写入、分类、标签、媒体和评论权限。
- 定期禁用或删除不再使用的 ApiKey。
- 根据实际需要设置日志内容记录，避免保存过多业务数据。
