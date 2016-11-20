CREATE TABLE `fx_feedback` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
    `openid` varchar(100) NOT NULL COMMENT '微信用户openid',
    `content` text NOT NULL COMMENT '意见反馈内容',
    `appid` varchar(100) NOT NULL COMMENT '公众号appid',
    `create_time` datetime NOT NULL COMMENT '创建时间' ,
    PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(意见反馈表)';

CREATE TABLE `fx_feedback_picture` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
    `pic_url` varchar(100) NOT NULL COMMENT '图片路径',
    `fid` int(11) NOT NULL COMMENT '意见反馈id',
    PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='(意见反馈所上传的图片表)';