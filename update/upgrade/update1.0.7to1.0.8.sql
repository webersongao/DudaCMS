-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.8
-- @requires  1.0.7
-- @title     全站统一规范配置补齐
-- @summary   新增时区、默认外观、水印参数与视频压缩配置；统一展示与媒体处理行为
-- @summary   INSERT IGNORE 保证可重复执行
-- @verify    table:config
-- ============================================================================

INSERT IGNORE INTO `dask_config` (`id`,`name`,`group`,`title`,`tips`,`type`,`value`,`option`,`sort`,`settings`,`system`,`source`) VALUES
(518,'site_timezone','8','站点时区','全站统一时区，例如 Asia/Shanghai、UTC','text','Asia/Shanghai','null',1,NULL,1,0),
(519,'theme_mode','8','默认外观','light 浅色、dark 深色、auto 跟随系统','radio','auto','{"light":"浅色","dark":"深色","auto":"跟随系统"}',2,NULL,1,0),
(520,'watermark_font','4','水印字体路径','留空使用内置思源黑体；可填写服务器上的 TTF 字体绝对路径','text','','null',20,NULL,1,0),
(521,'watermark_position','4','水印位置','统一控制图片水印落点','radio','bottom-right','{"top-left":"左上","top-right":"右上","bottom-left":"左下","bottom-right":"右下","center":"居中"}',21,NULL,1,0),
(522,'watermark_font_size','4','水印字号','0 表示按图片尺寸自动计算','number','0','[]',22,NULL,1,0),
(523,'watermark_opacity','4','水印不透明度','0-100，数值越大越不透明','number','100','[]',23,NULL,1,0),
(524,'watermark_color','4','水印颜色','十六进制颜色值，例如 #ffffff','text','#ffffff','null',24,NULL,1,0),
(525,'watermark_max_lines','4','水印最大行数','超出自动省略','number','3','[]',25,NULL,1,0),
(526,'watermark_quality','4','水印输出质量','1-100，JPEG/WebP 使用；PNG 自动换算压缩级别','number','90','[]',26,NULL,1,0),
(527,'video_compress_enable','4','启用视频压缩','开启后上传视频使用 ffmpeg 转码，未安装 ffmpeg 时自动跳过','radio','N','{"Y":"启用","N":"不启用"}',30,NULL,1,0),
(528,'ffmpeg_path','4','ffmpeg 路径','留空使用系统 PATH 中的 ffmpeg','text','ffmpeg','null',31,NULL,1,0),
(529,'video_compress_crf','4','视频压缩质量','18-40，数值越大压缩率越高','number','28','[]',32,NULL,1,0),
(530,'video_compress_preset','4','视频编码预设','在速度与压缩率之间取舍','radio','veryfast','{"ultrafast":"极速","veryfast":"很快","medium":"均衡","slow":"高压缩"}',33,NULL,1,0),
(531,'video_compress_max_width','4','视频最大宽度','0 表示不限制；等比缩放并保持偶数尺寸','number','1280','[]',34,NULL,1,0);
