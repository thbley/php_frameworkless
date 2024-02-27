set allow_experimental_object_type = 1;

create table if not exists stream_tasks (
    last_updated DateTime not null,
    last_updated_by String not null,
    data JSON not null
) ENGINE = MergeTree() order by last_updated;
