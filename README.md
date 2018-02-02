# House-forum

## Introduction

ICT SBA Project

[Live website](https://heteropterous-recei.000webhostapp.com/sba/login.php)

## Other projects used

* [materialize](https://github.com/dogfalo/materialize/) - A CSS framework based on Material Design
* [Parsedown](https://github.com/erusev/parsedown) - Markdown parser in PHP
* [Highlight.js](https://github.com/isagalaev/highlight.js) - Javascript syntax highlighter

## To-do list

- [x] Change queries to prepared statement
- [x] Delete reply
- [x] Pin thread
- [x] Optimize queries
- [x] User settings
- [x] Global settings
- [x] Change username
- [x] Change password
- [x] User group change
- [x] User group settings
- [ ] ~~Scoreboard~~
- [x] Script to clean up session table
- [ ] ~~Clean up unused functions and variables~~
- [ ] Setup script
- [ ] Upgrade to Materialize 1.0

## Database tables

### `forum` table

Used to store information of individual forums

Must have one and only one inter-house forum, where members of all houses can access. The record of the inter-house forum will have hId set to `NULL`

Each house must only have 1 forum

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
fId | char(3) | `PRIMARY KEY`
fName | varchar(30) | `NOT NULL`
fDescription | varchar(100) |
hId | char(3) | `UNIQUE`, `FOREIGN KEY REFERENCES house(hId)`

SQL to create the table:

```sql
CREATE TABLE forum (fId char(3) PRIMARY KEY, fName varchar(30) NOT NULL, fDescription varchar(30), hId CHAR(3) UNIQUE, FOREIGN KEY (hId) REFERENCES house(hId));
```

### `house` table

Used to store information of the houses

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
hId | char(3) | `PRIMARY KEY`
houseName | varchar(20) | `NOT NULL`

SQL to create the table:

```sql
CREATE TABLE house (hId CHAR(3) PRIMARY KEY, houseName varchar(20) NOT NULL);
```

### `permission` table

Stores a set of permission that is used in the code

Have a set of default data that should **NOT** be changed

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
permisison | char(3) | `PRIMARY KEY`
permissionDescription | varchar(100) | `NOT NULL`

SQL to create the table:

```sql
CREATE TABLE permission (permission CHAR(3) PRIMARY KEY, permissionDescription VARCHAR(100) NOT NULL);
```

#### Default data

permission | permissionDescription
----- | -----
AGS | Access global settings
AUG | Access userGroup settings
AUS | Access user settings (change his/her userGroup)(accessible within profile page)
DAH | Delete posts and replies in all house forums
DH | Delete posts and replies within their respective house-specific forum
DI | Delete posts and replies within inter-house forum
EAH | Edit posts and replies in all house forums
EH | Edit posts and replies within their respective house-specific forum
EI | Edit posts and replies within inter-house forum
PAH | Post in all house-specific forums
PH | Post in their respective house-specific forum
PI | Post in inter-house forum
RAH | Reply to all house-specific forum threads
RH | Reply to posts within their respective house-specific forum
RI | Reply to posts within inter-house forum
VAH | View all house-specific forums
VH | View house-specific forum for user's house
VI | View inter-house forums

### `session` table

Used to store information of sessions

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
sessionId | char(40) | `PRIMARY KEY`
studentId | char(7) | `NOT NULL`, `FOREIGN KEY REFERENCES users(studentId)`
lastActivity | int(10) | `NOT NULL`

SQL to create the table:

```sql
CREATE TABLE session (sessionId CHAR(40) PRIMARY KEY, studentId CHAR(7) NOT NULL, lastActivity INT(10) NOT NULL, FOREIGN KEY (studentId) REFERENCES users(studentId));
```

### `thread` table

Used to store information of threads

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
tId | int(10) | `PRIMARY KEY`
tTitle | varchar(40) | `NOT NULL`
tContent | text | `NOT NULL`
tTime | int(10) | `NOT NULL`
fId | char(3) | `NOT NULL`, `FOREIGN KEY REFERENCES forum(fId)`
studentId | char(7)| `NOT NULL`, `FOREIGN KEY REFERENCES users(studentId)`
pin | char(1) | `DEFAULT '0'`

SQL to create the table:

```sql
CREATE TABLE thread (tId INT(10) PRIMARY KEY, tTitle VARCHAR(40) NOT NULL, tContent TEXT NOT NULL, tTime INT(10) NOT NULL, fId CHAR(3) NOT NULL, studentId CHAR(7) NOT NULL, pin CHAR(1) DEFAULT '0', FOREIGN KEY (fId) REFERENCES forum(fId), FOREIGN KEY (studentId) REFERENCES users(studentId));
```

### `userGroup` table

Used to store information of user groups

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
userGroup | char(3) | `PRIMARY KEY`
userGroupName | varchar(50) | `NOT NULL`
userGroupDescription | varchar(100) |

SQL to create the table:

```sql
CREATE TABLE userGroup (userGroup CHAR(3) PRIMARY KEY, userGroupName varchar(50) NOT NULL, userGroupDescription VARCHAR(100));
```

### `userPermission` table

Used to store information of permissions of each user groups

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
userGroup | char(3) | `PRIMARY KEY`, `FOREIGN KEY REFERENCES userGroup(userGroup)`
permission | char(3) | `PRIMARY KEY`, `FOREIGN KEY REFERENCES permission(permission)`

SQL to create the table:

```sql
CREATE TABLE userPermission (userGroup CHAR(3) NOT NULL, permission CHAR(3) NOT NULL, PRIMARY KEY (userGroup, permission), FOREIGN KEY (userGroup) REFERENCES userGroup(userGroup), FOREIGN KEY (permission) REFERENCES permission(permission));
```

### `users` table

Used to store information of users

Data should be inserted using [add_user.php](https://github.com/eugenelee0420/House-forum/blob/master/add_user.php)

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
studentId | char(7) | `PRIMARY KEY`
userName | varchar(30) | `NOT NULL`, `UNIQUE`
hId | char(3) | `NOT NULL`, `FOREIGN KEY REFERENCES house(hId)`
userGroup | char(3) | `NOT NULL`, `FOREIGN KEY REFERENCES userGroup(userGroup)`
hash | varchar(100) | `NOT NULL`

SQL to create the table:

```sql
CREATE TABLE users (studentId CHAR(7) PRIMARY KEY, userName VARCHAR(30) NOT NULL UNIQUE, hId CHAR(3) NOT NULL, userGroup CHAR(3) NOT NULL, hash VARCHAR(100) NOT NULL, FOREIGN KEY (hId) REFERENCES house(hId), FOREIGN KEY (userGroup) REFERENCES userGroup(userGroup));
```

### `userSetting` table

Used to store users' settings

New record with default settings will be added when an user is created using [add_user.php](https://github.com/eugenelee0420/House-forum/blob/master/add_user.php)

Records should not be added or deleted, but the defaults can be customized

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
studentId | char(7) | `PRIMARY KEY`, `FOREIGN KEY REFERENCES users(studentId)`
rowsPerPage | int(5) | `NOT NULL`, `DEFAULT 10`
avatarPic | varchar(200) | `NOT NULL`, `DEFAULT 'https://upload.wikimedia.org/wikipedia/commons/1/1e/Default-avatar.jpg'`
bgPic | varchar(200) | `NOT NULL`, `DEFAULT 'http://puu.sh/wZnZr.jpg'`

SQL to create the table:

```sql
CREATE TABLE userSetting (studentId CHAR(7) PRIMARY KEY, rowsPerPage INT(5) NOT NULL DEFAULT 10, avatarPic VARCHAR(200) NOT NULL DEFAULT 'https://upload.wikimedia.org/wikipedia/commons/1/1e/Default-avatar.jpg', bgPic VARCHAR(200) NOT NULL DEFAULT 'http://puu.sh/wZnZr.jpg', FOREIGN KEY (studentId) REFERENCES users(studentId));
```

### `reply` table

Used to store replies to threads

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
rId | int(10) | `PRIMARY KEY`
rContent | text | `NOT NULL`
rTime | int(10) | `NOT NULL`
tId | int(10) | `NOT NULL`, `FOREIGN KEY REFERENCES thread(tId)`
studentId | char(7) | `NOT NULL`, `FOREIGN KEY REFERENCES users(studentId)`

SQL to create the table:

```sql
CREATE TABLE reply (rId INT(10) PRIMARY KEY, rContent TEXT NOT NULL, rTime INT(10) NOT NULL, tId INT(10) NOT NULL, studentId CHAR(7) NOT NULL, FOREIGN KEY (tId) REFERENCES thread(tId), FOREIGN KEY (studentId) REFERENCES users(studentId));
```

### `globalSetting` table

Used to store global settings

Has a set of default data

The `setting` field should not be changed, the `value` field can be customized within the "Global Settings" page

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
setting | varchar(30) | `NOT NULL`
value | text | `NOT NULL`
settingDescription | varchar(100) |

SQL to create the table:

```sql
CREATE TABLE globalSetting (setting VARCHAR(30) NOT NULL, value TEXT NOT NULL, settingDescription VARCHAR(100));
```

#### Default data

setting | value | settingDescription
----- | ----- | -----
welcomeMsg | Hi | A welcome message that will be displayed on index.php. HTML and markdown are supported.
userTimeout | 600 | Idle time before user is logged out automatically (seconds)
timezoneOffset | 28800 | UNIX epoch timezone offset

### `loginRecord` table

Used to store login records

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
time | int(10) | `PRIMARY KEY`, `AUTO_INCREMENT`
studentId | char(7) | `NOT NULL`, `FOREIGN KEY REFERENCES users(studentId)`
ip | char(45) | `NOT NULL`

SQL to create the table:

```sql
CREATE TABLE loginRecord (time int(10) PRIMARY KEY AUTO_INCREMENT, studentId char(7) NOT NULL, ip char(45) NOT NULL, FOREIGN KEY (studentId) REFERENCES users(studentId));
```
