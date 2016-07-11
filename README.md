## Authentication, Identity, and Security for ZF3
[![Build Status](https://travis-ci.org/Saeven/zf3-circlical-user.svg?branch=master)](https://travis-ci.org/Saeven/zf3-circlical-user)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/fe24b2bf7ab74919844fdb49adbf99fe)](https://www.codacy.com/app/alemaire/zf3-circlical-user?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Saeven/zf3-circlical-user&amp;utm_campaign=Badge_Grade)

Plug and play system for:

- cookie based authentication (using halite and its authenticated encryption)
- role-based access control with guards at the controller and action level
- also supports user-level exceptions to the RBAC config (let all admins, and Pete view the list) 
- resource-based rule control, giving you 'resource' and 'verb' control at the role and user level, e.g. 
(all administrators can 'add' a server, only Pete can 'delete')

### Philosophy

There are other Authentication, ACL, and User modules out there.  This one differs in that it is opinionated. It is specifically geared to
support **Doctrine** and the latest version of **Zend Framework**.  This lets its code and configuration be very clean, and
also lets it impose things that'll make your life easier if your goals are congruent.  

Its authentication system is cookie-based, meaning no session usage at all.  This was done because I develop for circumstances where this is preferable, removing any need
for complex or error-prone solutions for session management on an EC2 auto-scale group for example.

Lastly, authentication encryption is handled using the well-trusted [Halite](http://google.com), and password hashing is properly done with PHP's new password functions. [Feedback always solicited on r/php.](https://www.reddit.com/r/PHP/comments/4r84jn/need_reviews_of_cookiebased_authentication_service/)

> Right now, I am waiting for Doctrine-ORM to move to the latest Zend-MVC.
> When that's done, I will promptly upgrade this package.

## Its Parts

### User Authentication
The module provides full identity management, starting at the user-level.  A design goal was to connect this into your registration or login
processes with little more than one-liners.

### Groups

Your users belong to groups that are configured in the database, and can be hierarchical.  *The default guest user, is group-less.*  Groups can be used
to restrict access to **controllers**, **actions**, or **resources**.

### Guards

Guards are conditions on **controllers** or **actions** that examine **group** or **user** privileges to permit/decline whatever is going on.  It works
very similarly to [BjyAuthorize](https://github.com/bjyoungblood/BjyAuthorize) (a great module I used for years).

### Resources
(WIP)

### User Exceptions
(WIP)

# Installation
(WIP)