# php-mysql-translation

## INSTALLATION

1. Create required database table

```sql
create table translation
(
    id      int unsigned auto_increment        primary key,
    `key`   varchar(255)                       not null,
    de      text                               not null,
    en      text                               null,
    created datetime default CURRENT_TIMESTAMP not null,
    updated datetime                           null on update CURRENT_TIMESTAMP,
    constraint `key`
        unique (`key`)
);
```
