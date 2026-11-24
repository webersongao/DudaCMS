-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.2
-- @requires  1.0.1
-- @title     安全加固、统计回刷与查询索引升级
-- @summary   修复回答数/赞同数统计口径；补充内容表复合索引与全文索引；修正失效种子图路径
-- @summary   所有 DDL 幂等，可安全重复执行
-- @verify    index:article.idx_article_status_time, index:answer.idx_answer_question_status_time, index:topic_relation.idx_tr_topic_status_time, index:question.idx_q_status_time
-- ============================================================================
--
-- 注意：FULLTEXT 索引会增加写入成本，大表请在低峰期执行；升级器会自动事务执行并在失败时回滚。
--

-- 1. 内容表复合索引：列表/搜索/统计高频条件
SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_article` ADD INDEX `idx_article_status_time` (`status`,`create_time`)', 'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_article' AND INDEX_NAME='idx_article_status_time');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_article` ADD INDEX `idx_article_status_popular` (`status`,`popular_value`)', 'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_article' AND INDEX_NAME='idx_article_status_popular');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_answer` ADD INDEX `idx_answer_question_status_time` (`question_id`,`status`,`create_time`)', 'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_answer' AND INDEX_NAME='idx_answer_question_status_time');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_topic_relation` ADD INDEX `idx_tr_topic_status_time` (`topic_id`,`status`,`create_time`)', 'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_topic_relation' AND INDEX_NAME='idx_tr_topic_status_time');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_question` ADD INDEX `idx_q_status_time` (`status`,`create_time`)', 'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_question' AND INDEX_NAME='idx_q_status_time');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. 全文索引：中文检索依赖 ngram 分词（MySQL 5.7+）
SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_question` ADD FULLTEXT INDEX `ft_question_search_text` (`search_text`) WITH PARSER ngram', 'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_question' AND INDEX_NAME='ft_question_search_text');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_article` ADD FULLTEXT INDEX `ft_article_search_text` (`search_text`) WITH PARSER ngram', 'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_article' AND INDEX_NAME='ft_article_search_text');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_answer` ADD FULLTEXT INDEX `ft_answer_search_text` (`search_text`) WITH PARSER ngram', 'SELECT 1')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_answer' AND INDEX_NAME='ft_answer_search_text');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. 统计回刷：用户回答数按作者重新统计
UPDATE `dask_users` u
SET u.`answer_count` = (SELECT COUNT(*) FROM `dask_answer` a WHERE a.`uid` = u.`uid` AND a.`status` = 1)
WHERE u.`uid` IN (SELECT DISTINCT `uid` FROM `dask_answer` WHERE `uid` > 0);

-- 4. 统计回刷：用户被赞同数只统计 question/answer/article，排除评论赞
UPDATE `dask_users` u
SET u.`agree_count` =
  (SELECT COUNT(*) FROM `dask_question_vote` qv
   WHERE qv.`item_uid` = u.`uid` AND qv.`vote_value` = 1 AND qv.`item_type` IN ('question','answer'))
  + (SELECT COUNT(*) FROM `dask_article_vote` av
     WHERE av.`item_uid` = u.`uid` AND av.`vote_value` = 1)
WHERE u.`uid` IN (
  SELECT `item_uid` FROM `dask_question_vote` WHERE `item_uid` > 0
  UNION
  SELECT `item_uid` FROM `dask_article_vote` WHERE `item_uid` > 0
);

-- 5. 修正示例数据中不存在的封面/话题图片路径
UPDATE `dask_article` SET `cover` = '/static/common/image/default-cover.svg'
WHERE `cover` LIKE '/static/common/image/seed/articles/%' OR `cover` LIKE 'static/common/image/seed/articles/%';
UPDATE `dask_topic` SET `pic` = '/static/common/image/topic.svg'
WHERE `pic` LIKE '/static/common/image/seed/topics/%' OR `pic` LIKE 'static/common/image/seed/topics/%';
