CREATE DATABASE IF NOT EXISTS mysql_tasks ENGINE = MySQL('mysql:3306', 'tasks', 'root', 'root')
    SETTINGS read_write_timeout=60, connect_timeout=10;
