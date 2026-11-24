# DudaCMS 简介

> 当前版本：**DudaCMS 1.0.2**（Build 20261124）

---

DudaCMS 是 DudaCMS团队推出的一款基于 TP8 的社交化知识付费问答系统与企业内部知识库系统，用于打造私有社交化问答和内部知识存储。

DudaCMS is a TP8-powered social Q&A and internal knowledge base platform.

### 品牌区分规范（务必遵守）

本项目涉及两个不同层级的名称，严禁混用：

| 名称 | 含义 | 使用场景 | 取值方式 |
|---|---|---|---|
| **DudaCMS** | 源码 / 程序的品牌名 | 后台管理界面与 Logo、系统版本信息、版权声明、README、PWA 静态资源、邮件模板 Logo、源码级文案 | 写死为 `DudaCMS`（源码品牌，不随部署变化） |
| **站点名** | 用本程序运营的具体站点名称 | 前台页面标题、导航 Logo、页脚版权、`关于/隐私协议` 等站点文案、SEO 元信息、站务邮件署名 | 一律读取配置 `get_setting('site_name')`，**代码中禁止写死** |

- 安装种子 `install.sql` 的站点名留空，运营站点在安装后自行填写。
- 语言包里需要引用站点名时，用 `%s` 占位句型（如 `'关于 %s'`），调用处传 `site_name`，不得把具体站点名编进翻译值。
- 新增后台界面时品牌用 `DudaCMS`；新增前台展示站点名时一律走 `site_name` 配置。

### DudaCMS 的下载

您可以随时从官方站点获取最新版本与补丁：

https://ask.btbk.org/

### DudaCMS 问答系统的环境需求

> 本项目基于 **ThinkPHP 8.1**，环境要求以 ThinkPHP 8 及其依赖为准。

1. 可用的 www 服务器，如 Apache、IIS、nginx，推荐使用性能高效的 Apache 或 nginx。
2. **PHP 8.2 及以上**（推荐 8.3 / 8.4），必须启用 PDO_MySQL、mbstring、json、gd、zip、curl、fileinfo、openssl、iconv、zlib、sockets 扩展。
3. MySQL 5.7 及以上（推荐 8.0 / 8.4），字符集 utf8mb4，服务器需要支持 PDO_MySQL。
4. GD 图形库（需包含 JPEG / PNG / WebP / FreeType 支持）。
5. Composer 2.x（仅升级/安装依赖时需要）。

### 开发环境

```bash
cp .example.env .env                  # 填写数据库与密钥
composer install --no-dev             # 生产依赖
composer audit --no-dev
```

前台资源全部是仓库内静态文件，运行与部署不依赖 Node、Vite 或任何前端打包工具链。

- API v1：`/api.php/v1`，文档 `/api-docs`；
- PWA：同一套 SSR 页面 + `/sw.js` 离线外壳，安装清单在 `/pwa/manifest.json`；

全新安装 SQL：`install/sql/install.sql`。

后续升级：涉及数据库变更的版本，随代码同步 `update/upgrade/` 目录下的幂等升级 SQL（文件名如 `update1.0.0to1.0.1.sql`），管理员在后台「系统运维 → 在线升级」页面即可看到待升级版本并一键执行；升级前自动全库备份、失败自动回滚，相邻版本逐级升级，不支持跨版本。

### DudaCMS 问答系统的安装

### 全新安装

①、获取最新版的DudaCMS程序源码。

②、把程序上传到网站根目录，并且设置运行目录为 public

网站根目录，就是存放网站代码的地方，例如用虚拟主机的，那么网站根目录就是你的虚拟主机的主目录了。

运行目录，简单来说，就是你网站根目录下的一个文件夹。出于安全考虑，程序的入口文件放在 public 目录，需要把运行目录指向它。

运行目录去哪设置呢？

用宝塔面板举个例子，就是下面这个，其他的管理面板，虚拟主机都类似，仔细找一找，肯定有运行目录这个东西了。

③、设置伪静态规则，宝塔面板可以直接在伪静态选择 thinkphp 伪静态规则

④、访问网站域名会跳转到 `/install.php`；也可以直接访问 `/install/`，两者使用同一套图形化安装逻辑。

这时候，我们根据提示，一步步操作，然后你就会来到填写数据库的界面。

这里有个需要特别注意的，就是下面的这个后台地址，默认是admin，但是为了提高安全性，建议你设置一个复杂的名字，随便你写，只要自己容易记住，别人记不住的就尽管写！比如我写的就是 jxxadmin，那么我访问后台的时候，就不是

https://ask.btbk.org/admin.php 了，而是 https://ask.btbk.org/jxxadmin.php

然后执行下一步，如果顺利的话，就会安装成功了！

#### URL 重写规则

可以通过 URL 重写隐藏应用入口文件 `index.php`（也可以是其它入口文件，但 URL 重写通常只能设置一个入口文件）。以下是常见服务器的配置参考。

**Apache**

`httpd.conf` 中加载 `mod_rewrite.so` 模块，将 `AllowOverride None` 改为 `AllowOverride All`。把以下内容保存为 `.htaccess`，放到应用入口文件同级目录：

```apache
<IfModule mod_rewrite.c>
Options +FollowSymlinks -Multiviews
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```

**IIS**

如果服务器支持 ISAPI_Rewrite，可在 `httpd.ini` 中添加：

```ini
RewriteRule (.*)$ /index\.php\?s=$1 [I]
```

高版本 IIS 可在 `web.config` 中添加 rewrite 节点：

```xml
<rewrite>
  <rules>
    <rule name="OrgPage" stopProcessing="true">
      <match url="^(.*)$" />
      <conditions logicalGrouping="MatchAll">
        <add input="{HTTP_HOST}" pattern="^(.*)$" />
        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
      </conditions>
      <action type="Rewrite" url="index.php/{R:1}" />
    </rule>
  </rules>
</rewrite>
```

**Nginx**

低版本 Nginx 不支持 PATHINFO，可通过在 `nginx.conf` 中配置转发规则实现：

```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
    }
}
```

