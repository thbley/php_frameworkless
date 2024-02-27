create table if not exists migration (
    filename String not null,
    created_at DateTime not null,
    primary key (filename)
) ENGINE = MergeTree();