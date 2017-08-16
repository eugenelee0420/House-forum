# House-forum

## Introduction

ICT SBA Project

[Live website](https://heteropterous-recei.000webhostapp.com/sba/login.php)

## Other projects used

* [materialize](https://github.com/dogfalo/materialize/)
* [Parsedown](https://github.com/erusev/parsedown)

## To-do list

- [x] Change queries to prepared statement
- [x] Delete reply
- [x] Pin thread
- [x] Optimize queries
- [x] User settings
- [x] Global settings
- [x] Change username
- [x] Change password
- [ ] User group change
- [ ] User group settings
- [ ] Scoreboard
- [ ] Script to clean up session table
- [ ] Clean up unused functions and variables

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
studentId | char(7) | `NOT NULL`, `FOREIGN KEY REFERENCING users(studentId)`
lastActivity | int(10) | `NOT NULL`

### `thread` table

Used to store information of threads

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
tId | int(10) | `PRIMARY KEY`
tTitle | varchar(40) | `NOT NULL`
tContent | text | `NOT NULL`
tTime | char(10) | `NOT NULL`
fId | char(3) | `NOT NULL`, `FOREIGN KEY REFERENCING forum(fId)`
studentId | char(7)| `NOT NULL`, `FOREIGN KEY REFERENCING users(studentId)`
pin | char(1) | `DEFAULT '0'`

### `userGroup` table

Used to store information of user groups

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
userGroup | char(3) | `PRIMARY KEY`
userGroupName | varchar(50) | `NOT NULL`
userGroupDescription | varchar(100) |

### `userPermisison` table

Used to store information of permissions of each user groups

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
userGroup | char(3) | `PRIMARY KEY`
permission | char(3) | `PRIMARY KEY`, `FOREIGN KEY REFERENCING permission(permission)`

### `users` table

Used to store information of users

Data should be inserted using [add_user.php](https://github.com/eugenelee0420/House-forum/blob/master/add_user.php)

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
studentId | char(7) | `PRIMARY KEY`
userName | varchar(30) | `NOT NULL`, `UNIQUE`
hId | char(3) | `NOT NULL`, `FOREIGN KEY REFERENCING house(hId)`
userGroup | char(3) | `NOT NULL`, `FOREIGN KEY REFERENCING userGroup(userGroup)`
hash | varchar(100) | `NOT NULL`

### `userSetting` table

Used to store users' settings

New record with default settings will be added when an user is created using [add_user.php](https://github.com/eugenelee0420/House-forum/blob/master/add_user.php)

Records should not be added or deleted, but the defaults can be customized

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
studentId | char(7) | `PRIMARY KEY`, `FOREIGN KEY REFERENCING users(studentId)`
rowsPerPage | int(5) | `NOT NULL`, `DEFAULT 10`
avatarPic | varchar(200) | `NOT NULL`, `DEFAULT 'https://upload.wikimedia.org/wikipedia/commons/1/1e/Default-avatar.jpg'`
bgPic | varchar(200) | `NOT NULL`, `DEFAULT 'http://puu.sh/wZnZr.jpg'`

### `reply` table

Used to store replies to threads

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
rId | int(10) | `PRIMARY KEY`
rContent | text | `NOT NULL`
rTime | int(10) | `NOT NULL`
tId | int(10) | `NOT NULL`, `FOREIGN KEY REFERENCING thread(tId)`
studentId | char(7) | `NOT NULL`, `FOREIGN KEY REFERENCING users(studentId)`

### `globalSetting` table

Used to store global settings

Has a set of default data

The `setting` field should not be changed, the `value` field can be customized within the "Global Settings" page

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
setting | varchar(30) | `NOT NULL`
value | text | `NOT NULL`
settingDescription | varchar(100) |

#### Default data

setting | value | settingDescription
----- | ----- | -----
welcomeMsg | Hi | A welcome message that will be displayed on index.php. HTML and markdown are supported.
userTimeout | 600 | Idle time before user is logged out automatically (seconds)
timezoneOffset | 28800 | UNIX epoch timezone offset
