# House-forum

## Introduction

ICT SBA Project

## Database tables

### `forum` table

Used to store information of individual forums

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
fId | char(3) | `PRIMARY KEY`
fName | varchar(30) | `NOT NULL`
fDescription | varchar(100) |
hId | char(3) | `UNIQUE`, `FOREIGN KEY REFERENCING house(hId)`

### `house` table

Used to store information of the houses

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
hId | char(3) | `PRIMARY KEY`
houseName | varchar(20) | `NOT NULL`

### `permission` table

Stores a set of permission that is used in the code

Have a set of default data that should **NOT** be changed

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
permisison | char(3) | `PRIMARY KEY`
permissionDescription | varchar(100) | `NOT NULL`

### `session` table

Used to store information of sessions

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
sessionId | char(40) | `PRIMARY KEY`
studentId | char(7) | `FOREIGN KEY REFERENCING users(studentId)`
lastActivity | int(10) | `NOT NULL`

### `thread` table

Used to store information of threads

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
tId | char(10) | `PRIMARY KEY`
tTitle | varchar(40) | `NOT NULL`
tContent | text | `NOT NULL`
tTime | char(10) | `NOT NULL`
fId | char(3) | `NOT NULL`
studentId | char(7)| `FOREIGN KEY REFERENCING users(studentId)`
