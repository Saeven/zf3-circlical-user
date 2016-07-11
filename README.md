##Turnkey User Module for ZF3 and Doctrine
[![Build Status](https://travis-ci.org/Saeven/zf3-circlical-user.svg?branch=master)](https://travis-ci.org/Saeven/zf3-circlical-user)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/fe24b2bf7ab74919844fdb49adbf99fe)](https://www.codacy.com/app/alemaire/zf3-circlical-user?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Saeven/zf3-circlical-user&amp;utm_campaign=Badge_Grade)

Plug and play system for:

- cookie based authentication (using halite and its authenticated encryption)
- role-based access control with guards at the controller and action level
- also supports user-level exceptions to the RBAC config (let all admins, and Pete view the list) 
- resource-based rule control, giving you 'resource' and 'verb' control at the role and user level, e.g. 
(all administrators can 'add' a server, only Pete can 'delete')