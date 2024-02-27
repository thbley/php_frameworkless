create table if not exists task_queue (
    task_id bigint unsigned not null primary key,
    num_tries tinyint unsigned not null,
    last_try datetime not null,
    index(num_tries)
) character set utf8mb4 collate utf8mb4_general_ci;
