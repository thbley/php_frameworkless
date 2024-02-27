set allow_experimental_refreshable_materialized_view = 1;

create materialized view if not exists mysql_tasks_task_view
    refresh after 600 second
    ENGINE = MergeTree() order by id
    as select * from mysql_tasks.task;

create materialized view if not exists mysql_tasks_task_view2
    refresh after 600 second
    ENGINE = MergeTree() order by customer_id
    as select customer_id, count() as count from mysql_tasks.task group by customer_id;
