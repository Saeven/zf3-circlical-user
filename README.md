##Turnkey User Module for ZF3 and Doctrine

Plug and play system for:

- cookie based authentication (using halite and its authenticated encryption)
- role-based access control with guards at the controller and action level
- also supports user-level exceptions to the RBAC config (let all admins, and Pete view the list) 
- resource-based rule control, giving you 'resource' and 'verb' control at the role and user level, e.g. 
(all administrators can 'add' a server, only Pete can 'delete')