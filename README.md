Automatic Task Push/Pull
==============================

** BETA ** this plugin is in beta mode, it evolve so use it carefully at you own risk ** BETA **


This plugin aim to add an automatic action to push and pull tasks to an other
cols if the limit is reached, this in order to automatically apply
the kanban workflow.

Author
------
- Jerome Barotin
- License MIT

Requirements
------------
- Kanboard >= 1.0.35

Installation
------------

You have the choice between 3 methods:

1. Install the plugin from the Kanboard plugin manager in one click
2. Download the zip file and decompress everything under the directory `plugins/auto_task_push_pull`
3. Clone this repository into the folder `plugins/auto_task_push_pull`

Note: Plugin folder is case-sensitive.

Documentation
-------------

This plugin aim to add an automatic action that aim to push and pull tasks to an
other cols if the limit is reached. In order to automatically apply the kanban workflow.

Rule of automatic push pull are :
- if dest is full => push bottom task to src
- if dest is not full and src is full => pull top task to dest  
