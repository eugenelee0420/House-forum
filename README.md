# House-forum

## Introduction

ICT SBA Project

## Other projects used

* [materialize](https://github.com/dogfalo/materialize/) - A CSS framework based on Material Design
* [Parsedown](https://github.com/erusev/parsedown) - Markdown parser in PHP
* [Highlight.js](https://github.com/isagalaev/highlight.js) - Javascript syntax highlighter

## Minimum requirement

* PHP 7.0.2
* MySQL 4.1.13
* Apache 2
* OpenSSL
* Sendmail (Follow [this guide](https://gist.github.com/adamstac/7462202) to install and configure Sendmail on Ubuntu)

### PHP modules

* apcu
* curl
* date
* hash
* json
* mbstring
* mcrypt
* mysqli
* openssl
* pcre
* session

Please also make sure you meet the minimum requirement of composer.

## Installation

1. Clone the repository

```bash
git clone https://github.com/eugenelee0420/House-forum.git forums
cd forums
```

2. Install composer

If you want to verify the hash of the installer, use the instruction on [this page](https://getcomposer.org/download/) instead.

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

3. Install the dependencies

```bash
php composer.phar install
```

4. Give permission to www-data user

```bash
cd ..
chown -R www-data:www-data forums
```

5. Create a new empty database for the forums
```sql
CREATE DATABASE forums;
```

6. Use a web browser to navigate to setup.php and follow the instructions

7. Make sure cfg.json is inaccessible through the web server, as it contains database credentials.

To do this on apache web server, enable the use of .htaccess and create a .htaccess file. (or modify the server config if you wish)

First enable .htaccess by adding these lines to your site's configuration file: (located in `/etc/apache2/sites-enabled/`)

```
<Directory /var/www/html/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Change the directory to your site's root if necessary.

Restart the apache service after changing:

```bash
service apache2 restart
```

Then create a file called `.htaccess` that contain the following in your site's root:

```
<Files .htaccess>
Require all denied
</files>

<Files *.json>
Require all denied
</Files>
```

Then test to make sure that attempt to access cfg.json through a browser will result in a 403 error.

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
CREATE TABLE forum (fId char(3) PRIMARY KEY, fName varchar(30) NOT NULL, fDescription varchar(30), hId CHAR(3) UNIQUE, FOREIGN KEY (hId) REFERENCES house(hId)) ENGINE=InnoDB;
```

### `house` table

Used to store information of the houses

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
hId | char(3) | `PRIMARY KEY`
houseName | varchar(20) | `NOT NULL`

SQL to create the table:

```sql
CREATE TABLE house (hId CHAR(3) PRIMARY KEY, houseName varchar(20) NOT NULL) ENGINE=InnoDB;
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
CREATE TABLE permission (permission CHAR(3) PRIMARY KEY, permissionDescription VARCHAR(100) NOT NULL) ENGINE=InnoDB;
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
CREATE TABLE session (sessionId CHAR(40) PRIMARY KEY, studentId CHAR(7) NOT NULL, lastActivity INT(10) NOT NULL, FOREIGN KEY (studentId) REFERENCES users(studentId)) ENGINE=InnoDB;
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
CREATE TABLE thread (tId INT(10) PRIMARY KEY, tTitle VARCHAR(40) NOT NULL, tContent TEXT NOT NULL, tTime INT(10) NOT NULL, fId CHAR(3) NOT NULL, studentId CHAR(7) NOT NULL, pin CHAR(1) DEFAULT '0', FOREIGN KEY (fId) REFERENCES forum(fId), FOREIGN KEY (studentId) REFERENCES users(studentId)) ENGINE=InnoDB;
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
CREATE TABLE userGroup (userGroup CHAR(3) PRIMARY KEY, userGroupName varchar(50) NOT NULL, userGroupDescription VARCHAR(100)) ENGINE=InnoDB;
```

### `userPermission` table

Used to store information of permissions of each user groups

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
userGroup | char(3) | `PRIMARY KEY`, `FOREIGN KEY REFERENCES userGroup(userGroup)`
permission | char(3) | `PRIMARY KEY`, `FOREIGN KEY REFERENCES permission(permission)`

SQL to create the table:

```sql
CREATE TABLE userPermission (userGroup CHAR(3) NOT NULL, permission CHAR(3) NOT NULL, PRIMARY KEY (userGroup, permission), FOREIGN KEY (userGroup) REFERENCES userGroup(userGroup), FOREIGN KEY (permission) REFERENCES permission(permission)) ENGINE=InnoDB;
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
hash | varchar(255) | `NOT NULL`
email | varchar(100) | `UNIQUE`
emailVerified | int(1) | `NOT NULL`, `DEFAULT 0`

SQL to create the table:

```sql
CREATE TABLE users (studentId CHAR(7) PRIMARY KEY, userName VARCHAR(30) NOT NULL UNIQUE, hId CHAR(3) NOT NULL, userGroup CHAR(3) NOT NULL, hash VARCHAR(100) NOT NULL, FOREIGN KEY (hId) REFERENCES house(hId), FOREIGN KEY (userGroup) REFERENCES userGroup(userGroup)) ENGINE=InnoDB;
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
CREATE TABLE userSetting (studentId CHAR(7) PRIMARY KEY, rowsPerPage INT(5) NOT NULL DEFAULT 10, avatarPic VARCHAR(200) NOT NULL DEFAULT 'https://upload.wikimedia.org/wikipedia/commons/1/1e/Default-avatar.jpg', bgPic VARCHAR(200) NOT NULL DEFAULT 'http://puu.sh/wZnZr.jpg', FOREIGN KEY (studentId) REFERENCES users(studentId)) ENGINE=InnoDB;
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
CREATE TABLE reply (rId INT(10) PRIMARY KEY, rContent TEXT NOT NULL, rTime INT(10) NOT NULL, tId INT(10) NOT NULL, studentId CHAR(7) NOT NULL, FOREIGN KEY (tId) REFERENCES thread(tId), FOREIGN KEY (studentId) REFERENCES users(studentId)) ENGINE=InnoDB;
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
CREATE TABLE globalSetting (setting VARCHAR(30) NOT NULL, value TEXT NOT NULL, settingDescription VARCHAR(100)) ENGINE=InnoDB;
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
CREATE TABLE loginRecord (time int(10) PRIMARY KEY AUTO_INCREMENT, studentId char(7) NOT NULL, ip char(45) NOT NULL, FOREIGN KEY (studentId) REFERENCES users(studentId)) ENGINE=InnoDB;
```

### `tfa` table

Used to store 2-factor authentication shared secret

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
studentId | char(7) | `PRIMARY KEY`, `FOREIGN KEY REFERENCES users(studentId)`
tfaSecret | varchar(100) | `NOT NULL`

SQL to create the table:

```sql
CREATE TABLE tfa (studentId CHAR(7) PRIMARY KEY, tfaSecret VARCHAR(100) NOT NULL, FOREIGN KEY (studentId) REFERENCES users(studentId)) ENGINE=InnoDB;
```

### `mailToken` table

Used to store tokens for sent emails

Field Name | Data Type (Size) | Constraints
----- | ----- | -----
token | varchar(100) | `PRIMARY KEY`
action | varchar(20) | `NOT NULL`
studentId | char(7) | `NOT NULL`, `FOREIGN KEY REFERENCES users(studentId)`

SQL to create the table:

```sql
CREATE TABLE mailToken (token VARCHAR(100) PRIMARY KEY, action VARCHAR(20) NOT NULL, studentId CHAR(7) NOT NULL, FOREIGN KEY (studentId) REFERENCES users(studentId)) ENGINE=InnoDB;
```
