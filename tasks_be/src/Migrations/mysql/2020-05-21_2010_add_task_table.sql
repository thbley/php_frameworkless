create table if not exists task (
    id bigint unsigned not null auto_increment primary key,
    customer_id bigint unsigned not null,
    title varchar(255) not null,
    duedate date not null,
    completed tinyint not null,
    last_updated_by varchar(255) not null,
    index(customer_id, completed)
) character set utf8mb4 collate utf8mb4_general_ci;