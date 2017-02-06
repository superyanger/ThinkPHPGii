create table if not exists <?=C('DB_PREFIX')?>auth (
`id` int unsigned not null primary key auto_increment,
`name` varchar(20) not null comment '名称',
`parent_id` int unsigned not null default 0 comment '父权限',
`controller` varchar(150) not null default '' comment '控制器',
`action` varchar(150) not null default '' comment '方法',
`rank` int unsigned not null default 0 comment '排序',
`is_trash` enum('0','1') not null default '0' comment '在回收站'
) engine=innodb charset=utf8mb4 comment='权限';
create table if not exists <?=C('DB_PREFIX')?>role (
`id` int unsigned not null primary key auto_increment,
`name` varchar(20) not null comment '名称',
`rank` int unsigned not null default 0 comment '排序',
`is_trash` enum('0','1') not null default '0' comment '在回收站'
) engine=innodb charset=utf8mb4 comment='角色';
create table if not exists <?=C('DB_PREFIX')?>admin (
`id` int unsigned not null primary key auto_increment,
`name` varchar(20) not null comment '名称',
`password` char(32) not null comment '密码',
`rank` int unsigned not null default 0 comment '排序',
`is_trash` enum('0','1') not null default '0' comment '在回收站'
) engine=innodb charset=utf8mb4 comment='管理员';
create table if not exists <?=C('DB_PREFIX')?>role_auth (
`id` int unsigned not null primary key auto_increment,
`auth_id` int unsigned not null comment '权限',
`role_id` int unsigned not null comment '角色',
`login_status` enum('0','1') not null default '0' comment '登录状态',
`last_login` datetime not null default '1970-01-01 00:00:00' comment '上次登陆时间',
`rank` int unsigned not null default 0 comment '排序',
`is_trash` enum('0','1') not null default '0' comment '在回收站',
index `auth_id`(`auth_id`),
index `role_id`(`role_id`)
) engine=innodb charset=utf8mb4 comment='角色权限';
create table if not exists <?=C('DB_PREFIX')?>admin_role (
`id` int unsigned not null primary key auto_increment,
`role_id` int unsigned not null comment '角色',
`admin_id` int unsigned not null comment '管理员',
`rank` int unsigned not null default 0 comment '排序',
`is_trash` enum('0','1') not null default '0' comment '在回收站',
index `role_id`(`role_id`),
index `admin_id`(`admin_id`)
) engine=innodb charset=utf8mb4 comment='管理员角色';