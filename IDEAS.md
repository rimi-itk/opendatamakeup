# Ideas

## Using a database as data storage

The general idea is to use a database as storage and run simple transformations as sql queries and use code for more elaborate transforms.

1. Load data from external source, convert to csv, create a database table and load the data into it.
2. Run transformations:
   1. Create new database table with schema matching the result of the transformation
   2. If the transformation is simple, generate sql query to copy data from previous table to new table
      Otherwise, process the rows from previous table one by one and insert into new table
   3. Repeat.

3. Load data from final table and
   * Convert to output format
   * or push to data lake (SQL)
   * or …


## Transformations



| Name                              | Simple?  | Comments                                                        |
|-----------------------------------|----------|-----------------------------------------------------------------|
| [select columns](#select-columns) | yes      | Can easily be done in SQL.                                      |
| [rename columns](#rename-columns) | yes      | Can easily be done in SQL.                                      |
| [expand column](#expand-column)   | no       | Expand JSON data into multiple new columns.                     |
| [calculate](#calculate)           | probably | Most calculation can be done in SQL.                            |
| [filter](#filter)                 | maybe    | Some filters can be handled with SQL. Others will require code. |
| [replace](#replace)               | no/maybe | Probably easiest to always handle in code.                      |
| [combine](#combine)               | yes      | A SQL table join.                                               |

### select columns

Parameters

| Name    | Type                | Required | Default value | Comments |
|---------|---------------------|----------|---------------|----------|
| columns | list of column name | yes      |               |          |

### rename column

| Name | Type        | Required | Default value | Comments |
|------|-------------|----------|---------------|----------|
| from | column name | yes      |               |          |
| to   | column name | yes      |               |          |

### expand column

| Name    | Type                             | Required | Default value | Comments                                         |
|---------|----------------------------------|----------|---------------|--------------------------------------------------|
| column  | column name                      | yes      |               |                                                  |
| columns | map(column name → property path) | yes      |               | add new columns with values from original column |
| remove  | bool                             | no       | true          | if set, the original column will be removed      |

Should we always remove the original column? Or have a parameter controlling it?

### calculate

Very simple calculation; two operands and an operator.

| Name     | Type        | Required | Default value | Comments |
|----------|-------------|----------|---------------|----------|
| left     | column name | yes      |               |          |
| operator | +, -, *, /  | yes      |               |          |
| right    | column name | yes      |               |          |

### filter

| Name        | Type        | Required | Default value | Comments                                                                |
|-------------|-------------|----------|---------------|-------------------------------------------------------------------------|
| column      | column name | yes      |               |                                                                         |
| match       | string      | yes      |               | The string to match                                                     |
| partial | bool | no | false | If set, only partial match required |
| ignore case | bool | no | false | |
| regexp | bool | no | false | If set, use regexp match |
| include | bool | no | true | If set, matching rows will be included. Otherwise they will be removed. |

### replace

| Name        | Type                | Required | Default value | Comments                                                                |
|-------------|---------------------|----------|---------------|-------------------------------------------------------------------------|
| columns     | list of column name | yes      |               |                                                                         |
| search      | string              | yes      |               | The string to search for                                                |
| replacement | string              | yes      |               | The replacement text                                                    |
| ignore case | bool                | no       | false         |                                                                         |
| regexp      | bool                | no       | false         | If set, use regexp match                                                |

### combine

Combines two datasets.

| Name        | Type                           | Required | Default value | Comments                                                        |
|-------------|--------------------------------|----------|---------------|-----------------------------------------------------------------|
| columns     | map(column name → column name) | no       |               | The columns to join on. If not set, create a cartesian product. |
| join_type   | string                         | yes      | inner         | “inner”, “outer”                                                |
