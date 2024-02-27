create table if not exists customer (
    id bigint unsigned not null auto_increment primary key,
    email varchar(255) not null,
    password varchar(128) not null,
    unique index(email)
) character set utf8mb4 collate utf8mb4_general_ci;