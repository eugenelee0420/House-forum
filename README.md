# House-forum

## Introduction

ICT SBA Project

## Database tables

`forum` table

Field Name | Data Type (Size) | Constraints
----- | --- | -----
fId | char(3) | PRIMARY KEY
fName | varchar(30) | NOT NULL
fDescription | varchar(100) |
hId | char(3) | UNIQUE, FOREIGN KEY REFERENCING house(hId)
